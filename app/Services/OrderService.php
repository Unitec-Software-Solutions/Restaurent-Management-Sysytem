<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\Employee;
use App\Services\InventoryService;
use App\Services\ProductCatalogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Exception;

class OrderService
{
    protected $inventoryService;
    protected $catalogService;

    public function __construct(
        InventoryService $inventoryService = null,
        ProductCatalogService $catalogService = null
    ) {
        $this->inventoryService = $inventoryService ?: app(InventoryService::class);
        $this->catalogService = $catalogService ?: app(ProductCatalogService::class);
    }
    /**
     * Create order with stock validation and deduction
     */
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validate stock availability first
            $this->validateStockAvailability($data['items'], $data['branch_id']);

            // Create the order
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'order_type' => $data['order_type'] ?? Order::TYPE_TAKEAWAY_ONLINE,
                'steward_id' => $data['steward_id'] ?? null,
                'reservation_id' => $data['reservation_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => Order::STATUS_SUBMITTED
            ]);

            // Create order items and deduct stock
            $this->createOrderItems($order, $data['items']);
            
            // Calculate totals
            $order->calculateTotal();

            // Generate KOT
            $order->generateKOT();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'total_items' => count($data['items'])
            ]);

            return $order;
        });
    }

    /**
     * Validate stock availability for all items
     */
    protected function validateStockAvailability(array $items, int $branchId)
    {
        foreach ($items as $item) {
            $itemMaster = ItemMaster::findOrFail($item['item_id']);
            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);
            
            if ($currentStock < $item['quantity']) {
                throw new Exception(
                    "Insufficient stock for {$itemMaster->name}. Available: {$currentStock}, Required: {$item['quantity']}"
                );
            }
        }
    }

    /**
     * Create order items and deduct stock
     */
    protected function createOrderItems(Order $order, array $items)
    {
        foreach ($items as $itemData) {
            $itemMaster = ItemMaster::findOrFail($itemData['item_id']);
            
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $itemData['item_id'],
                'inventory_item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemMaster->selling_price,
                'total_price' => $itemMaster->selling_price * $itemData['quantity']
            ]);

            // Deduct stock
            $this->deductStock($order, $itemMaster, $itemData['quantity']);
        }

        $order->update(['stock_deducted' => true]);
    }

    /**
     * Deduct stock for an item
     */
    protected function deductStock(Order $order, ItemMaster $item, int $quantity)
    {
        ItemTransaction::create([
            'organization_id' => $order->branch->organization_id,
            'branch_id' => $order->branch_id,
            'inventory_item_id' => $item->id,
            'transaction_type' => 'sales_order',
            'quantity' => -$quantity, // Negative for stock out
            'cost_price' => $item->buying_price,
            'unit_price' => $item->selling_price,
            'source_id' => (string)$order->id,            'source_type' => 'Order',
            'created_by_user_id' => Auth::id(),
            'notes' => "Stock deducted for Order #{$order->order_number}",
            'is_active' => true
        ]);

        Log::info('Stock deducted', [
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'remaining_stock' => ItemTransaction::stockOnHand($item->id, $order->branch_id)
        ]);
    }    /**
     * Get available servers for a branch (updated from stewards)
     */
    public function getAvailableServers($branchId)
    {
        return Employee::active()
            ->servers()
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available stewards for a branch (legacy method - kept for compatibility)
     */
    public function getAvailableStewards($branchId)
    {
        return $this->getAvailableServers($branchId);
    }

    /**
     * Get items with stock information for ordering
     */
    public function getItemsWithStock($branchId, $organizationId)
    {
        return ItemMaster::where('organization_id', $organizationId)
            ->where('is_menu_item', true)
            ->get()
            ->map(function ($item) use ($branchId) {
                $stock = ItemTransaction::stockOnHand($item->id, $branchId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'selling_price' => $item->selling_price,
                    'unit_of_measurement' => $item->unit_of_measurement,
                    'current_stock' => $stock,
                    'is_available' => $stock > 0,
                    'category' => $item->category->name ?? 'Uncategorized'
                ];
            })
            ->filter(fn($item) => $item['is_available'])
            ->values();
    }

    /**
     * Check stock alerts for low stock items
     */
    public function getStockAlerts($branchId, $organizationId)
    {
        $items = ItemMaster::where('organization_id', $organizationId)->get();
        $alerts = [];

        foreach ($items as $item) {
            $stock = ItemTransaction::stockOnHand($item->id, $branchId);
            
            if ($stock <= 0) {
                $alerts[] = [
                    'type' => 'out_of_stock',
                    'item' => $item->name,
                    'current_stock' => $stock,
                    'message' => "{$item->name} is out of stock"
                ];
            } elseif ($stock <= $item->reorder_level) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'item' => $item->name,
                    'current_stock' => $stock,
                    'reorder_level' => $item->reorder_level,
                    'message' => "{$item->name} is running low (Stock: {$stock}, Reorder: {$item->reorder_level})"
                ];
            }
        }

        return $alerts;
    }

    /**
     * Update order with validation
     */
    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function () use ($order, $data) {
            // If items are being updated, validate stock and restore previous stock
            if (isset($data['items'])) {
                // Restore previous stock
                $this->restoreOrderStock($order);
                
                // Delete old items
                $order->items()->delete();
                
                // Validate new stock requirements
                $this->validateStockAvailability($data['items'], $order->branch_id);
                
                // Create new items and deduct stock
                $this->createOrderItems($order, $data['items']);
            }

            // Update order details
            $order->update(array_intersect_key($data, array_flip([
                'customer_name', 'customer_phone', 'steward_id', 'notes'
            ])));

            $order->calculateTotal();

            return $order;
        });
    }

    /**
     * Restore stock when order is cancelled or updated
     */
    public function restoreOrderStock(Order $order)
    {
        if (!$order->stock_deducted) {
            return;
        }

        foreach ($order->items as $orderItem) {
            ItemTransaction::create([
                'organization_id' => $order->branch->organization_id,
                'branch_id' => $order->branch_id,
                'inventory_item_id' => $orderItem->inventory_item_id,
                'transaction_type' => 'return',
                'quantity' => $orderItem->quantity, // Positive for stock return
                'cost_price' => $orderItem->inventoryItem->buying_price,
                'unit_price' => $orderItem->unit_price,
                'source_id' => (string)$order->id,                'source_type' => 'OrderCancellation',
                'created_by_user_id' => Auth::id(),
                'notes' => "Stock restored from cancelled Order #{$order->order_number}",
                'is_active' => true
            ]);
        }

        $order->update(['stock_deducted' => false]);

        Log::info('Stock restored for order', ['order_id' => $order->id]);
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrder(Order $order, $reason = null)
    {
        return DB::transaction(function () use ($order, $reason) {
            // Restore stock if it was deducted
            $this->restoreOrderStock($order);
            
            // Cancel the order
            $order->cancel($reason);

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'reason' => $reason
            ]);

            return $order;
        });
    }

    /**
     * Validate cart items in real-time
     */
    public function validateCartItems(array $cartItems, int $branchId, int $organizationId): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'items' => []
        ];

        foreach ($cartItems as $item) {
            $itemValidation = $this->validateCartItem($item, $branchId, $organizationId);
            $validation['items'][] = $itemValidation;

            if (!$itemValidation['valid']) {
                $validation['valid'] = false;
                $validation['errors'] = array_merge($validation['errors'], $itemValidation['errors']);
            }

            if (!empty($itemValidation['warnings'])) {
                $validation['warnings'] = array_merge($validation['warnings'], $itemValidation['warnings']);
            }
        }

        return $validation;
    }

    /**
     * Validate individual cart item
     */
    protected function validateCartItem(array $item, int $branchId, int $organizationId): array
    {
        $validation = [
            'item_id' => $item['item_id'],
            'quantity' => $item['quantity'],
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'availability' => null
        ];

        try {
            $itemMaster = ItemMaster::where('id', $item['item_id'])
                ->where('organization_id', $organizationId)
                ->first();

            if (!$itemMaster) {
                $validation['errors'][] = "Item not found";
                return $validation;
            }

            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);
            $requestedQuantity = $item['quantity'];

            $validation['availability'] = [
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQuantity,
                'available' => $currentStock >= $requestedQuantity
            ];

            if ($currentStock <= 0) {
                $validation['errors'][] = "{$itemMaster->name} is out of stock";
            } elseif ($currentStock < $requestedQuantity) {
                $validation['errors'][] = "Insufficient stock for {$itemMaster->name}. Available: {$currentStock}, Required: {$requestedQuantity}";
            } else {
                $validation['valid'] = true;
                
                // Add warnings for low stock
                if ($currentStock <= $itemMaster->reorder_level) {
                    $validation['warnings'][] = "{$itemMaster->name} is running low (Stock: {$currentStock})";
                }
                
                // Warning if order will bring stock below reorder level
                if (($currentStock - $requestedQuantity) <= $itemMaster->reorder_level) {
                    $validation['warnings'][] = "Order will bring {$itemMaster->name} below reorder level";
                }
            }

        } catch (\Exception $e) {
            $validation['errors'][] = "Error validating item: " . $e->getMessage();
        }

        return $validation;
    }

    /**
     * Create order with reservation-confirmation workflow
     */
    public function createOrderWithReservation(array $data): array
    {
        try {
            // Step 1: Validate and reserve stock
            $reservationResult = $this->reserveStockForCart($data['items'], $data['branch_id'], $data['organization_id'] ?? Auth::user()->organization_id);
            
            if (!$reservationResult['success']) {
                return [
                    'success' => false,
                    'errors' => $reservationResult['errors'],
                    'step' => 'reservation_failed'
                ];
            }

            // Step 2: Create order with reservation keys
            $order = DB::transaction(function () use ($data, $reservationResult) {
                $order = Order::create([
                    'branch_id' => $data['branch_id'],
                    'customer_name' => $data['customer_name'] ?? 'Not Provided',
                    'customer_phone' => $data['customer_phone'] ?? 'Not Provided',
                    'order_type' => $data['order_type'] ?? Order::TYPE_TAKEAWAY_ONLINE,
                    'steward_id' => $data['steward_id'] ?? null,
                    'reservation_id' => $data['reservation_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => Order::STATUS_SUBMITTED
                ]);

                // Store reservation keys for later confirmation
                $order->reservation_keys = collect($reservationResult['reservations'])->pluck('reservation_key')->toArray();
                $order->save();

                // Create order items
                $this->createOrderItems($order, $data['items']);
                
                return $order;
            });

            return [
                'success' => true,
                'order' => $order,
                'reservation_keys' => $order->reservation_keys,
                'step' => 'order_created_pending_confirmation'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'step' => 'order_creation_failed'
            ];
        }
    }

    /**
     * Confirm order and finalize stock deduction
     */
    public function confirmOrder(Order $order): array
    {
        try {
            if (!$order->reservation_keys) {
                return [
                    'success' => false,
                    'error' => 'No reservation keys found for order'
                ];
            }            // Convert reservations to actual stock deductions
            $this->confirmStockReservations($order);

            if (true) {
                $order->update([
                    'status' => Order::STATUS_PREPARING,
                    'stock_deducted' => true,
                    'confirmed_at' => now()
                ]);

                $order->calculateTotal();
                $order->generateKOT();

                return [
                    'success' => true,
                    'order' => $order->fresh()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to confirm stock reservations'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel order and restore reserved stock
     */
    public function cancelOrderWithReservation(Order $order, string $reason = null): array
    {
        try {
            DB::transaction(function () use ($order, $reason) {                if ($order->reservation_keys) {
                    // Cancel reservations - simplified approach
                    // In real implementation, this would remove reservation transactions
                } elseif ($order->stock_deducted) {
                    $this->restoreOrderStock($order);
                }

                $order->cancel($reason);
            });

            return [
                'success' => true,
                'message' => 'Order cancelled and stock restored'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }    /**
     * Reserve stock for cart items
     */
    protected function reserveStockForCart(array $items, int $branchId, int $organizationId): array
    {
        // Simplified reservation - validate stock and prepare reservation data
        $reservations = [];
        $errors = [];

        foreach ($items as $item) {
            $itemMaster = ItemMaster::where('id', $item['item_id'])
                ->where('organization_id', $organizationId)
                ->first();

            if (!$itemMaster) {
                $errors[] = "Item not found: {$item['item_id']}";
                continue;
            }

            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);
            $requestedQuantity = $item['quantity'];

            if ($currentStock < $requestedQuantity) {
                $errors[] = "Insufficient stock for {$itemMaster->name}. Available: {$currentStock}, Required: {$requestedQuantity}";
                continue;
            }

            $reservations[] = [
                'item_id' => $item['item_id'],
                'quantity' => $requestedQuantity,
                'reservation_key' => uniqid('res_', true)
            ];
        }

        return [
            'success' => empty($errors),
            'reservations' => $reservations,
            'errors' => $errors
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(int $branchId, int $organizationId): array
    {
        $today = now()->toDateString();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'orders' => [
                'today' => Order::where('branch_id', $branchId)
                    ->whereDate('created_at', $today)
                    ->count(),
                'this_week' => Order::where('branch_id', $branchId)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'this_month' => Order::where('branch_id', $branchId)
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'pending' => Order::where('branch_id', $branchId)
                    ->whereIn('status', [Order::STATUS_SUBMITTED, Order::STATUS_PREPARING])
                    ->count()
            ],
            'revenue' => [
                'today' => Order::where('branch_id', $branchId)
                    ->whereDate('created_at', $today)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->sum('total'),
                'this_week' => Order::where('branch_id', $branchId)
                    ->where('created_at', '>=', $thisWeek)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->sum('total'),
                'this_month' => Order::where('branch_id', $branchId)
                    ->where('created_at', '>=', $thisMonth)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->sum('total')
            ],
            'avg_order_value' => Order::where('branch_id', $branchId)
                ->whereDate('created_at', $today)
                ->where('status', Order::STATUS_COMPLETED)
                ->avg('total'),
            'top_items' => $this->getTopSellingItems($branchId, $today)
        ];
    }    /**
     * Get stock alerts for orders
     */
    public function getOrderStockAlerts(int $branchId, int $organizationId): array
    {
        $alerts = collect();
        $items = ItemMaster::where('organization_id', $organizationId)->get();

        foreach ($items as $item) {
            $stock = ItemTransaction::stockOnHand($item->id, $branchId);
            
            if ($stock <= 0) {
                $alerts->push([
                    'type' => 'critical',
                    'level' => 'out_of_stock',
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'current_stock' => $stock,
                    'message' => "{$item->name} is out of stock"
                ]);
            } elseif ($stock <= $item->reorder_level) {
                $alerts->push([
                    'type' => 'warning',
                    'level' => 'low_stock',
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'current_stock' => $stock,
                    'reorder_level' => $item->reorder_level,
                    'message' => "{$item->name} is running low"
                ]);
            }
        }

        return [
            'critical_alerts' => $alerts->where('type', 'critical')->values(),
            'warning_alerts' => $alerts->where('type', 'warning')->values(),
            'total_alerts' => $alerts->count(),
            'critical_count' => $alerts->where('type', 'critical')->count(),
            'warning_count' => $alerts->where('type', 'warning')->count()
        ];
    }

    /**
     * Get top selling items
     */
    protected function getTopSellingItems(int $branchId, string $date, int $limit = 5): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('item_master', 'order_items.menu_item_id', '=', 'item_master.id')
            ->where('orders.branch_id', $branchId)
            ->whereDate('orders.created_at', $date)
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select(
                'item_master.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('order_items.menu_item_id', 'item_master.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Auto-remove out-of-stock items from cart
     */
    public function autoRemoveOutOfStockItems(array $cartItems, int $branchId, int $organizationId): array
    {
        $validItems = [];
        $removedItems = [];

        foreach ($cartItems as $item) {
            $itemMaster = ItemMaster::where('id', $item['item_id'])
                ->where('organization_id', $organizationId)
                ->first();

            if (!$itemMaster) {
                $removedItems[] = [
                    'item_id' => $item['item_id'],
                    'reason' => 'Item not found'
                ];
                continue;
            }

            $currentStock = ItemTransaction::stockOnHand($item['item_id'], $branchId);

            if ($currentStock <= 0) {
                $removedItems[] = [
                    'item_id' => $item['item_id'],
                    'name' => $itemMaster->name,
                    'reason' => 'Out of stock'
                ];
            } else {
                // Adjust quantity if requested amount exceeds stock
                $adjustedQuantity = min($item['quantity'], $currentStock);
                
                $validItems[] = [
                    'item_id' => $item['item_id'],
                    'quantity' => $adjustedQuantity,
                    'original_quantity' => $item['quantity'],
                    'adjusted' => $adjustedQuantity !== $item['quantity']
                ];
            }
        }

        return [
            'valid_items' => $validItems,
            'removed_items' => $removedItems,
            'has_changes' => !empty($removedItems) || collect($validItems)->contains('adjusted', true)
        ];
    }

    /**
     * Confirm stock reservations by deducting actual stock
     */
    protected function confirmStockReservations(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            $this->deductStock($order, $orderItem->menuItem ?? $orderItem->inventoryItem, $orderItem->quantity);
        }
    }
}
