<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuItemIngredient;
use App\Models\InventoryItem;

class MenuRecipeSeeder extends Seeder
{
    public function run()
    {
        // Get inventory items by SKU for easy reference
        $items = InventoryItem::all()->keyBy('sku');

        // Create some example menu items with their recipes
        $menuItems = [
            [
                'name' => 'Classic Burger',
                'description' => 'Juicy beef patty with fresh vegetables',
                'price' => 12.99,
                'requires_preparation' => true,
                'preparation_time' => 15,
                'station' => 'kitchen',
                'ingredients' => [
                    ['sku' => 'ING-GB-002', 'quantity' => 0.200, 'unit' => 'kg'], // Ground Beef
                    ['sku' => 'ING-TM-004', 'quantity' => 0.050, 'unit' => 'kg'], // Tomatoes
                    ['sku' => 'ING-LT-005', 'quantity' => 0.030, 'unit' => 'kg'], // Lettuce
                ]
            ],
            [
                'name' => 'Fresh Fruit Juice',
                'description' => 'Blend of seasonal fruits',
                'price' => 5.99,
                'requires_preparation' => true,
                'preparation_time' => 5,
                'station' => 'bar',
                'ingredients' => [
                    ['sku' => 'BEV-OJ-003', 'quantity' => 0.250, 'unit' => 'ltr'], // Orange Juice
                ]
            ],
            [
                'name' => 'Grilled Salmon',
                'description' => 'Fresh salmon with herbs',
                'price' => 24.99,
                'requires_preparation' => true,
                'preparation_time' => 20,
                'station' => 'kitchen',
                'ingredients' => [
                    ['sku' => 'ING-SF-003', 'quantity' => 0.200, 'unit' => 'kg'], // Salmon Fillet
                ]
            ]
        ];

        foreach ($menuItems as $item) {
            $ingredients = $item['ingredients'];
            unset($item['ingredients']);

            // Create menu item
            $menuItem = MenuItem::create([
                'menu_category_id' => 1, // Assuming category 1 exists
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'requires_preparation' => $item['requires_preparation'],
                'preparation_time' => $item['preparation_time'],
                'station' => $item['station'],
                'is_available' => true,
                'is_active' => true
            ]);

            // Add ingredients
            foreach ($ingredients as $ingredient) {
                if (isset($items[$ingredient['sku']])) {
                    MenuItemIngredient::create([
                        'menu_item_id' => $menuItem->id,
                        'inventory_item_id' => $items[$ingredient['sku']]->id,
                        'quantity' => $ingredient['quantity'],
                        'unit' => $ingredient['unit'],
                        'is_active' => true
                    ]);
                }
            }
        }

        $this->command->info('Menu items and recipes seeded successfully!');
    }
}