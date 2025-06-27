<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MenuSafetyService
{
    /**
     * Validate that menu items are available for ordering
     */
    public function validateMenuItemsAvailability(array $itemIds, int $menuId): array
    {
        $errors = [];
        $warnings = [];
        
        // Check if menu is active
        $menu = Menu::find($menuId);
        if (!$menu || !$menu->is_active) {
            $errors[] = 'Selected menu is not active and cannot be used for orders.';
            return ['errors' => $errors, 'warnings' => $warnings];
        }
        
        // Check menu validity dates
        $now = Carbon::now();
        if ($menu->valid_from && $now->lt($menu->valid_from)) {
            $errors[] = 'Selected menu is not yet valid. Valid from: ' . $menu->valid_from->format('Y-m-d H:i');
        }
        
        if ($menu->valid_until && $now->gt($menu->valid_until)) {
            $errors[] = 'Selected menu has expired. Valid until: ' . $menu->valid_until->format('Y-m-d H:i');
        }
        
        // Check if menu is valid for current day of week
        if ($menu->days_of_week && !$this->isValidForCurrentDay($menu->days_of_week)) {
            $errors[] = 'Selected menu is not available today (' . $now->format('l') . ').';
        }
        
        // Get menu items and check availability
        $menuItems = MenuItem::whereIn('id', $itemIds)
            ->where('menu_id', $menuId)
            ->get();
        
        $foundItemIds = $menuItems->pluck('id')->toArray();
        $missingItemIds = array_diff($itemIds, $foundItemIds);
        
        if (!empty($missingItemIds)) {
            $errors[] = 'Some items are not available in the selected menu: ' . implode(', ', $missingItemIds);
        }
        
        // Check individual item availability
        foreach ($menuItems as $item) {
            if (!$item->is_available) {
                $warnings[] = "Item '{$item->name}' is currently marked as unavailable.";
            }
            
            // Check stock levels
            if ($item->current_stock !== null && $item->current_stock <= 0) {
                $warnings[] = "Item '{$item->name}' is out of stock.";
            } elseif ($item->current_stock !== null && $item->current_stock < 5) {
                $warnings[] = "Item '{$item->name}' has low stock ({$item->current_stock} remaining).";
            }
        }
        
        return ['errors' => $errors, 'warnings' => $warnings];
    }
    
    /**
     * Prevent orders for inactive menu items
     */
    public function preventInactiveItemOrders(Order $order): array
    {
        $issues = [];
        
        if (!$order->menu_id) {
            $issues[] = 'Order is not associated with any menu.';
            return $issues;
        }
        
        $menu = $order->menu;
        if (!$menu || !$menu->is_active) {
            $issues[] = 'Order is associated with an inactive menu.';
        }
        
        foreach ($order->orderItems as $orderItem) {
            $menuItem = $orderItem->menuItem;
            if (!$menuItem) {
                $issues[] = "Order item ID {$orderItem->id} references a deleted menu item.";
                continue;
            }
            
            if (!$menuItem->is_available) {
                $issues[] = "Menu item '{$menuItem->name}' is no longer available.";
            }
            
            if ($menuItem->menu_id !== $order->menu_id) {
                $issues[] = "Menu item '{$menuItem->name}' does not belong to the order's menu.";
            }
        }
        
        return $issues;
    }
    
    /**
     * Archive old menus and their associated data
     */
    public function archiveOldMenus(int $daysOld = 30): array
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        $menusToArchive = Menu::where('is_active', false)
            ->where('updated_at', '<', $cutoffDate)
            ->whereNull('archived_at')
            ->get();
        
        $archivedCount = 0;
        $errors = [];
        
        foreach ($menusToArchive as $menu) {
            try {
                // Check if menu has recent orders
                $recentOrdersCount = Order::where('menu_id', $menu->id)
                    ->where('created_at', '>', $cutoffDate)
                    ->count();
                
                if ($recentOrdersCount > 0) {
                    continue; // Skip archiving if has recent orders
                }
                
                $menu->update([
                    'archived_at' => Carbon::now(),
                    'archive_reason' => 'Automatic archival - inactive for ' . $daysOld . ' days'
                ]);
                
                $archivedCount++;
                
                Log::info('Menu archived automatically', [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'reason' => 'Inactive for ' . $daysOld . ' days'
                ]);
                
            } catch (\Exception $e) {
                $errors[] = "Failed to archive menu {$menu->id}: " . $e->getMessage();
                Log::error('Menu archival failed', [
                    'menu_id' => $menu->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'archived_count' => $archivedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * Validate menu date overlaps
     */
    public function validateMenuDateOverlaps(Menu $menu, ?int $excludeMenuId = null): array
    {
        $errors = [];
        
        if (!$menu->valid_from || !$menu->valid_until) {
            return $errors; // No date restrictions, no overlaps possible
        }
        
        $query = Menu::where('branch_id', $menu->branch_id)
            ->where('type', $menu->type)
            ->where('is_active', true)
            ->whereNotNull('valid_from')
            ->whereNotNull('valid_until');
        
        if ($excludeMenuId) {
            $query->where('id', '!=', $excludeMenuId);
        }
        
        // Check for overlapping date ranges
        $overlappingMenus = $query->where(function ($q) use ($menu) {
            $q->whereBetween('valid_from', [$menu->valid_from, $menu->valid_until])
              ->orWhereBetween('valid_until', [$menu->valid_from, $menu->valid_until])
              ->orWhere(function ($sq) use ($menu) {
                  $sq->where('valid_from', '<=', $menu->valid_from)
                     ->where('valid_until', '>=', $menu->valid_until);
              });
        })->get();
        
        foreach ($overlappingMenus as $overlapping) {
            $errors[] = "Menu conflicts with '{$overlapping->name}' (ID: {$overlapping->id}) " .
                       "from {$overlapping->valid_from->format('Y-m-d')} to {$overlapping->valid_until->format('Y-m-d')}";
        }
        
        return $errors;
    }
    
    /**
     * Handle menu transitions safely
     */
    public function handleMenuTransition(Menu $fromMenu, Menu $toMenu): array
    {
        $issues = [];
        
        try {
            // Log the transition
            Log::info('Menu transition initiated', [
                'from_menu_id' => $fromMenu->id,
                'from_menu_name' => $fromMenu->name,
                'to_menu_id' => $toMenu->id,
                'to_menu_name' => $toMenu->name,
                'branch_id' => $fromMenu->branch_id
            ]);
            
            // Check if transition is valid
            if ($fromMenu->branch_id !== $toMenu->branch_id) {
                $issues[] = 'Cannot transition menus between different branches.';
                return $issues;
            }
            
            // Check for pending orders on the old menu
            $pendingOrders = Order::where('menu_id', $fromMenu->id)
                ->whereIn('status', ['active', 'submitted', 'preparing'])
                ->count();
            
            if ($pendingOrders > 0) {
                $issues[] = "Warning: {$pendingOrders} pending orders still exist for the previous menu.";
            }
            
            // Deactivate old menu
            $fromMenu->update([
                'is_active' => false,
                'deactivated_at' => Carbon::now(),
                'deactivation_reason' => 'Menu transition'
            ]);
            
            // Activate new menu
            $toMenu->update([
                'is_active' => true,
                'activated_at' => Carbon::now()
            ]);
            
            // Clear relevant caches
            Cache::forget("active_menu_branch_{$fromMenu->branch_id}");
            Cache::forget("menu_items_{$fromMenu->id}");
            Cache::forget("menu_items_{$toMenu->id}");
            
            Log::info('Menu transition completed successfully', [
                'from_menu_id' => $fromMenu->id,
                'to_menu_id' => $toMenu->id
            ]);
            
        } catch (\Exception $e) {
            $issues[] = 'Menu transition failed: ' . $e->getMessage();
            Log::error('Menu transition failed', [
                'from_menu_id' => $fromMenu->id,
                'to_menu_id' => $toMenu->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $issues;
    }
    
    /**
     * Check if menu is valid for current day of week
     */
    private function isValidForCurrentDay(?string $daysOfWeek): bool
    {
        if (!$daysOfWeek) {
            return true; // No day restrictions
        }
        
        $allowedDays = json_decode($daysOfWeek, true);
        if (!is_array($allowedDays)) {
            return true; // Invalid format, assume no restrictions
        }
        
        $currentDay = strtolower(Carbon::now()->format('l'));
        return in_array($currentDay, array_map('strtolower', $allowedDays));
    }
    
    /**
     * Get menu safety status for dashboard
     */
    public function getMenuSafetyStatus(int $branchId): array
    {
        $activeMenus = Menu::where('branch_id', $branchId)
            ->where('is_active', true)
            ->with(['menuItems'])
            ->get();
        
        $status = [
            'active_menus_count' => $activeMenus->count(),
            'total_items_count' => 0,
            'unavailable_items_count' => 0,
            'low_stock_items_count' => 0,
            'expired_menus_count' => 0,
            'conflicts' => []
        ];
        
        $now = Carbon::now();
        
        foreach ($activeMenus as $menu) {
            // Check expiry
            if ($menu->valid_until && $now->gt($menu->valid_until)) {
                $status['expired_menus_count']++;
                $status['conflicts'][] = "Menu '{$menu->name}' has expired";
            }
            
            // Check day validity
            if (!$this->isValidForCurrentDay($menu->days_of_week)) {
                $status['conflicts'][] = "Menu '{$menu->name}' is not valid for today";
            }
            
            // Count items
            foreach ($menu->menuItems as $item) {
                $status['total_items_count']++;
                
                if (!$item->is_available) {
                    $status['unavailable_items_count']++;
                }
                
                if ($item->current_stock !== null && $item->current_stock < 5) {
                    $status['low_stock_items_count']++;
                }
            }
        }
        
        return $status;
    }
}
