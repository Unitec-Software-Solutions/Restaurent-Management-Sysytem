<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryService
{
    /**
     * Check real-time availability for menu items
     */
    public function checkMenuItemAvailability(int $menuItemId, int $branchId, int $quantity = 1): array
    {
        $menuItem = MenuItem::with(['recipes.ingredient'])->find($menuItemId);
        
        if (!$menuItem) {
            return [
                'available' => false,
                'reason' => 'Menu item not found',
                'max_quantity' => 0,
                'missing_ingredients' => []
            ];
        }

        return $menuItem->checkAvailability($branchId, $quantity);
    }

    /**
     * Get current stock level for an inventory item
     */
    public function getCurrentStock(int $itemId, int $branchId): float
    {
        return ItemTransaction::where('inventory_item_id', $itemId)
            ->where('branch_id', $branchId)
            ->sum('quantity');
    }

    /**
     * Check if item is low stock (below reorder level)
     */
    public function isLowStock(int $itemId, int $branchId): bool
    {
        $item = ItemMaster::find($itemId);
        if (!$item) return false;

        $currentStock = $this->getCurrentStock($itemId, $branchId);
        return $currentStock <= $item->reorder_level;
    }

    /**
     * Get low stock items for a branch
     */
    public function getLowStockItems(int $branchId): array
    {
        $items = ItemMaster::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        $lowStockItems = [];
        
        foreach ($items as $item) {
            $currentStock = $this->getCurrentStock($item->id, $branchId);
            if ($currentStock <= $item->reorder_level) {
                $lowStockItems[] = [
                    'item' => $item,
                    'current_stock' => $currentStock,
                    'reorder_level' => $item->reorder_level,
                    'shortage' => $item->reorder_level - $currentStock,
                    'affected_menu_items' => $this->getAffectedMenuItems($item->id)
                ];
            }
        }

        return $lowStockItems;
    }    /**
     * Get menu items affected by ingredient shortage
     */
    public function getAffectedMenuItems(int $ingredientId): array
    {
        return Recipe::where('ingredient_item_id', $ingredientId)
            ->where('is_active', true)
            ->with('menuItem')
            ->get()
            ->pluck('menuItem')
            ->filter()
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Get available menu items for a branch with real-time stock checking
     */
    public function getAvailableMenuItems(int $branchId, array $filters = []): array
    {
        $query = MenuItem::with(['category', 'recipes.ingredient'])
            ->where('branch_id', $branchId)
            ->where('is_active', true);

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('menu_category_id', $filters['category_id']);
        }

        if (isset($filters['availability_status'])) {
            // This will be filtered after we check real-time availability
        }

        $items = $query->get();
        $availableItems = [];

        foreach ($items as $item) {
            $availability = $item->checkAvailability($branchId);
            
            $itemData = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'category' => $item->category->name ?? 'Uncategorized',
                'image_path' => $item->image_path,
                'available' => $availability['available'],
                'max_quantity' => $availability['max_quantity'],
                'limiting_factor' => $availability['limiting_factor'],
                'missing_ingredients' => $availability['missing_ingredients'],
                'status' => $item->availability_status,
                'stock_percentage' => $item->stock_percentage,
                'requires_preparation' => $item->requires_preparation,
                'preparation_time' => $item->preparation_time,
                'allergens' => $item->allergens,
                'is_vegetarian' => $item->is_vegetarian,
                'contains_alcohol' => $item->contains_alcohol,
            ];

            // Apply availability filter if set
            if (isset($filters['availability_status'])) {
                if ($filters['availability_status'] !== $itemData['status']) {
                    continue;
                }
            }

            $availableItems[] = $itemData;
        }

        return $availableItems;
    }

    /**
     * Get sellable inventory items (items that can be sold directly)
     */
    public function getSellableInventoryItems(int $branchId): array
    {
        $items = ItemMaster::where('branch_id', $branchId)
            ->where('is_menu_item', true)
            ->active()
            ->with(['category'])
            ->get();

        $sellableItems = [];

        foreach ($items as $item) {
            $currentStock = $this->getCurrentStock($item->id, $branchId);
            $stockStatus = $item->getStockStatus($branchId);

            $sellableItems[] = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'selling_price' => $item->selling_price,
                'category' => $item->category->name ?? 'Uncategorized',
                'current_stock' => $currentStock,
                'stock_status' => $stockStatus,
                'stock_percentage' => $item->getStockPercentage($branchId),
                'unit' => $item->unit_of_measurement,
                'available' => $currentStock > 0,
                'is_perishable' => $item->is_perishable,
                'shelf_life_days' => $item->shelf_life_in_days,
            ];
        }

        return $sellableItems;
    }

    /**
     * Reserve stock for order items (prevents overselling)
     */
    public function reserveStockForOrder(array $orderItems, int $branchId, string $orderId): bool
    {
        DB::beginTransaction();
        
        try {
            foreach ($orderItems as $orderItem) {
                if (isset($orderItem['menu_item_id'])) {
                    // Reserve menu item ingredients
                    $menuItem = MenuItem::find($orderItem['menu_item_id']);
                    if (!$menuItem || !$menuItem->reserveIngredients($branchId, $orderItem['quantity'])) {
                        throw new \Exception("Cannot reserve ingredients for menu item: {$menuItem->name}");
                    }
                } elseif (isset($orderItem['inventory_item_id'])) {
                    // Reserve inventory item stock
                    if (!$this->reserveInventoryItem($orderItem['inventory_item_id'], $branchId, $orderItem['quantity'], $orderId)) {
                        throw new \Exception("Cannot reserve inventory item");
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock reservation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Release reserved stock (on order cancellation)
     */
    public function releaseReservedStock(string $orderId): bool
    {
        DB::beginTransaction();
        
        try {
            // Remove reservation transactions
            ItemTransaction::where('reference_id', $orderId)
                ->where('transaction_type', 'reservation')
                ->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock release failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirm order and convert reservations to actual consumption
     */
    public function confirmOrderStock(string $orderId): bool
    {
        DB::beginTransaction();
        
        try {
            // Convert reservations to consumption
            $reservations = ItemTransaction::where('reference_id', $orderId)
                ->where('transaction_type', 'reservation')
                ->get();

            foreach ($reservations as $reservation) {
                // Create consumption record
                ItemTransaction::create([
                    'inventory_item_id' => $reservation->inventory_item_id,
                    'branch_id' => $reservation->branch_id,
                    'transaction_type' => 'consumption',
                    'quantity' => $reservation->quantity, // Already negative
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'notes' => 'Order confirmation - converted from reservation',
                    'transaction_date' => now(),
                ]);

                // Remove reservation
                $reservation->delete();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order stock confirmation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reserve individual inventory item
     */
    private function reserveInventoryItem(int $itemId, int $branchId, int $quantity, string $orderId): bool
    {
        $currentStock = $this->getCurrentStock($itemId, $branchId);
        
        if ($currentStock < $quantity) {
            return false;
        }

        // Create negative reservation transaction
        ItemTransaction::create([
            'inventory_item_id' => $itemId,
            'branch_id' => $branchId,
            'transaction_type' => 'reservation',
            'quantity' => -$quantity,
            'reference_type' => 'order',
            'reference_id' => $orderId,
            'notes' => 'Stock reserved for order',
            'transaction_date' => now(),
        ]);

        return true;
    }

    /**
     * Get real-time stock summary for dashboard
     */
    public function getStockSummary(int $branchId): array
    {
        $totalItems = ItemMaster::where('branch_id', $branchId)->active()->count();
        $lowStockItems = $this->getLowStockItems($branchId);
        $outOfStockItems = ItemMaster::where('branch_id', $branchId)
            ->active()
            ->get()
            ->filter(function($item) use ($branchId) {
                return $item->isOutOfStock($branchId);
            });

        $unavailableMenuItems = MenuItem::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get()
            ->filter(function($item) use ($branchId) {
                return !$item->checkAvailability($branchId)['available'];
            });

        return [
            'total_inventory_items' => $totalItems,
            'low_stock_count' => count($lowStockItems),
            'out_of_stock_count' => $outOfStockItems->count(),
            'unavailable_menu_items_count' => $unavailableMenuItems->count(),
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems->values()->toArray(),
            'unavailable_menu_items' => $unavailableMenuItems->values()->toArray(),
        ];
    }

    /**
     * Reserve ingredients for an order (temporary hold)
     */
    public function reserveIngredients(array $orderItems, int $branchId, string $reservationId): array
    {
        $reservedItems = [];
        $failedItems = [];

        DB::beginTransaction();

        try {
            foreach ($orderItems as $orderItem) {
                $menuItem = MenuItem::with('recipes.ingredient')->find($orderItem['menu_item_id']);
                $quantity = $orderItem['quantity'];

                if (!$menuItem) {
                    $failedItems[] = "Menu item {$orderItem['menu_item_id']} not found";
                    continue;
                }

                $availability = $menuItem->checkAvailability($branchId, $quantity);
                
                if (!$availability['available']) {
                    $failedItems[] = "Insufficient stock for {$menuItem->name}";
                    continue;
                }

                // Create reservation transactions
                foreach ($menuItem->recipes as $recipe) {
                    $reservationQuantity = -($recipe->actual_quantity_needed * $quantity);
                    
                    $transaction = ItemTransaction::create([
                        'organization_id' => $menuItem->organization_id,
                        'branch_id' => $branchId,
                        'inventory_item_id' => $recipe->ingredient_item_id,
                        'transaction_type' => 'reservation',
                        'quantity' => $reservationQuantity,
                        'cost_price' => $recipe->ingredient->buying_price ?? 0,
                        'unit_price' => 0,
                        'total_value' => 0,
                        'notes' => "Reserved for order - Reservation ID: {$reservationId}",
                        'reference_type' => 'OrderReservation',
                        'reference_id' => $reservationId,
                        'expires_at' => Carbon::now()->addMinutes(30), // 30-minute hold
                    ]);

                    $reservedItems[] = $transaction;
                }
            }

            if (empty($failedItems)) {
                DB::commit();
                return ['success' => true, 'reserved_items' => $reservedItems];
            } else {
                DB::rollback();
                return ['success' => false, 'errors' => $failedItems];
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reserve ingredients', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['System error during reservation']];
        }
    }

    /**
     * Convert reservations to actual consumption
     */
    public function confirmReservation(string $reservationId, int $orderId): bool
    {
        DB::beginTransaction();

        try {
            $reservationTransactions = ItemTransaction::where('reference_type', 'OrderReservation')
                ->where('reference_id', $reservationId)
                ->where('transaction_type', 'reservation')
                ->get();

            foreach ($reservationTransactions as $transaction) {
                // Convert reservation to consumption
                $transaction->update([
                    'transaction_type' => 'order_consumption',
                    'reference_type' => 'Order',
                    'reference_id' => $orderId,
                    'notes' => "Consumed for order #{$orderId}",
                    'expires_at' => null,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to confirm reservation', [
                'reservation_id' => $reservationId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Release expired or cancelled reservations
     */
    public function releaseReservation(string $reservationId): bool
    {
        DB::beginTransaction();

        try {
            $reservationTransactions = ItemTransaction::where('reference_type', 'OrderReservation')
                ->where('reference_id', $reservationId)
                ->where('transaction_type', 'reservation')
                ->get();

            foreach ($reservationTransactions as $transaction) {
                // Reverse the reservation by creating opposite transaction
                ItemTransaction::create([
                    'organization_id' => $transaction->organization_id,
                    'branch_id' => $transaction->branch_id,
                    'inventory_item_id' => $transaction->inventory_item_id,
                    'transaction_type' => 'reservation_release',
                    'quantity' => -$transaction->quantity, // Opposite quantity
                    'cost_price' => $transaction->cost_price,
                    'unit_price' => 0,
                    'total_value' => 0,
                    'notes' => "Released reservation for cancelled/expired order - Reservation ID: {$reservationId}",
                    'reference_type' => 'ReservationRelease',
                    'reference_id' => $reservationId,
                ]);

                // Mark original reservation as released
                $transaction->update([
                    'notes' => $transaction->notes . ' [RELEASED]'
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to release reservation', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clean up expired reservations
     */
    public function cleanupExpiredReservations(): int
    {
        $expiredReservations = ItemTransaction::where('transaction_type', 'reservation')
            ->where('expires_at', '<', Carbon::now())
            ->whereNotIn('notes', ['%[RELEASED]%'])
            ->get()
            ->groupBy('reference_id');

        $cleanedCount = 0;

        foreach ($expiredReservations as $reservationId => $transactions) {
            if ($this->releaseReservation($reservationId)) {
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }

    /**
     * Get real-time menu availability for display
     */
    public function getMenuAvailability(int $branchId, array $menuItemIds = null): array
    {
        $query = MenuItem::with(['recipes.ingredient', 'category'])
            ->where('branch_id', $branchId)
            ->where('is_active', true);

        if ($menuItemIds) {
            $query->whereIn('id', $menuItemIds);
        }

        $menuItems = $query->get();
        $availability = [];

        foreach ($menuItems as $item) {
            $itemAvailability = $item->checkAvailability($branchId);
            
            $availability[] = [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->name ?? 'Uncategorized',
                'price' => $item->price,
                'available' => $itemAvailability['available'],
                'max_quantity' => $itemAvailability['max_quantity'],
                'status' => $item->availability_status,
                'stock_percentage' => $item->stock_percentage,
                'limiting_factor' => $itemAvailability['limiting_factor'],
                'missing_ingredients' => $itemAvailability['missing_ingredients'],
            ];
        }

        return $availability;
    }

    /**
     * Get alternative menu items when something is out of stock
     */
    public function getAlternativeMenuItems(int $menuItemId, int $branchId): array
    {
        $originalItem = MenuItem::with('category')->find($menuItemId);
        if (!$originalItem) return [];

        $alternatives = MenuItem::where('menu_category_id', $originalItem->menu_category_id)
            ->where('id', '!=', $menuItemId)
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();

        $availableAlternatives = [];

        foreach ($alternatives as $alternative) {
            $availability = $alternative->checkAvailability($branchId);
            if ($availability['available']) {
                $availableAlternatives[] = [
                    'menu_item_id' => $alternative->id,
                    'name' => $alternative->name,
                    'price' => $alternative->price,
                    'max_quantity' => $availability['max_quantity'],
                ];
            }
        }

        return $availableAlternatives;
    }

    /**
     * Generate stock alerts for branch managers
     */
    public function generateStockAlerts(int $branchId): array
    {
        $alerts = [];

        // Low stock alerts
        $lowStockItems = $this->getLowStockItems($branchId);
        foreach ($lowStockItems as $item) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'title' => "Low Stock Alert",
                'message' => "'{$item['item']->name}' is running low. Current: {$item['current_stock']}, Reorder at: {$item['reorder_level']}",
                'item_id' => $item['item']->id,
                'affected_menu_items' => $item['affected_menu_items'],
            ];
        }

        // Out of stock alerts
        $outOfStockItems = $this->getOutOfStockItems($branchId);
        foreach ($outOfStockItems as $item) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'severity' => 'error',
                'title' => "Out of Stock",
                'message' => "'{$item['item']->name}' is completely out of stock",
                'item_id' => $item['item']->id,
                'affected_menu_items' => $item['affected_menu_items'],
            ];
        }

        return $alerts;
    }

    /**
     * Get out of stock items
     */
    private function getOutOfStockItems(int $branchId): array
    {
        $items = ItemMaster::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        $outOfStockItems = [];
        
        foreach ($items as $item) {
            $currentStock = $this->getCurrentStock($item->id, $branchId);
            if ($currentStock <= 0) {
                $outOfStockItems[] = [
                    'item' => $item,
                    'affected_menu_items' => $this->getAffectedMenuItems($item->id)
                ];
            }
        }

        return $outOfStockItems;
    }
}
