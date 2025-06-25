<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\InventoryItem;
use App\Models\KitchenStation;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\InventoryReserved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Phase 2: Enhanced Order Management Service
 * Handles real-time inventory checks, KOT generation, and stock reservation
 */
class EnhancedOrderService
{
    /**
     * Order status state machine
     */
    private const VALID_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => []
    ];

    /**
     * Create order with real-time inventory validation
     */
    public function createOrderWithInventoryCheck(array $orderData): Order
    {
        return DB::transaction(function() use ($orderData) {
            // 1. Validate inventory availability
            $inventoryCheck = $this->validateInventoryAvailability($orderData['items']);
            if (!$inventoryCheck['available']) {
                throw new \Exception('Insufficient inventory: ' . implode(', ', $inventoryCheck['issues']));
            }

            // 2. Reserve inventory
            $reservations = $this->reserveInventory($orderData['items']);

            // 3. Create order
            $order = Order::create([
                'customer_name' => $orderData['customer_name'],
                'customer_phone' => $orderData['customer_phone'],
                'customer_email' => $orderData['customer_email'] ?? null,
                'branch_id' => $orderData['branch_id'],
                'menu_id' => $orderData['menu_id'],
                'order_type' => $orderData['order_type'],
                'status' => 'pending',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'special_instructions' => $orderData['special_instructions'] ?? null,
                'inventory_reservations' => $reservations,
                'created_at' => now()
            ]);

            // 4. Create order items
            $subtotal = 0;
            foreach ($orderData['items'] as $itemData) {
                $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
                $lineTotal = $menuItem->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['menu_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                    'special_instructions' => $itemData['special_instructions'] ?? null
                ]);
            }

            // 5. Calculate totals
            $taxAmount = $subtotal * 0.10; // 10% tax
            $totalAmount = $subtotal + $taxAmount;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount
            ]);

            // 6. Generate KOTs
            $kots = $this->generateKOTs($order);

            // 7. Fire events
            event(new OrderCreated($order));
            event(new InventoryReserved($order, $reservations));

            Log::info('Order created with inventory check', [
                'order_id' => $order->id,
                'customer' => $order->customer_name,
                'items_count' => count($orderData['items']),
                'total' => $totalAmount,
                'kots_generated' => count($kots)
            ]);

            return $order;
        });
    }

    /**
     * Validate inventory availability for order items
     */
    public function validateInventoryAvailability(array $items): array
    {
        $issues = [];
        $available = true;

        foreach ($items as $item) {
            $menuItem = MenuItem::with('inventoryItems')->findOrFail($item['menu_item_id']);
            
            // Check if menu item requires inventory tracking
            if (!$menuItem->track_inventory) {
                continue;
            }

            // Check each required inventory item
            foreach ($menuItem->inventoryItems as $inventoryItem) {
                $requiredQuantity = $inventoryItem->pivot->quantity_per_unit * $item['quantity'];
                $availableStock = $inventoryItem->current_stock - $inventoryItem->reserved_stock;

                if ($availableStock < $requiredQuantity) {
                    $available = false;
                    $issues[] = "Insufficient stock for {$menuItem->name} (need {$requiredQuantity}, have {$availableStock})";
                }
            }
        }

        return [
            'available' => $available,
            'issues' => $issues
        ];
    }

    /**
     * Reserve inventory for order
     */
    public function reserveInventory(array $items): array
    {
        $reservations = [];

        foreach ($items as $item) {
            $menuItem = MenuItem::with('inventoryItems')->findOrFail($item['menu_item_id']);
            
            if (!$menuItem->track_inventory) {
                continue;
            }

            foreach ($menuItem->inventoryItems as $inventoryItem) {
                $requiredQuantity = $inventoryItem->pivot->quantity_per_unit * $item['quantity'];
                
                // Reserve the stock
                $inventoryItem->increment('reserved_stock', $requiredQuantity);
                
                $reservations[] = [
                    'inventory_item_id' => $inventoryItem->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity_reserved' => $requiredQuantity,
                    'reserved_at' => now()
                ];
            }
        }

        return $reservations;
    }

    /**
     * Release inventory reservations
     */
    public function releaseInventoryReservations(Order $order): void
    {
        if (!$order->inventory_reservations) {
            return;
        }

        foreach ($order->inventory_reservations as $reservation) {
            $inventoryItem = InventoryItem::find($reservation['inventory_item_id']);
            if ($inventoryItem) {
                $inventoryItem->decrement('reserved_stock', $reservation['quantity_reserved']);
            }
        }

        Log::info('Inventory reservations released', [
            'order_id' => $order->id,
            'reservations_count' => count($order->inventory_reservations)
        ]);
    }

    /**
     * Generate KOTs (Kitchen Order Tickets) for order
     */
    public function generateKOTs(Order $order): array
    {
        $order->load(['orderItems.menuItem.kitchenStations']);
        $kotsByStation = [];

        // Group items by kitchen station
        foreach ($order->orderItems as $orderItem) {
            $stations = $orderItem->menuItem->kitchenStations;
            
            if ($stations->isEmpty()) {
                // Default to main kitchen if no specific station
                $stationId = 'main_kitchen';
            } else {
                // Use first assigned station (could be enhanced for multiple stations)
                $stationId = $stations->first()->id;
            }

            if (!isset($kotsByStation[$stationId])) {
                $kotsByStation[$stationId] = [
                    'station_id' => $stationId,
                    'station_name' => $stations->first()->name ?? 'Main Kitchen',
                    'order_id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'order_type' => $order->order_type,
                    'created_at' => now(),
                    'items' => []
                ];
            }

            $kotsByStation[$stationId]['items'][] = [
                'menu_item_id' => $orderItem->menu_item_id,
                'name' => $orderItem->menuItem->name,
                'quantity' => $orderItem->quantity,
                'special_instructions' => $orderItem->special_instructions,
                'prep_time' => $orderItem->menuItem->prep_time
            ];
        }

        // Save KOTs to database and generate print data
        $kots = [];
        foreach ($kotsByStation as $kotData) {
            $kot = $this->saveKOT($kotData);
            $kots[] = $kot;
        }

        return $kots;
    }

    /**
     * Save KOT to database
     */
    private function saveKOT(array $kotData): array
    {
        // For now, we'll store as JSON. Could be enhanced with dedicated KOT table
        $kotId = 'KOT_' . $kotData['order_id'] . '_' . $kotData['station_id'] . '_' . time();
        
        $kot = array_merge($kotData, [
            'kot_id' => $kotId,
            'status' => 'pending',
            'printed_at' => null,
            'completed_at' => null
        ]);

        // Store in order's metadata or dedicated table
        DB::table('order_kots')->insert([
            'kot_id' => $kotId,
            'order_id' => $kotData['order_id'],
            'station_id' => $kotData['station_id'],
            'kot_data' => json_encode($kot),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $kot;
    }

    /**
     * Update order status with state machine validation
     */
    public function updateOrderStatus(Order $order, string $newStatus, string $reason = null): bool
    {
        $currentStatus = $order->status;

        // Validate transition
        if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
            throw new \Exception("Invalid status transition from {$currentStatus} to {$newStatus}");
        }

        $oldStatus = $order->status;
        $order->update([
            'status' => $newStatus,
            'status_updated_at' => now(),
            'status_reason' => $reason
        ]);

        // Handle status-specific logic
        $this->handleStatusChange($order, $oldStatus, $newStatus);

        // Fire event
        event(new OrderStatusChanged($order, $oldStatus, $newStatus));

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'reason' => $reason
        ]);

        return true;
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
            case 'confirmed':
                // Commit inventory reservations
                $this->commitInventoryReservations($order);
                break;

            case 'cancelled':
                // Release inventory reservations
                $this->releaseInventoryReservations($order);
                break;

            case 'completed':
                // Mark inventory as consumed
                $this->markInventoryConsumed($order);
                // Generate completion notifications
                $this->sendCompletionNotifications($order);
                break;
        }
    }

    /**
     * Commit inventory reservations (convert to actual usage)
     */
    private function commitInventoryReservations(Order $order): void
    {
        if (!$order->inventory_reservations) {
            return;
        }

        foreach ($order->inventory_reservations as $reservation) {
            $inventoryItem = InventoryItem::find($reservation['inventory_item_id']);
            if ($inventoryItem) {
                // Move from reserved to actual consumption
                $inventoryItem->decrement('reserved_stock', $reservation['quantity_reserved']);
                $inventoryItem->decrement('current_stock', $reservation['quantity_reserved']);
            }
        }
    }

    /**
     * Mark inventory as consumed
     */
    private function markInventoryConsumed(Order $order): void
    {
        // Additional logic for tracking consumption, analytics, etc.
        Log::info('Inventory consumed for order', [
            'order_id' => $order->id,
            'items' => $order->inventory_reservations
        ]);
    }

    /**
     * Send completion notifications
     */
    private function sendCompletionNotifications(Order $order): void
    {
        // Implementation for SMS, email, push notifications
        // This would integrate with notification services
    }

    /**
     * Get real-time order status
     */
    public function getOrderStatus(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_updated_at' => $order->status_updated_at,
            'estimated_completion' => $this->calculateEstimatedCompletion($order),
            'kots' => $this->getOrderKOTs($order),
            'inventory_status' => $this->getInventoryStatus($order)
        ];
    }

    /**
     * Calculate estimated completion time
     */
    private function calculateEstimatedCompletion(Order $order): ?Carbon
    {
        if ($order->status === 'completed') {
            return null;
        }

        $totalPrepTime = $order->orderItems->sum(function($item) {
            return $item->menuItem->prep_time * $item->quantity;
        });

        // Add buffer time based on order type
        $bufferTime = $order->order_type === 'dine_in' ? 5 : 10;

        return $order->created_at->addMinutes($totalPrepTime + $bufferTime);
    }

    /**
     * Get KOTs for order
     */
    private function getOrderKOTs(Order $order): array
    {
        $kots = DB::table('order_kots')
            ->where('order_id', $order->id)
            ->get()
            ->map(function($kot) {
                return array_merge(json_decode($kot->kot_data, true), [
                    'status' => $kot->status,
                    'updated_at' => $kot->updated_at
                ]);
            });

        return $kots->toArray();
    }

    /**
     * Get inventory status for order
     */
    private function getInventoryStatus(Order $order): array
    {
        if (!$order->inventory_reservations) {
            return ['status' => 'not_tracked'];
        }

        $status = 'available';
        $issues = [];

        foreach ($order->inventory_reservations as $reservation) {
            $inventoryItem = InventoryItem::find($reservation['inventory_item_id']);
            if (!$inventoryItem) {
                $issues[] = "Inventory item not found: {$reservation['inventory_item_id']}";
                $status = 'issues';
                continue;
            }

            if ($inventoryItem->current_stock < $reservation['quantity_reserved']) {
                $issues[] = "Insufficient stock for item: {$inventoryItem->name}";
                $status = 'insufficient';
            }
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'reservations' => $order->inventory_reservations
        ];
    }
}
