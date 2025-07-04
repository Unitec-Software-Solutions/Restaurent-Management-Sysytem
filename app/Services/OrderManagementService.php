<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use App\Models\Kot;
use App\Models\KotItem;
use App\Models\KitchenStation;
use App\Models\MenuItem;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Phase 2: Order Management Service
 * Real-time inventory checks, KOT generation, order state machine, stock reservation
 */
class OrderManagementService
{
    // Order status state machine
    const ORDER_STATES = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['served', 'cancelled'],
        'served' => ['completed'],
        'completed' => [],
        'cancelled' => []
    ];

    // KOT status states
    const KOT_STATES = [
        'pending' => ['started', 'cancelled'],
        'started' => ['cooking', 'cancelled'],
        'cooking' => ['ready', 'cancelled'],
        'ready' => ['served'],
        'served' => [],
        'cancelled' => []
    ];

    /**
     * Install real-time order management system
     */
    public function installRealTimeSystem(): void
    {
        $this->setupInventoryChecks();
        $this->setupKotGeneration();
        $this->setupOrderStateMachine();
        $this->setupStockReservation();
        
        Log::info('OrderManagementService: Real-time system installed');
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
                'batch_update_enabled' => true
            ];
        });
    }

    /**
     * Setup KOT generation system
     */
    private function setupKotGeneration(): void
    {
        // KOT generation rules
        Cache::remember('kot_generation_rules', 1800, function () {
            return [
                'auto_generate' => true,
                'group_by_station' => true,
                'include_allergens' => true,
                'print_queue_enabled' => true,
                'priority_items' => ['appetizers', 'mains', 'desserts']
            ];
        });
    }

    /**
     * Setup order state machine
     */
    private function setupOrderStateMachine(): void
    {
        // State transition rules
        Cache::remember('order_state_rules', 1800, function () {
            return [
                'auto_transitions' => ['pending' => 'confirmed'],
                'notification_states' => ['confirmed', 'ready', 'served'],
                'timeout_states' => ['preparing' => 1800, 'ready' => 600], // seconds
                'requires_approval' => ['cancelled']
            ];
        });
    }

    /**
     * Setup stock reservation system
     */
    private function setupStockReservation(): void
    {
        // Stock reservation rules
        Cache::remember('stock_reservation_rules', 1800, function () {
            return [
                'reservation_duration' => 300, // 5 minutes
                'auto_release_on_cancel' => true,
                'overbooking_allowed' => false,
                'priority_reservations' => true
            ];
        });
    }

    /**
     * Create order with real-time inventory validation and KOT generation
     */
    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            try {
                // 1. Validate inventory availability in real-time
                $this->validateInventoryAvailability($orderData['items']);
                
                // 2. Reserve stock for order items
                $reservations = $this->reserveStock($orderData['items']);
                
                // 3. Create order with reserved stock info
                $order = Order::create([
                    'branch_id' => $orderData['branch_id'],
                    'organization_id' => $orderData['organization_id'] ?? Branch::find($orderData['branch_id'])->organization_id,
                    'order_number' => $this->generateOrderNumber($orderData['branch_id']),
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $orderData['customer_phone'] ?? null,
                    'customer_email' => $orderData['customer_email'] ?? null,
                    'table_id' => $orderData['table_id'] ?? null,
                    'steward_id' => $orderData['steward_id'] ?? null,
                    'order_type' => $orderData['order_type'] ?? 'dine_in',
                    'status' => 'pending',
                    'subtotal' => $orderData['subtotal'],
                    'tax_amount' => $orderData['tax_amount'] ?? 0,
                    'discount_amount' => $orderData['discount_amount'] ?? 0,
                    'service_charge' => $orderData['service_charge'] ?? 0,
                    'total_amount' => $orderData['total_amount'],
                    'notes' => $orderData['notes'] ?? null,
                    'special_instructions' => $orderData['special_instructions'] ?? null,
                    'estimated_preparation_time' => $this->calculatePreparationTime($orderData['items']),
                    'stock_reservations' => json_encode($reservations),
                    'ordered_at' => now(),
                ]);
                
                // 4. Create order items with detailed tracking
                foreach ($orderData['items'] as $itemData) {
                    $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $itemData['menu_item_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['total_price'],
                        'special_instructions' => $itemData['special_instructions'] ?? null,
                        'allergen_notes' => $itemData['allergen_notes'] ?? null,
                        'preparation_priority' => $menuItem->preparation_priority ?? 'normal',
                        'estimated_time' => $menuItem->preparation_time ?? 15,
                        'kitchen_station_id' => $menuItem->kitchen_station_id
                    ]);
                }
                
                // 5. Generate KOTs for kitchen stations
                $this->generateKots($order);
                
                // 6. Transition to confirmed state if auto-confirmation enabled
                $this->transitionOrderState($order, 'confirmed');
                
                // 7. Log order creation
                Log::info('Order created successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'branch_id' => $order->branch_id,
                    'total_amount' => $order->total_amount,
                    'items_count' => count($orderData['items'])
                ]);
                
                return $order;
                
            } catch (Exception $e) {
                Log::error('Order creation failed', [
                    'error' => $e->getMessage(),
                    'order_data' => $orderData
                ]);
                throw $e;
            }
        });
    }

    /**
     * Validate inventory availability for order items
     */
    private function validateInventoryAvailability(array $items): void
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::with(['inventoryItems'])->findOrFail($item['menu_item_id']);
            
            // Skip items that don't require inventory tracking
            if (!$menuItem->requires_inventory_check) {
                continue;
            }
            
            $this->checkMenuItemInventory($menuItem, $item['quantity']);
        }
    }

    /**
     * Check specific menu item inventory availability
     */
    private function checkMenuItemInventory(MenuItem $menuItem, int $quantity): void
    {
        foreach ($menuItem->inventoryItems as $inventoryItem) {
            $requiredQuantity = $inventoryItem->pivot->quantity_required * $quantity;
            $availableStock = $this->getAvailableStock($inventoryItem->id, $menuItem->branch_id);
            
            if ($availableStock < $requiredQuantity) {
                throw new Exception(
                    "Insufficient stock for {$menuItem->name}. Required: {$requiredQuantity}, Available: {$availableStock}"
                );
            }
        }
    }

    /**
     * Get real-time available stock for inventory item
     */
    private function getAvailableStock(int $inventoryItemId, int $branchId): float
    {
        $cacheKey = "inventory_stock_{$branchId}_{$inventoryItemId}";
        
        return Cache::remember($cacheKey, 30, function () use ($inventoryItemId, $branchId) {
            $inventoryItem = InventoryItem::where('id', $inventoryItemId)
                ->where('branch_id', $branchId)
                ->first();
                
            if (!$inventoryItem) {
                return 0;
            }
            
            // Calculate available stock (current stock - reserved stock)
            $reservedStock = $this->getReservedStock($inventoryItemId, $branchId);
            return max(0, $inventoryItem->current_stock - $reservedStock);
        });
    }

    /**
     * Get currently reserved stock for inventory item
     */
    private function getReservedStock(int $inventoryItemId, int $branchId): float
    {
        // Get active reservations (not expired)
        $activeReservations = Cache::get("stock_reservations_{$branchId}_{$inventoryItemId}", []);
        
        $totalReserved = 0;
        $currentTime = now();
        
        foreach ($activeReservations as $reservation) {
            $expiresAt = Carbon::parse($reservation['expires_at']);
            
            if ($currentTime->lessThan($expiresAt)) {
                $totalReserved += $reservation['quantity'];
            }
        }
        
        return $totalReserved;
    }

    /**
     * Reserve stock for order items
     */
    private function reserveStock(array $items): array
    {
        $reservations = [];
        $reservationId = 'rsv_' . uniqid();
        $expiresAt = now()->addMinutes(5); // 5-minute reservation
        
        foreach ($items as $item) {
            $menuItem = MenuItem::with(['inventoryItems'])->findOrFail($item['menu_item_id']);
            
            if (!$menuItem->requires_inventory_check) {
                continue;
            }
            
            foreach ($menuItem->inventoryItems as $inventoryItem) {
                $reserveQuantity = $inventoryItem->pivot->quantity_required * $item['quantity'];
                
                // Create reservation entry
                $reservation = [
                    'reservation_id' => $reservationId,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => $reserveQuantity,
                    'created_at' => now()->toISOString(),
                    'expires_at' => $expiresAt->toISOString(),
                    'menu_item_id' => $menuItem->id,
                    'order_quantity' => $item['quantity']
                ];
                
                // Store reservation in cache
                $cacheKey = "stock_reservations_{$menuItem->branch_id}_{$inventoryItem->id}";
                $activeReservations = Cache::get($cacheKey, []);
                $activeReservations[] = $reservation;
                Cache::put($cacheKey, $activeReservations, 600); // 10 minutes
                
                $reservations[] = $reservation;
            }
        }
        
        return $reservations;
    }

    /**
     * Calculate total preparation time for order items
     */
    private function calculatePreparationTime(array $items): int
    {
        $maxTime = 0;
        $stationTimes = [];
        
        foreach ($items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $stationId = $menuItem->kitchen_station_id ?? 'default';
            $itemTime = ($menuItem->preparation_time ?? 15) * $item['quantity'];
            
            // Group by kitchen station for parallel cooking
            if (!isset($stationTimes[$stationId])) {
                $stationTimes[$stationId] = 0;
            }
            $stationTimes[$stationId] += $itemTime;
        }
        
        // Return maximum time across all stations (parallel execution)
        return empty($stationTimes) ? 15 : max($stationTimes);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(int $branchId): string
    {
        $date = now()->format('Ymd');
        $branchCode = str_pad($branchId, 3, '0', STR_PAD_LEFT);
        
        // Get daily sequence number
        $sequenceKey = "order_sequence_{$branchId}_{$date}";
        $sequence = Cache::increment($sequenceKey);
        
        if ($sequence === 1) {
            Cache::put($sequenceKey, 1, now()->endOfDay());
        }
        
        return "ORD-{$branchCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique KOT number
     */
    private function generateKotNumber(int $branchId): string
    {
        $date = now()->format('Ymd');
        $branchCode = str_pad($branchId, 3, '0', STR_PAD_LEFT);
        
        // Get daily KOT sequence number
        $sequenceKey = "kot_sequence_{$branchId}_{$date}";
        $sequence = Cache::increment($sequenceKey);
        
        if ($sequence === 1) {
            Cache::put($sequenceKey, 1, now()->endOfDay());
        }
        
        return "KOT-{$branchCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate KOTs (Kitchen Order Tickets) for order
     */
    private function generateKots(Order $order): array
    {
        $kots = [];
        $orderItems = $order->orderItems()->with(['menuItem', 'menuItem.kitchenStation'])->get();
        
        // Group items by kitchen station
        $stationGroups = $orderItems->groupBy(function ($item) {
            return $item->menuItem->kitchen_station_id ?? 'default';
        });
        
        foreach ($stationGroups as $stationId => $items) {
            $kot = Kot::create([
                'order_id' => $order->id,
                'kitchen_station_id' => $stationId === 'default' ? null : $stationId,
                'kot_number' => $this->generateKotNumber($order->branch_id),
                'status' => 'pending',
                'priority' => $this->calculateKotPriority($items),
                'estimated_time' => $items->sum(function ($item) {
                    return ($item->menuItem->preparation_time ?? 15) * $item->quantity;
                }),
                'created_at' => now(),
                'instructions' => $order->special_instructions
            ]);
            
            // Create KOT items
            foreach ($items as $orderItem) {
                KotItem::create([
                    'kot_id' => $kot->id,
                    'order_item_id' => $orderItem->id,
                    'menu_item_id' => $orderItem->menu_item_id,
                    'quantity' => $orderItem->quantity,
                    'special_instructions' => $orderItem->special_instructions,
                    'allergen_notes' => $orderItem->allergen_notes,
                    'priority' => $orderItem->preparation_priority ?? 'normal'
                ]);
            }
            
            $kots[] = $kot;
            
            // Log KOT generation
            Log::info('KOT generated', [
                'kot_id' => $kot->id,
                'kot_number' => $kot->kot_number,
                'order_id' => $order->id,
                'station_id' => $stationId,
                'items_count' => $items->count()
            ]);
        }
        
        return $kots;
    }

    /**
     * Calculate KOT priority based on items
     */
    private function calculateKotPriority($items): string
    {
        $highPriorityItems = $items->filter(function ($item) {
            return ($item->preparation_priority ?? 'normal') === 'high';
        });
        
        if ($highPriorityItems->count() > 0) {
            return 'high';
        }
        
        return 'normal';
    }

    /**
     * Transition order to new state
     */
    public function transitionOrderState(Order $order, string $newState): bool
    {
        $currentState = $order->status;
        
        // Validate state transition
        if (!$this->isValidTransition($currentState, $newState)) {
            Log::warning('Invalid order state transition attempted', [
                'order_id' => $order->id,
                'from_state' => $currentState,
                'to_state' => $newState
            ]);
            return false;
        }
        
        // Update order status
        $order->update([
            'status' => $newState,
            'status_updated_at' => now()
        ]);
        
        // Handle state-specific actions
        $this->handleStateTransition($order, $currentState, $newState);
        
        Log::info('Order state transitioned', [
            'order_id' => $order->id,
            'from_state' => $currentState,
            'to_state' => $newState
        ]);
        
        return true;
    }

    /**
     * Check if state transition is valid
     */
    private function isValidTransition(string $currentState, string $newState): bool
    {
        return in_array($newState, self::ORDER_STATES[$currentState] ?? []);
    }

    /**
     * Handle state-specific transition actions
     */
    private function handleStateTransition(Order $order, string $fromState, string $toState): void
    {
        switch ($toState) {
            case 'confirmed':
                // Send notification to kitchen
                $this->notifyKitchen($order);
                break;
                
            case 'preparing':
                // Start preparation timer
                $order->update(['preparation_started_at' => now()]);
                break;
                
            case 'ready':
                // Notify service staff
                $this->notifyServiceStaff($order);
                break;
                
            case 'served':
                // Record service time
                $order->update(['served_at' => now()]);
                break;
                
            case 'completed':
                // Release stock reservations
                $this->releaseStockReservations($order);
                // Update inventory levels
                $this->updateInventoryLevels($order);
                break;
                
            case 'cancelled':
                // Release stock reservations
                $this->releaseStockReservations($order);
                // Cancel KOTs
                $this->cancelOrderKots($order);
                break;
        }
    }

    /**
     * Release stock reservations for order
     */
    private function releaseStockReservations(Order $order): void
    {
        if (!$order->stock_reservations) {
            return;
        }
        
        $reservations = json_decode($order->stock_reservations, true);
        
        foreach ($reservations as $reservation) {
            $cacheKey = "stock_reservations_{$order->branch_id}_{$reservation['inventory_item_id']}";
            $activeReservations = Cache::get($cacheKey, []);
            
            // Remove this reservation
            $activeReservations = array_filter($activeReservations, function ($res) use ($reservation) {
                return $res['reservation_id'] !== $reservation['reservation_id'];
            });
            
            Cache::put($cacheKey, array_values($activeReservations), 600);
        }
        
        Log::info('Stock reservations released', [
            'order_id' => $order->id,
            'reservations_count' => count($reservations)
        ]);
    }

    /**
     * Update inventory levels after order completion
     */
    private function updateInventoryLevels(Order $order): void
    {
        $orderItems = $order->orderItems()->with(['menuItem.inventoryItems'])->get();
        
        foreach ($orderItems as $orderItem) {
            if (!$orderItem->menuItem->requires_inventory_check) {
                continue;
            }
            
            foreach ($orderItem->menuItem->inventoryItems as $inventoryItem) {
                $consumedQuantity = $inventoryItem->pivot->quantity_required * $orderItem->quantity;
                
                // Update inventory
                $branchInventory = InventoryItem::where('item_master_id', $inventoryItem->id)
                    ->where('branch_id', $order->branch_id)
                    ->first();
                    
                if ($branchInventory) {
                    $branchInventory->decrement('current_stock', $consumedQuantity);
                    $branchInventory->update(['last_updated' => now()]);
                    
                    // Clear cache for this inventory item
                    Cache::forget("inventory_stock_{$order->branch_id}_{$inventoryItem->id}");
                }
            }
        }
        
        Log::info('Inventory levels updated', [
            'order_id' => $order->id,
            'branch_id' => $order->branch_id
        ]);
    }

    /**
     * Cancel all KOTs for an order
     */
    private function cancelOrderKots(Order $order): void
    {
        $kots = Kot::where('order_id', $order->id)
            ->whereNotIn('status', ['served', 'cancelled'])
            ->get();
            
        foreach ($kots as $kot) {
            $kot->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
        }
        
        Log::info('Order KOTs cancelled', [
            'order_id' => $order->id,
            'kots_cancelled' => $kots->count()
        ]);
    }

    /**
     * Notify kitchen about new order
     */
    private function notifyKitchen(Order $order): void
    {
        // This would integrate with real-time notification system
        Log::info('Kitchen notification sent', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Notify service staff about ready order
     */
    private function notifyServiceStaff(Order $order): void
    {
        // This would integrate with real-time notification system
        Log::info('Service staff notification sent', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Get order with real-time status
     */
    public function getOrderWithStatus(int $orderId): ?Order
    {
        $order = Order::with([
            'orderItems.menuItem',
            'kots.kotItems',
            'branch',
            'organization'
        ])->find($orderId);
        
        if (!$order) {
            return null;
        }
        
        // Add real-time status calculations
        $order->setAttribute('real_time_status', $this->calculateRealTimeStatus($order));
        $order->setAttribute('preparation_progress', $this->calculatePreparationProgress($order));
        $order->setAttribute('estimated_completion', $this->calculateEstimatedCompletion($order));
        
        return $order;
    }

    /**
     * Calculate real-time order status
     */
    private function calculateRealTimeStatus(Order $order): array
    {
        $kots = $order->kots;
        $totalKots = $kots->count();
        $completedKots = $kots->where('status', 'served')->count();
        $activeKots = $kots->whereNotIn('status', ['served', 'cancelled'])->count();
        
        return [
            'overall_status' => $order->status,
            'kots_total' => $totalKots,
            'kots_completed' => $completedKots,
            'kots_active' => $activeKots,
            'completion_percentage' => $totalKots > 0 ? round(($completedKots / $totalKots) * 100, 2) : 0
        ];
    }

    /**
     * Calculate preparation progress
     */
    private function calculatePreparationProgress(Order $order): array
    {
        $items = $order->orderItems;
        $totalItems = $items->count();
        $estimatedTime = $order->estimated_preparation_time ?? 30;
        
        $timeSinceStart = $order->preparation_started_at 
            ? now()->diffInMinutes($order->preparation_started_at)
            : 0;
            
        $progressPercentage = $estimatedTime > 0 
            ? min(100, round(($timeSinceStart / $estimatedTime) * 100, 2))
            : 0;
        
        return [
            'total_items' => $totalItems,
            'estimated_time_minutes' => $estimatedTime,
            'elapsed_time_minutes' => $timeSinceStart,
            'progress_percentage' => $progressPercentage,
            'is_overdue' => $timeSinceStart > $estimatedTime
        ];
    }

    /**
     * Calculate estimated completion time
     */
    private function calculateEstimatedCompletion(Order $order): ?Carbon
    {
        if (!$order->preparation_started_at) {
            return null;
        }
        
        $estimatedTime = $order->estimated_preparation_time ?? 30;
        return $order->preparation_started_at->addMinutes($estimatedTime);
    }

    /**
     * Update KOT status
     */
    public function updateKotStatus(int $kotId, string $newStatus): bool
    {
        $kot = Kot::find($kotId);
        
        if (!$kot) {
            return false;
        }
        
        $currentStatus = $kot->status;
        
        // Validate KOT state transition
        if (!$this->isValidKotTransition($currentStatus, $newStatus)) {
            Log::warning('Invalid KOT state transition attempted', [
                'kot_id' => $kotId,
                'from_status' => $currentStatus,
                'to_status' => $newStatus
            ]);
            return false;
        }
        
        // Update KOT status
        $kot->update([
            'status' => $newStatus,
            'status_updated_at' => now()
        ]);
        
        // Handle status-specific actions
        if ($newStatus === 'started') {
            $kot->update(['started_at' => now()]);
        } elseif ($newStatus === 'ready') {
            $kot->update(['completed_at' => now()]);
        } elseif ($newStatus === 'served') {
            $kot->update(['served_at' => now()]);
        }
        
        // Check if all KOTs are completed to update order status
        $this->checkOrderCompletionStatus($kot->order);
        
        Log::info('KOT status updated', [
            'kot_id' => $kotId,
            'from_status' => $currentStatus,
            'to_status' => $newStatus
        ]);
        
        return true;
    }

    /**
     * Check if KOT state transition is valid
     */
    private function isValidKotTransition(string $currentStatus, string $newStatus): bool
    {
        return in_array($newStatus, self::KOT_STATES[$currentStatus] ?? []);
    }

    /**
     * Check if order should be marked as ready/completed
     */
    private function checkOrderCompletionStatus(Order $order): void
    {
        $kots = $order->kots;
        $allServed = $kots->every(function ($kot) {
            return $kot->status === 'served';
        });
        
        $allReady = $kots->every(function ($kot) {
            return in_array($kot->status, ['ready', 'served']);
        });
        
        if ($allServed && $order->status !== 'served') {
            $this->transitionOrderState($order, 'served');
        } elseif ($allReady && $order->status === 'preparing') {
            $this->transitionOrderState($order, 'ready');
        }
    }
}
