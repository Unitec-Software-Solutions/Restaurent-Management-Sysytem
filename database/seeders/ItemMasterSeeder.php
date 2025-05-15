<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemMasterSeeder extends Seeder
{
    public function run()
    {
        $items = [];

        // Food Items
        $foodCategories = [
            'Dairy' => ['Milk', 'Cheese', 'Yogurt', 'Butter', 'Cream'],
            'Produce' => ['Apples', 'Bananas', 'Lettuce', 'Tomatoes', 'Carrots'],
            'Meat' => ['Chicken Breast', 'Ground Beef', 'Pork Chops', 'Salmon Fillet', 'Turkey'],
            'Bakery' => ['Bread', 'Bagels', 'Muffins', 'Croissants', 'Donuts'],
            'Frozen' => ['Pizza', 'Ice Cream', 'Frozen Vegetables', 'Frozen Berries', 'Frozen Meals']
        ];

        foreach ($foodCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product,
                    'sku' => 'F-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'food',
                    'reorder_level' => rand(5, 20),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'shelf_life' => $this->getFoodShelfLife($category),
                        'supplier_id' => rand(1, 10),
                        'unit' => $this->getFoodUnit($product)
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Inventory Items
        $inventoryCategories = [
            'Cleaning' => ['Bleach', 'Disinfectant Wipes', 'Glass Cleaner', 'Trash Bags', 'Paper Towels'],
            'Office' => ['Pens', 'Notepads', 'Stapler', 'Printer Paper', 'Folders'],
            'Kitchen' => ['Knives', 'Cutting Boards', 'Mixing Bowls', 'Measuring Cups', 'Spatulas'],
            'Packaging' => ['Takeout Containers', 'Plastic Wrap', 'Aluminum Foil', 'Ziploc Bags', 'Food Storage Containers'],
            'Safety' => ['Gloves', 'Face Masks', 'First Aid Kit', 'Fire Extinguisher', 'Aprons']
        ];

        foreach ($inventoryCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product,
                    'sku' => 'INV-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'inventory',
                    'reorder_level' => rand(3, 15),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'supplier_id' => rand(1, 10),
                        'location' => $this->getInventoryLocation($category)
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Other Items
        $otherCategories = [
            'Miscellaneous' => ['Extension Cord', 'Light Bulbs', 'Batteries', 'Tool Kit', 'Step Ladder'],
            'Decor' => ['Picture Frames', 'Plants', 'Wall Art', 'Vases', 'Candles'],
            'Furniture' => ['Chair', 'Table', 'Shelf', 'Cabinet', 'Stool'],
            'Electronics' => ['Bluetooth Speaker', 'Digital Scale', 'Thermometer', 'Timer', 'Calculator'],
            'Maintenance' => ['Paint', 'Brush', 'Screwdriver Set', 'Hammer', 'Duct Tape']
        ];

        foreach ($otherCategories as $category => $products) {
            foreach ($products as $product) {
                $items[] = [
                    'name' => $product,
                    'sku' => 'OTH-' . strtoupper(substr($category, 0, 3)) . '-' . rand(1000, 9999),
                    'type' => 'other',
                    'reorder_level' => rand(1, 10),
                    'organization_id' => rand(1, 5),
                    'branch_id' => rand(1, 5),
                    'attributes' => json_encode([
                        'category' => $category,
                        'supplier_id' => rand(1, 10),
                        'condition' => ['New', 'Used', 'Refurbished'][rand(0, 2)]
                    ]),
                    'is_active' => rand(0, 1) == 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Insert all items in batches
        foreach (array_chunk($items, 50) as $chunk) {
            DB::table('item_master')->insert($chunk);
        }
    }

    private function getFoodShelfLife($category)
    {
        return match($category) {
            'Dairy' => '7 days',
            'Produce' => '5-14 days',
            'Meat' => '3-5 days',
            'Bakery' => '3-7 days',
            'Frozen' => '6-12 months',
            default => '7 days'
        };
    }

    private function getFoodUnit($product)
    {
        if (in_array($product, ['Milk', 'Cream', 'Yogurt'])) {
            return 'liter';
        }
        if (in_array($product, ['Cheese', 'Butter', 'Meat', 'Pizza'])) {
            return 'kg';
        }
        if (in_array($product, ['Apples', 'Bananas', 'Tomatoes', 'Carrots'])) {
            return 'dozen';
        }
        return 'unit';
    }

    private function getInventoryLocation($category)
    {
        return match($category) {
            'Cleaning' => 'Storage Room',
            'Office' => 'Front Desk',
            'Kitchen' => 'Kitchen Storage',
            'Packaging' => 'Packaging Area',
            'Safety' => 'First Aid Station',
            default => 'Main Storage'
        };
    }
}