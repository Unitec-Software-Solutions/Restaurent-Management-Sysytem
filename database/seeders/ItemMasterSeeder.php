<?php

namespace Database\Seeders;

use App\Models\ItemMaster;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get valid IDs from the database
        $organization = Organization::first();
        if (!$organization) {
            $this->command->error('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        $branches = Branch::where('organization_id', $organization->id)->get();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $categories = ItemCategory::all();
        if ($categories->isEmpty()) {
            $this->command->error('No item categories found. Please run ItemCategorySeeder first.');
            return;
        }

        // Use the first branch for all items (can be easily modified)
        $primaryBranch = $branches->first();
        
        // Get category IDs
        $mainCourseCategory = $categories->where('name', 'Main Course')->first();
        $beveragesCategory = $categories->where('name', 'Beverages')->first();
        $dessertsCategory = $categories->where('name', 'Desserts')->first();
        $ingredientsCategory = $categories->where('name', 'Ingredients')->first();
        
        // Fallback to first categories if named ones don't exist
        $mainCourseId = $mainCourseCategory ? $mainCourseCategory->id : $categories->first()->id;
        $beveragesId = $beveragesCategory ? $beveragesCategory->id : $categories->first()->id;
        $dessertsId = $dessertsCategory ? $dessertsCategory->id : $categories->first()->id;
        $ingredientsId = $ingredientsCategory ? $ingredientsCategory->id : $categories->first()->id;

        $items = [
            // Food Items
            [
                'name' => 'Margherita Pizza',
                'unicode_name' => 'Margherita Pizza',
                'item_category_id' => $mainCourseId,
                'item_code' => 'FD-001',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 10,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 150.00,
                'selling_price' => 350.00,
                'is_menu_item' => true,
                'additional_notes' => 'Classic pizza with tomato and mozzarella',
                'description' => 'Traditional Italian pizza',
                'attributes' => [
                    'ingredients' => 'Dough, Tomato Sauce, Mozzarella Cheese, Basil',
                    'img' => 'margherita.jpg',
                    'prep_time' => 15,
                    'portion_size' => 'regular',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Pepperoni Pizza',
                'unicode_name' => 'Pepperoni Pizza',
                'item_category_id' => $mainCourseId,
                'item_code' => 'FD-002',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 10,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 180.00,
                'selling_price' => 400.00,
                'is_menu_item' => true,
                'additional_notes' => 'Classic pizza with pepperoni',
                'description' => 'Traditional Italian pizza with pepperoni',
                'attributes' => [
                    'ingredients' => 'Dough, Tomato Sauce, Mozzarella Cheese, Pepperoni',
                    'img' => 'pepperoni.jpg',
                    'prep_time' => 15,
                    'portion_size' => 'regular',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => true,
                    'discounts' => 10.00
                ],
            ],
            [
                'name' => 'Hawaiian Pizza',
                'unicode_name' => 'Hawaiian Pizza',
                'item_category_id' => $mainCourseId,
                'item_code' => 'FD-003',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 8,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 170.00,
                'selling_price' => 380.00,
                'is_menu_item' => true,
                'additional_notes' => 'Pizza with ham and pineapple',
                'description' => 'Controversial but delicious pineapple pizza',
                'attributes' => [
                    'ingredients' => 'Dough, Tomato Sauce, Mozzarella Cheese, Ham, Pineapple',
                    'img' => 'hawaiian.jpg',
                    'prep_time' => 15,
                    'portion_size' => 'regular',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Caesar Salad',
                'unicode_name' => 'Caesar Salad',
                'item_category_id' => $mainCourseId,
                'item_code' => 'FD-004',
                'unit_of_measurement' => 'bowl',
                'reorder_level' => 12,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 80.00,
                'selling_price' => 250.00,
                'is_menu_item' => true,
                'additional_notes' => 'Fresh Caesar salad with croutons',
                'description' => 'Classic Caesar salad with romaine lettuce',
                'attributes' => [
                    'ingredients' => 'Romaine Lettuce, Caesar Dressing, Parmesan Cheese, Croutons',
                    'img' => 'caesar_salad.jpg',
                    'prep_time' => 8,
                    'portion_size' => 'regular',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Chicken Wings',
                'unicode_name' => 'Chicken Wings',
                'item_category_id' => $mainCourseId,
                'item_code' => 'FD-005',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 25,
                'is_perishable' => true,
                'shelf_life_in_days' => 2,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 45.00,
                'selling_price' => 120.00,
                'is_menu_item' => true,
                'additional_notes' => 'Spicy buffalo chicken wings',
                'description' => 'Crispy chicken wings with buffalo sauce',
                'attributes' => [
                    'ingredients' => 'Chicken Wings, Buffalo Sauce, Celery, Blue Cheese',
                    'img' => 'chicken_wings.jpg',
                    'prep_time' => 25,
                    'portion_size' => '6 pieces',
                    'available_from' => '15:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => true,
                    'discounts' => 15.00
                ],
            ],

            // Beverages
            [
                'name' => 'Coca Cola',
                'unicode_name' => 'Coca Cola',
                'item_category_id' => $beveragesId,
                'item_code' => 'BV-001',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 50,
                'is_perishable' => false,
                'shelf_life_in_days' => 365,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 25.00,
                'selling_price' => 60.00,
                'is_menu_item' => true,
                'additional_notes' => 'Classic cola drink',
                'description' => 'Refreshing carbonated cola beverage',
                'attributes' => [
                    'volume' => '330ml',
                    'img' => 'coca_cola.jpg',
                    'temperature' => 'cold',
                    'available_from' => '10:00:00',
                    'available_to' => '24:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Orange Juice',
                'unicode_name' => 'Orange Juice',
                'item_category_id' => $beveragesId,
                'item_code' => 'BV-002',
                'unit_of_measurement' => 'glass',
                'reorder_level' => 30,
                'is_perishable' => true,
                'shelf_life_in_days' => 3,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 30.00,
                'selling_price' => 80.00,
                'is_menu_item' => true,
                'additional_notes' => 'Fresh squeezed orange juice',
                'description' => '100% pure orange juice',
                'attributes' => [
                    'volume' => '250ml',
                    'img' => 'orange_juice.jpg',
                    'temperature' => 'cold',
                    'available_from' => '08:00:00',
                    'available_to' => '22:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Coffee Espresso',
                'unicode_name' => 'Coffee Espresso',
                'item_category_id' => $beveragesId,
                'item_code' => 'BV-003',
                'unit_of_measurement' => 'cup',
                'reorder_level' => 40,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 15.00,
                'selling_price' => 50.00,
                'is_menu_item' => true,
                'additional_notes' => 'Strong espresso coffee',
                'description' => 'Premium Italian espresso',
                'attributes' => [
                    'volume' => '30ml',
                    'img' => 'espresso.jpg',
                    'temperature' => 'hot',
                    'available_from' => '06:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],

            // Desserts
            [
                'name' => 'Chocolate Cake',
                'unicode_name' => 'Chocolate Cake',
                'item_category_id' => $dessertsId,
                'item_code' => 'DS-001',
                'unit_of_measurement' => 'slice',
                'reorder_level' => 5,
                'is_perishable' => true,
                'shelf_life_in_days' => 3,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 80.00,
                'selling_price' => 180.00,
                'is_menu_item' => true,
                'additional_notes' => 'Rich chocolate cake',
                'description' => 'Decadent chocolate layer cake',
                'attributes' => [
                    'ingredients' => 'Chocolate, Flour, Sugar, Eggs, Butter',
                    'img' => 'chocolate_cake.jpg',
                    'prep_time' => 5,
                    'portion_size' => 'slice',
                    'available_from' => '12:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Tiramisu',
                'unicode_name' => 'Tiramisu',
                'item_category_id' => $dessertsId,
                'item_code' => 'DS-002',
                'unit_of_measurement' => 'slice',
                'reorder_level' => 4,
                'is_perishable' => true,
                'shelf_life_in_days' => 2,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 90.00,
                'selling_price' => 220.00,
                'is_menu_item' => true,
                'additional_notes' => 'Italian coffee-flavored dessert',
                'description' => 'Traditional Italian dessert with mascarpone',
                'attributes' => [
                    'ingredients' => 'Mascarpone, Coffee, Ladyfingers, Cocoa',
                    'img' => 'tiramisu.jpg',
                    'prep_time' => 8,
                    'portion_size' => 'slice',
                    'available_from' => '12:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],
            [
                'name' => 'Ice Cream Vanilla',
                'unicode_name' => 'Ice Cream Vanilla',
                'item_category_id' => $dessertsId,
                'item_code' => 'DS-003',
                'unit_of_measurement' => 'scoop',
                'reorder_level' => 20,
                'is_perishable' => true,
                'shelf_life_in_days' => 30,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 15.00,
                'selling_price' => 45.00,
                'is_menu_item' => true,
                'additional_notes' => 'Premium vanilla ice cream',
                'description' => 'Creamy vanilla ice cream',
                'attributes' => [
                    'ingredients' => 'Milk, Cream, Sugar, Vanilla Extract',
                    'img' => 'vanilla_ice_cream.jpg',
                    'serving_temp' => 'frozen',
                    'portion_size' => '100g',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'discounts' => 0.00
                ],
            ],

            // Ingredients
            [
                'name' => 'Tomato Sauce',
                'unicode_name' => 'Tomato Sauce',
                'item_category_id' => $ingredientsId,
                'item_code' => 'ING-001',
                'unit_of_measurement' => 'liter',
                'reorder_level' => 20,
                'is_perishable' => true,
                'shelf_life_in_days' => 7,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 50.00,
                'selling_price' => 0.00, // Not directly sold
                'is_menu_item' => false,
                'additional_notes' => 'Base sauce for pizzas',
                'description' => 'Fresh tomato sauce for cooking',
                'attributes' => [
                    'type' => 'ingredient',
                    'storage' => 'refrigerated',
                    'supplier' => 'Local Farm',
                    'allergens' => 'none'
                ],
            ],
            [
                'name' => 'Mozzarella Cheese',
                'unicode_name' => 'Mozzarella Cheese',
                'item_category_id' => $ingredientsId,
                'item_code' => 'ING-002',
                'unit_of_measurement' => 'kg',
                'reorder_level' => 15,
                'is_perishable' => true,
                'shelf_life_in_days' => 14,
                'branch_id' => $primaryBranch->id,
                'organization_id' => $organization->id,
                'buying_price' => 120.00,
                'selling_price' => 0.00, // Not directly sold
                'is_menu_item' => false,
                'additional_notes' => 'Premium mozzarella for pizzas',
                'description' => 'Fresh mozzarella cheese',
                'attributes' => [
                    'type' => 'ingredient',
                    'storage' => 'refrigerated',
                    'supplier' => 'Dairy Co',
                    'allergens' => 'dairy'
                ],
            ],
        ];

        // Create items with validation
        foreach ($items as $itemData) {
            try {
                // Check if item already exists by code
                $existingItem = ItemMaster::where('item_code', $itemData['item_code'])->first();
                if ($existingItem) {
                    $this->command->info("Item {$itemData['item_code']} already exists, skipping.");
                    continue;
                }

                // Create the item
                ItemMaster::create($itemData);
                $this->command->info("Created item: {$itemData['name']} ({$itemData['item_code']})");
                
            } catch (\Exception $e) {
                $this->command->error("Failed to create item {$itemData['item_code']}: " . $e->getMessage());
            }
        }

        $this->command->info('ItemMasterSeeder completed successfully.');
    }
}
