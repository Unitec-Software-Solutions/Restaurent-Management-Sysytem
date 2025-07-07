<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemTypeService
{
    /**
     * Determine if an item should be buy/sell or KOT based on strict criteria
     */
    public function determineItemType(ItemMaster $item): string
    {
        // Clear rule: If requires_production is true, it's KOT
        if ($item->requires_production) {
            return 'kot_production';
        }
        
        // If it has current stock and is inventory tracked, it's buy/sell
        if ($item->is_inventory_item && $item->current_stock !== null) {
            return 'buy_sell';
        }
        
        // Default to buy_sell for sellable items
        return 'buy_sell';
    }
    
    /**
     * Validate if an item can be created as KOT
     */
    public function canCreateAsKOT(ItemMaster $item): bool
    {
        if (!$item->requires_production) {
            Log::warning("Item {$item->id} cannot be KOT - requires_production is false");
            return false;
        }
        
        if (!$item->is_active) {
            Log::warning("Item {$item->id} cannot be KOT - item is inactive");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate if an item can be buy/sell
     */
    public function canCreateAsBuySell(ItemMaster $item): bool
    {
        if ($item->requires_production) {
            Log::warning("Item {$item->id} cannot be buy/sell - requires production");
            return false;
        }
        
        if (!$item->selling_price || $item->selling_price <= 0) {
            Log::warning("Item {$item->id} cannot be buy/sell - invalid selling price");
            return false;
        }
        
        return true;
    }
    
    /**
     * Auto-sync menu items from item master changes
     */
    public function syncMenuItemsFromMaster(int $organizationId): array
    {
        $results = [
            'added' => 0,
            'updated' => 0,
            'removed' => 0,
            'errors' => []
        ];
        
        DB::beginTransaction();
        
        try {
            // Get all menu-eligible items from item master
            $menuEligibleItems = ItemMaster::where('organization_id', $organizationId)
                ->where('is_menu_item', true)
                ->where('is_active', true)
                ->get();
            
            foreach ($menuEligibleItems as $itemMaster) {
                $this->syncSingleMenuItem($itemMaster, $results);
            }
            
            // Remove menu items for inactive/deleted item master entries
            $this->removeInactiveMenuItems($organizationId, $results);
            
            DB::commit();
            
            Log::info('Menu sync completed', $results);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Menu sync failed', ['error' => $e->getMessage()]);
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Sync a single menu item from item master
     */
    private function syncSingleMenuItem(ItemMaster $itemMaster, array &$results): void
    {
        $existingMenuItem = MenuItem::where('item_master_id', $itemMaster->id)->first();
        
        $itemType = $this->determineItemType($itemMaster);
        $menuItemType = $itemType === 'kot_production' ? MenuItem::TYPE_KOT : MenuItem::TYPE_BUY_SELL;
        
        if ($existingMenuItem) {
            // Update existing menu item
            $existingMenuItem->update([
                'name' => $itemMaster->name,
                'description' => $itemMaster->description,
                'price' => $itemMaster->selling_price,
                'cost_price' => $itemMaster->buying_price,
                'type' => $menuItemType,
                'requires_preparation' => $itemMaster->requires_production,
                'is_active' => $itemMaster->is_active,
            ]);
            
            $results['updated']++;
        } else {
            // Create new menu item
            MenuItem::create([
                'organization_id' => $itemMaster->organization_id,
                'branch_id' => $itemMaster->branch_id,
                'item_master_id' => $itemMaster->id,
                'name' => $itemMaster->name,
                'unicode_name' => $itemMaster->unicode_name,
                'description' => $itemMaster->description,
                'item_code' => $itemMaster->item_code,
                'price' => $itemMaster->selling_price,
                'cost_price' => $itemMaster->buying_price,
                'type' => $menuItemType,
                'requires_preparation' => $itemMaster->requires_production,
                'is_active' => true,
                'is_available' => true,
                'created_by' => auth('admin')->id(),
            ]);
            
            $results['added']++;
        }
    }
    
    /**
     * Remove menu items for inactive item master entries
     */
    private function removeInactiveMenuItems(int $organizationId, array &$results): void
    {
        $inactiveMenuItems = MenuItem::whereHas('itemMaster', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
                  ->where(function($q) {
                      $q->where('is_active', false)
                        ->orWhere('is_menu_item', false);
                  });
        })->get();
        
        foreach ($inactiveMenuItems as $menuItem) {
            $menuItem->update(['is_active' => false, 'is_available' => false]);
            $results['removed']++;
        }
    }
    
    /**
     * Create KOT item with validation
     */
    public function createKOTItem(ItemMaster $itemMaster, array $options = []): MenuItem
    {
        if (!$this->canCreateAsKOT($itemMaster)) {
            throw new \InvalidArgumentException("Item {$itemMaster->id} cannot be created as KOT item");
        }
        
        // Check if KOT item already exists
        $existingKOT = MenuItem::where('item_master_id', $itemMaster->id)
                              ->where('type', MenuItem::TYPE_KOT)
                              ->first();
        
        if ($existingKOT) {
            throw new \InvalidArgumentException("KOT item already exists for item master {$itemMaster->id}");
        }
        
        return MenuItem::create([
            'organization_id' => $itemMaster->organization_id,
            'branch_id' => $itemMaster->branch_id,
            'menu_category_id' => $options['menu_category_id'] ?? null,
            'item_master_id' => $itemMaster->id,
            'name' => $itemMaster->name,
            'unicode_name' => $itemMaster->unicode_name,
            'description' => $itemMaster->description,
            'item_code' => $itemMaster->item_code,
            'price' => $itemMaster->selling_price,
            'cost_price' => $itemMaster->buying_price,
            'type' => MenuItem::TYPE_KOT,
            'requires_preparation' => true,
            'preparation_time' => $options['preparation_time'] ?? 15,
            'is_active' => true,
            'is_available' => true,
            'created_by' => auth('admin')->id(),
        ]);
    }
    
    /**
     * Sync menu categories with item categories
     */
    public function syncMenuCategories(int $organizationId): array
    {
        $results = ['synced' => 0, 'errors' => []];
        
        try {
            $itemCategories = \App\Models\ItemCategory::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->get();
            
            foreach ($itemCategories as $itemCategory) {
                MenuCategory::updateOrCreate(
                    [
                        'organization_id' => $organizationId,
                        'name' => $itemCategory->name,
                    ],
                    [
                        'description' => $itemCategory->description,
                        'is_active' => $itemCategory->is_active,
                        'item_category_id' => $itemCategory->id,
                    ]
                );
                
                $results['synced']++;
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Menu category sync failed', ['error' => $e->getMessage()]);
        }
        
        return $results;
    }
}
