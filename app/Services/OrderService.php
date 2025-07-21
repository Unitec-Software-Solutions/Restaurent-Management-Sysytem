<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Comprehensive Order Service Layer
 * Handles order creation, validation, and processing with enhanced error handling
 */
class OrderService
{
    // Order status constants with state machine
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Valid status transitions
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => []
    ];

    /**
     * Create a new order with comprehensive validation
     */
    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            // 1. Validate order data
            $this->validateOrderData($orderData);

            // 2. Validate reservation if provided
            $reservation = null;
            if (!empty($orderData['reservation_id'])) {
                $reservation = $this->validateReservation($orderData['reservation_id']);

                // Ensure reservation is confirmed and valid
                if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
                    throw new Exception("Reservation must be confirmed before placing orders");
                }

                // Check time constraints
                if ($reservation->date < now()->toDateString()) {
                    throw new Exception("Cannot create orders for past reservations");
                }
            }

            // 3. Validate branch and organization status
            $branch = $this->validateBranch($orderData['branch_id'] ?? $reservation?->branch_id);
            if (!$branch->is_active || !$branch->organization->is_active) {
                throw new Exception("Cannot create orders for inactive branch or organization");
            }

            // 4. Validate items and stock
            $this->validateOrderItems($orderData['items'], $branch->id);

            // 5. Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'reservation_id' => $reservation?->id,
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'customer_name' => $orderData['customer_name'] ?? $reservation?->name,
                'customer_phone' => $orderData['customer_phone'] ?? $reservation?->phone,
                'customer_email' => $orderData['customer_email'] ?? $reservation?->email,
                'order_type' => $orderData['order_type'],
                'status' => self::STATUS_PENDING,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'notes' => $orderData['notes'] ?? null,
                'created_by' => optional(auth())->id(),
            ]);

            // 6. Create order items and update stock
            $this->createOrderItems($order, $orderData['items']);

            // 7. Calculate totals
            $this->calculateOrderTotals($order);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'branch_id' => $branch->id,
                'total_amount' => $order->total_amount
            ]);

            return $order->fresh(['orderItems', 'reservation', 'branch']);
        });
    }

    /**
     * Update an existing order
     */
    public function updateOrder(Order $order, array $orderData): Order
    {
        return DB::transaction(function () use ($order, $orderData) {
            // Check if order can be updated
            if (!in_array($order->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])) {
                throw new Exception('Order cannot be updated in current status: ' . $order->status);
            }

            // Reverse stock for existing items
            $this->reverseOrderStock($order);

            // Validate new items
            $this->validateOrderItems($orderData['items'], $order->branch_id);

            // Update order details
            $order->update([
                'customer_name' => $orderData['customer_name'] ?? $order->customer_name,
                'customer_phone' => $orderData['customer_phone'] ?? $order->customer_phone,
                'customer_email' => $orderData['customer_email'] ?? $order->customer_email,
                'order_type' => $orderData['order_type'] ?? $order->order_type,
                'notes' => $orderData['notes'] ?? $order->notes,
                'updated_by' => optional(auth())->id(),
            ]);

            // Delete old items
            $order->orderItems()->delete();

            // Create new items
            $this->createOrderItems($order, $orderData['items']);

            // Recalculate totals
            $this->calculateOrderTotals($order);

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount
            ]);

            return $order->fresh(['orderItems', 'reservation', 'branch']);
        });
    }

    /**
     * Update order status with validation
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $reason = null): Order
    {
        $currentStatus = $order->status;

        // Validate transition
        if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
            throw new Exception("Invalid status transition from {$currentStatus} to {$newStatus}");
        }

        $order->update([
            'status' => $newStatus,
            'status_updated_at' => now(),
            'status_reason' => $reason,
            'updated_by' => optional(auth())->id(),
        ]);

        // Handle status-specific logic
        $this->handleStatusChange($order, $currentStatus, $newStatus);

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'from_status' => $currentStatus,
            'to_status' => $newStatus,
            'reason' => $reason
        ]);

        return $order;
    }

    /**
     * Get available stewards for a branch
     */
    public function getAvailableStewards(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::stewards()
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get items with current stock levels for a branch
     */
    public function getItemsWithStock(int $branchId, ?int $organizationId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = ItemMaster::where('is_menu_item', true)
            ->where('is_active', true);

        // Apply organization filter if provided
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $items = $query->select('id', 'name', 'selling_price', 'buying_price', 'attributes')
            ->get();

        // Add stock information to each item
        return $items->map(function ($item) use ($branchId) {
            $stockOnHand = ItemTransaction::stockOnHand($item->id, $branchId);
            $item->stock_on_hand = $stockOnHand;
            $item->is_low_stock = $stockOnHand <= ($item->reorder_level ?? 10);
            $item->is_out_of_stock = $stockOnHand <= 0;
            return $item;
        });
    }

    /**
     * Get stock alerts for a branch
     */
    public function getStockAlerts(int $branchId, ?int $organizationId = null): array
    {
        $items = $this->getItemsWithStock($branchId, $organizationId);

        $alerts = [
            'low_stock' => $items->where('is_low_stock', true)->where('is_out_of_stock', false),
            'out_of_stock' => $items->where('is_out_of_stock', true),
            'total_alerts' => 0
        ];

        $alerts['total_alerts'] = $alerts['low_stock']->count() + $alerts['out_of_stock']->count();

        return $alerts;
    }

    /**
     * Cancel an order and reverse stock
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            // Check if order can be cancelled
            if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
                throw new Exception('Order cannot be cancelled in current status: ' . $order->status);
            }

            // Reverse stock
            $this->reverseOrderStock($order);

            // Update order status
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'status_updated_at' => now(),
                'status_reason' => $reason ?? 'Cancelled by admin',
                'updated_by' => optional(auth())->id(),
            ]);

            // Update reservation status if linked
            if ($order->reservation) {
                $order->reservation->update(['status' => 'cancelled']);
            }

            Log::info('Order cancelled successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $reason
            ]);

            return $order->fresh();
        });
    }

    /**
     * Get order statistics for a branch
     */
    public function getOrderStatistics(int $branchId, int $days = 1): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $orders = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', self::STATUS_COMPLETED)->count(),
            'pending_orders' => $orders->where('status', self::STATUS_PENDING)->count(),
            'cancelled_orders' => $orders->where('status', self::STATUS_CANCELLED)->count(),
            'total_revenue' => $orders->where('status', self::STATUS_COMPLETED)->sum('total_amount'),
            'average_order_value' => $orders->where('status', self::STATUS_COMPLETED)->avg('total_amount'),
        ];
    }

    /**
     * Get order alerts for dashboard
     */
    public function getOrderAlerts(int $branchId): array
    {
        $alerts = [];

        // Check for stuck orders (pending for too long)
        $stuckOrders = Order::where('branch_id', $branchId)
            ->where('status', self::STATUS_PENDING)
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();

        if ($stuckOrders > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "You have {$stuckOrders} orders pending for more than 30 minutes",
                'action' => 'Review pending orders',
                'count' => $stuckOrders
            ];
        }

        // Check for orders ready for pickup/serving
        $readyOrders = Order::where('branch_id', $branchId)
            ->where('status', self::STATUS_READY)
            ->count();

        if ($readyOrders > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "You have {$readyOrders} orders ready for pickup/serving",
                'action' => 'Complete orders',
                'count' => $readyOrders
            ];
        }

        return $alerts;
    }

    /**
     * Validate order data
     */
    private function validateOrderData(array $orderData): void
    {
        $required = ['order_type', 'items'];

        foreach ($required as $field) {
            if (empty($orderData[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        if (empty($orderData['items']) || !is_array($orderData['items'])) {
            throw new Exception('Order must contain at least one item');
        }

        // Validate order type
        $validTypes = [
            Order::TYPE_DINE_IN,
            Order::TYPE_TAKEAWAY,
            Order::TYPE_DELIVERY,
            Order::TYPE_TAKEAWAY_IN_CALL,
            Order::TYPE_TAKEAWAY_ONLINE,
            Order::TYPE_TAKEAWAY_WALKIN_SCHEDULED,
            Order::TYPE_TAKEAWAY_WALKIN_DEMAND,
            // Order::TYPE_DINEIN_ONLINE,
            // Order::TYPE_DINEIN_INCALL,
            // Order::TYPE_DINEIN_WALKIN_SCHEDULED,
            // Order::TYPE_DINEIN_WALKIN_DEMAND,
        ];

        if (!in_array($orderData['order_type'], $validTypes)) {
            throw new Exception('Invalid order type: ' . $orderData['order_type']);
        }
    }

    /**
     * Validate reservation
     */
    private function validateReservation(int $reservationId): Reservation
    {
        $reservation = Reservation::with(['branch.organization'])->find($reservationId);

        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        if (!$reservation->branch->isSystemActive()) {
            throw new Exception('Reservation branch is inactive');
        }

        if ($reservation->status === 'cancelled') {
            throw new Exception('Cannot create order for cancelled reservation');
        }

        return $reservation;
    }

    /**
     * Validate branch
     */
    private function validateBranch(int $branchId): Branch
    {
        $branch = Branch::with('organization')->find($branchId);

        if (!$branch) {
            throw new Exception('Branch not found');
        }

        if (!$branch->isSystemActive()) {
            throw new Exception('Branch or organization is inactive');
        }

        return $branch;
    }

    /**
     * Validate order items and stock availability
     */
    private function validateOrderItems(array $items, int $branchId): void
    {
        $stockErrors = [];

        foreach ($items as $index => $item) {
            if (empty($item['item_id']) || empty($item['quantity'])) {
                throw new Exception("Invalid item data at index {$index}");
            }

            $menuItem = ItemMaster::find($item['item_id']);
            if (!$menuItem) {
                throw new Exception("Item not found: {$item['item_id']}");
            }

            if (!$menuItem->is_active) {
                throw new Exception("Item is inactive: {$menuItem->name}");
            }

            // Check stock availability
            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);
            if ($currentStock < $item['quantity']) {
                $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
            }
        }

        if (!empty($stockErrors)) {
            throw new Exception('Stock validation failed: ' . implode(', ', $stockErrors));
        }
    }

    /**
     * Create order items and deduct stock
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $menuItem = ItemMaster::find($item['item_id']);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'line_total' => $menuItem->selling_price * $item['quantity'],
                'notes' => $item['notes'] ?? null,
            ]);

            // Deduct stock
            ItemTransaction::create([
                'organization_id' => $order->organization_id,
                'branch_id' => $order->branch_id,
                'inventory_item_id' => $item['item_id'],
                'transaction_type' => 'order_sale',
                'quantity' => -$item['quantity'], // Negative for outgoing
                'cost_price' => $menuItem->buying_price ?? 0,
                'unit_price' => $menuItem->selling_price,
                'reference_id' => $order->id,
                'reference_type' => 'Order',
                'created_by_user_id' => optional(auth())->id(),
                'notes' => "Order #{$order->order_number} - {$menuItem->name}",
                'is_active' => true,
            ]);
        }
    }

    /**
     * Reverse stock for order items (for updates/cancellations)
     */
    private function reverseOrderStock(Order $order): void
    {
        foreach ($order->orderItems as $orderItem) {
            ItemTransaction::create([
                'organization_id' => $order->organization_id,
                'branch_id' => $order->branch_id,
                'inventory_item_id' => $orderItem->inventory_item_id,
                'transaction_type' => 'order_reversal',
                'quantity' => $orderItem->quantity, // Positive to add back
                'cost_price' => $orderItem->inventoryItem->buying_price ?? 0,
                'unit_price' => $orderItem->unit_price,
                'reference_id' => $order->id,
                'reference_type' => 'Order',
                'created_by_user_id' => optional(auth())->id(),
                'notes' => "Stock reversal for Order #{$order->order_number}",
                'is_active' => true,
            ]);
        }
    }

    /**
     * Calculate order totals
     */
    private function calculateOrderTotals(Order $order): void
    {
        $subtotal = $order->orderItems()->sum('line_total');
        $taxRate = config('app.default_tax_rate', 0.10); // 10% default
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Check if status transition is valid
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$currentStatus] ?? []);
    }

    /**
     * Handle status-specific logic
     */
    private function handleStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        switch ($newStatus) {
            case self::STATUS_CANCELLED:
                // Reverse stock when cancelling
                $this->reverseOrderStock($order);
                break;

            case self::STATUS_COMPLETED:
                // Mark reservation as completed if linked
                if ($order->reservation) {
                    $order->reservation->update(['status' => 'completed']);
                }
                break;
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $latest = Order::whereDate('created_at', today())
                      ->latest('id')
                      ->first();

        $sequence = $latest ? ((int) substr($latest->order_number, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    // Legacy methods for backward compatibility

    /**
     * Install real-time order management system
     */
    public function installRealTimeSystem(): void
    {
        $this->setupInventoryChecks();
        $this->setupKotGeneration();
        $this->setupOrderStateMachine();
        $this->setupStockReservation();

        Log::info('OrderService: Real-time system installed');
    }

    /**
     * Setup real-time inventory validation
     */
    private function setupInventoryChecks(): void
    {
        // Cache inventory levels for 30 seconds for performance
        Cache::remember('inventory_check_rules', 1800, function () {
            return [
                'check_interval' => 30, // seconds
                'low_stock_threshold' => 10,
                'auto_reserve_duration' => 300, // 5 minutes
                'stock_validation_enabled' => true
            ];
        });
    }

    /**
     * Setup KOT generation
     */
    private function setupKotGeneration(): void
    {
        // KOT configuration
        Cache::put('kot_generation_rules', [
            'auto_generate' => true,
            'group_by_station' => true,
            'include_preparation_time' => true,
            'print_immediately' => false
        ], 1800);
    }

    /**
     * Setup order state machine
     */
    private function setupOrderStateMachine(): void
    {
        // State machine configuration
        Cache::put('order_state_machine', self::VALID_TRANSITIONS, 1800);
    }

    /**
     * Setup stock reservation
     */
    private function setupStockReservation(): void
    {
        // Stock reservation configuration
        Cache::put('stock_reservation_rules', [
            'reserve_on_order' => true,
            'reservation_duration' => 1800, // 30 minutes
            'auto_release_expired' => true
        ], 1800);
    }
}
