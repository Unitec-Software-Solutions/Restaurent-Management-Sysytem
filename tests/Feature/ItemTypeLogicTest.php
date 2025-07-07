<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\Organization;
use App\Models\ItemCategory;
use App\Services\ItemTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTypeLogicTest extends TestCase
{
    use RefreshDatabase;

    private $itemTypeService;
    private $organization;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemTypeService = new ItemTypeService();
        
        $this->organization = Organization::factory()->create();
        $this->category = ItemCategory::factory()->create([
            'organization_id' => $this->organization->id
        ]);
    }

    /** @test */
    public function buy_sell_items_appear_in_menu_without_kot_creation()
    {
        // Create a buy/sell item
        $buySellItem = ItemMaster::factory()->create([
            'organization_id' => $this->organization->id,
            'item_category_id' => $this->category->id,
            'requires_production' => false,
            'item_type' => 'buy_sell',
            'is_menu_item' => true,
            'is_inventory_item' => true,
            'current_stock' => 100,
            'selling_price' => 25.00
        ]);

        // Sync menu items
        $results = $this->itemTypeService->syncMenuItemsFromMaster($this->organization->id);

        // Assert menu item was created
        $this->assertEquals(1, $results['added']);
        
        $menuItem = MenuItem::where('item_master_id', $buySellItem->id)->first();
        $this->assertNotNull($menuItem);
        $this->assertEquals(MenuItem::TYPE_BUY_SELL, $menuItem->type);
        $this->assertFalse($menuItem->requires_preparation);
    }

    /** @test */
    public function kot_items_trigger_production_when_ordered()
    {
        // Create a KOT production item
        $kotItem = ItemMaster::factory()->create([
            'organization_id' => $this->organization->id,
            'item_category_id' => $this->category->id,
            'requires_production' => true,
            'item_type' => 'kot_production',
            'is_menu_item' => true,
            'selling_price' => 45.00
        ]);

        // Create KOT menu item
        $menuItem = $this->itemTypeService->createKOTItem($kotItem);

        // Assert KOT properties
        $this->assertEquals(MenuItem::TYPE_KOT, $menuItem->type);
        $this->assertTrue($menuItem->requires_preparation);
        $this->assertEquals($kotItem->id, $menuItem->item_master_id);
    }

    /** @test */
    public function inactive_items_are_removed_from_menu()
    {
        // Create active item first
        $item = ItemMaster::factory()->create([
            'organization_id' => $this->organization->id,
            'item_category_id' => $this->category->id,
            'is_menu_item' => true,
            'is_active' => true,
            'selling_price' => 30.00
        ]);

        // Create menu item
        $this->itemTypeService->syncMenuItemsFromMaster($this->organization->id);
        
        $menuItem = MenuItem::where('item_master_id', $item->id)->first();
        $this->assertTrue($menuItem->is_active);

        // Deactivate item
        $item->update(['is_active' => false]);

        // Sync again
        $results = $this->itemTypeService->syncMenuItemsFromMaster($this->organization->id);

        // Assert menu item was deactivated
        $menuItem->refresh();
        $this->assertFalse($menuItem->is_active);
        $this->assertFalse($menuItem->is_available);
        $this->assertEquals(1, $results['removed']);
    }

    /** @test */
    public function buy_sell_items_cannot_be_kot_produced()
    {
        $buySellItem = ItemMaster::factory()->create([
            'requires_production' => false,
            'item_type' => 'buy_sell',
            'is_inventory_item' => true,
            'current_stock' => 50
        ]);

        $this->assertFalse($this->itemTypeService->canCreateAsKOT($buySellItem));
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be created as KOT item');
        
        $this->itemTypeService->createKOTItem($buySellItem);
    }

    /** @test */
    public function item_category_changes_sync_to_menu()
    {
        // Create item with category
        $item = ItemMaster::factory()->create([
            'organization_id' => $this->organization->id,
            'item_category_id' => $this->category->id,
            'is_menu_item' => true,
            'name' => 'Test Item'
        ]);

        // Create new category
        $newCategory = ItemCategory::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'New Category'
        ]);

        // Sync categories
        $results = $this->itemTypeService->syncMenuCategories($this->organization->id);
        
        $this->assertEquals(2, $results['synced']); // Original + new category
        
        // Verify menu categories were created
        $menuCategories = \App\Models\MenuCategory::where('organization_id', $this->organization->id)->count();
        $this->assertEquals(2, $menuCategories);
    }

    /** @test */
    public function duplicate_kot_items_are_prevented()
    {
        $kotItem = ItemMaster::factory()->create([
            'requires_production' => true,
            'item_type' => 'kot_production',
            'selling_price' => 35.00
        ]);

        // Create first KOT item
        $firstMenuItem = $this->itemTypeService->createKOTItem($kotItem);
        $this->assertInstanceOf(MenuItem::class, $firstMenuItem);

        // Try to create duplicate
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('KOT item already exists');
        
        $this->itemTypeService->createKOTItem($kotItem);
    }

    /** @test */
    public function item_master_without_valid_price_fails_validation()
    {
        $invalidItem = ItemMaster::factory()->create([
            'selling_price' => 0, // Invalid price
            'requires_production' => false
        ]);

        $this->assertFalse($this->itemTypeService->canCreateAsBuySell($invalidItem));
    }
}
