<?php


namespace Database\Seeders;

use App\Models\ItemMaster;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Food Items
            [
                'name' => 'Margherita Pizza',
                'unicode_name' => 'Margherita Pizza',
                'item_category_id' => 1,
                'item_code' => 'FD-001',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 10,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => 1,
                'organization_id' => 1,
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
                'name' => 'Chicken Burger',
                'unicode_name' => 'Chicken Burger',
                'item_category_id' => 1,
                'item_code' => 'FD-002',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 15,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => 1,
                'organization_id' => 1,
                'buying_price' => 120.00,
                'selling_price' => 250.00,
                'is_menu_item' => true,
                'additional_notes' => 'Spicy chicken burger with mayo',
                'description' => 'Crispy chicken burger',
                'attributes' => [
                    'ingredients' => 'Chicken Patty, Burger Bun, Lettuce, Tomato, Mayo',
                    'img' => 'chicken-burger.jpg',
                    'prep_time' => 10,
                    'portion_size' => 'regular',
                    'available_from' => '11:00:00',
                    'available_to' => '23:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => true,
                    'discounts' => 10.00
                ],
            ],

            // Beverage Items
            [
                'name' => 'Iced Coffee',
                'unicode_name' => 'Iced Coffee',
                'item_category_id' => 2,
                'item_code' => 'BV-001',
                'unit_of_measurement' => 'glass',
                'reorder_level' => 20,
                'is_perishable' => true,
                'shelf_life_in_days' => 1,
                'branch_id' => 1,
                'organization_id' => 1,
                'buying_price' => 30.00,
                'selling_price' => 120.00,
                'is_menu_item' => true,
                'additional_notes' => 'Cold brewed coffee',
                'description' => 'Refreshing iced coffee',
                'attributes' => [
                    'ingredients' => 'Coffee, Milk, Ice, Sugar',
                    'img' => 'iced-coffee.jpg',
                    'prep_time' => 5,
                    'portion_size' => 'regular',
                    'available_from' => '08:00:00',
                    'available_to' => '22:00:00',
                    'available_days' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                    'promotions' => false,
                    'variants' => ['Regular', 'Large']
                ],
            ],

            // Cleaning Supplies
            [
                'name' => 'Dishwashing Liquid',
                'unicode_name' => 'Dishwashing Liquid',
                'item_category_id' => 3,
                'item_code' => 'CS-001',
                'unit_of_measurement' => 'bottle',
                'reorder_level' => 5,
                'is_perishable' => false,
                'shelf_life_in_days' => 365,
                'branch_id' => 1,
                'organization_id' => 1,
                'buying_price' => 200.00,
                'selling_price' => 300.00,
                'is_menu_item' => false,
                'additional_notes' => '500ml bottle',
                'description' => 'Concentrated dishwashing liquid',
                'attributes' => [
                    'brand' => 'CleanPro',
                    'volume' => '500ml',
                    'fragrance' => 'Lemon',
                    'img' => 'dishwash-liquid.jpg'
                ],
            ],

            // Kitchen Equipment
            [
                'name' => 'Chef Knife',
                'unicode_name' => 'Chef Knife',
                'item_category_id' => 4,
                'item_code' => 'KE-001',
                'unit_of_measurement' => 'piece',
                'reorder_level' => 2,
                'is_perishable' => false,
                'shelf_life_in_days' => null,
                'branch_id' => 1,
                'organization_id' => 1,
                'buying_price' => 1500.00,
                'selling_price' => 2500.00,
                'is_menu_item' => false,
                'additional_notes' => '8-inch stainless steel',
                'description' => 'Professional chef knife',
                'attributes' => [
                    'brand' => 'KitchenPro',
                    'material' => 'Stainless Steel',
                    'length' => '8 inches',
                    'warranty' => '2 years',
                    'img' => 'chef-knife.jpg'
                ],
            ]
        ];

        foreach ($items as $item) {
            ItemMaster::create($item);
        }
    }
}
