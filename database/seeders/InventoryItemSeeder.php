<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use Carbon\Carbon;


class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = InventoryCategory::all()->keyBy('name');
        
        $inventoryItems = [
            // Ingredients Category
            [
                'name' => 'Chicken Breast',
                'category' => 'Ingredients',
                'sku' => 'ING-CB-001',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 10.00,
                'is_perishable' => true,
                'shelf_life_days' => 5,
            ],
            [
                'name' => 'Ground Beef',
                'category' => 'Ingredients',
                'sku' => 'ING-GB-002',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 8.00,
                'is_perishable' => true,
                'shelf_life_days' => 3,
            ],
            [
                'name' => 'Salmon Fillet',
                'category' => 'Ingredients',
                'sku' => 'ING-SF-003',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 5.00,
                'is_perishable' => true,
                'shelf_life_days' => 2,
            ],
            [
                'name' => 'Tomatoes',
                'category' => 'Ingredients',
                'sku' => 'ING-TM-004',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 5.00,
                'is_perishable' => true,
                'shelf_life_days' => 7,
            ],
            [
                'name' => 'Lettuce',
                'category' => 'Ingredients',
                'sku' => 'ING-LT-005',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 3.00,
                'is_perishable' => true,
                'shelf_life_days' => 5,
            ],
            
            // Beverages Category
            [
                'name' => 'Coca Cola',
                'category' => 'Beverages',
                'sku' => 'BEV-CC-001',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 24.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Sprite',
                'category' => 'Beverages',
                'sku' => 'BEV-SP-002',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 24.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Orange Juice',
                'category' => 'Beverages',
                'sku' => 'BEV-OJ-003',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 10.00,
                'is_perishable' => true,
                'shelf_life_days' => 7,
            ],
            [
                'name' => 'Bottled Water',
                'category' => 'Beverages',
                'sku' => 'BEV-BW-004',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 48.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'House Wine Red',
                'category' => 'Beverages',
                'sku' => 'BEV-WR-005',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 12.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            
            // Packaging Category
            [
                'name' => 'Takeaway Containers Small',
                'category' => 'Packaging',
                'sku' => 'PKG-TC-001',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 100.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Takeaway Containers Large',
                'category' => 'Packaging',
                'sku' => 'PKG-TC-002',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 100.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Paper Bags',
                'category' => 'Packaging',
                'sku' => 'PKG-PB-003',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 200.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Plastic Cups',
                'category' => 'Packaging',
                'sku' => 'PKG-PC-004',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 200.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Disposable Cutlery Sets',
                'category' => 'Packaging',
                'sku' => 'PKG-DC-005',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 100.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            
            // Cleaning Supplies Category
            [
                'name' => 'All-Purpose Cleaner',
                'category' => 'Cleaning Supplies',
                'sku' => 'CLN-AP-001',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 5.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Dish Soap',
                'category' => 'Cleaning Supplies',
                'sku' => 'CLN-DS-002',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 3.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Floor Cleaner',
                'category' => 'Cleaning Supplies',
                'sku' => 'CLN-FC-003',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 5.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Sanitizing Wipes',
                'category' => 'Cleaning Supplies',
                'sku' => 'CLN-SW-004',
                'unit_of_measurement' => 'pcs',
                'reorder_level' => 10.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            [
                'name' => 'Hand Sanitizer',
                'category' => 'Cleaning Supplies',
                'sku' => 'CLN-HS-005',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 2.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
            ],
            
            // Dry Goods Category
            [
                'name' => 'Rice',
                'category' => 'Dry Goods',
                'sku' => 'DRY-RC-001',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 25.00,
                'is_perishable' => false,
                'shelf_life_days' => 365,
            ],
            [
                'name' => 'Pasta',
                'category' => 'Dry Goods',
                'sku' => 'DRY-PT-002',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 15.00,
                'is_perishable' => false,
                'shelf_life_days' => 365,
            ],
            [
                'name' => 'Flour',
                'category' => 'Dry Goods',
                'sku' => 'DRY-FL-003',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 10.00,
                'is_perishable' => false,
                'shelf_life_days' => 180,
            ],
            [
                'name' => 'Sugar',
                'category' => 'Dry Goods',
                'sku' => 'DRY-SG-004',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 10.00,
                'is_perishable' => false,
                'shelf_life_days' => 365,
            ],
            [
                'name' => 'Salt',
                'category' => 'Dry Goods',
                'sku' => 'DRY-ST-005',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 5.00,
                'is_perishable' => false,
                'shelf_life_days' => 730,
            ],
            
            // Dairy Products Category
            [
                'name' => 'Milk',
                'category' => 'Dairy Products',
                'sku' => 'DRY-MK-001',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 10.00,
                'is_perishable' => true,
                'shelf_life_days' => 7,
            ],
            [
                'name' => 'Butter',
                'category' => 'Dairy Products',
                'sku' => 'DRY-BT-002',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 5.00,
                'is_perishable' => true,
                'shelf_life_days' => 30,
            ],
            [
                'name' => 'Cheese',
                'category' => 'Dairy Products',
                'sku' => 'DRY-CH-003',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 3.00,
                'is_perishable' => true,
                'shelf_life_days' => 21,
            ],
            [
                'name' => 'Cream',
                'category' => 'Dairy Products',
                'sku' => 'DRY-CR-004',
                'unit_of_measurement' => 'ltr',
                'reorder_level' => 2.00,
                'is_perishable' => true,
                'shelf_life_days' => 10,
            ],
            [
                'name' => 'Yogurt',
                'category' => 'Dairy Products',
                'sku' => 'DRY-YG-005',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 3.00,
                'is_perishable' => true,
                'shelf_life_days' => 14,
            ],
        ];

        foreach ($inventoryItems as $item) {

            // Calculate expiry date for perishable items
            $expiryDate = null;
            if ($item['is_perishable'] && $item['shelf_life_days']) {
                // Set expiry date based on shelf life
                $expiryDate = Carbon::now()->addDays($item['shelf_life_days']);
            }

            InventoryItem::create([
                'inventory_category_id' => $categories[$item['category']]->id,
                'name' => $item['name'],
                'sku' => $item['sku'],
                'unit_of_measurement' => $item['unit_of_measurement'],
                'reorder_level' => $item['reorder_level'],
                'is_perishable' => $item['is_perishable'],
                'shelf_life_days' => $item['shelf_life_days'],
                'expiry_date' => $expiryDate,
                'is_inactive' => false,
                'is_active' => true,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $this->command->info('Inventory items seeded successfully!');
    }
} 