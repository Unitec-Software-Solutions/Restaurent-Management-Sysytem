<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryCategory;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Ingredients',
                'description' => 'Raw ingredients for food preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Drinks and liquid refreshments',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging',
                'description' => 'Containers, wraps, and disposable serving items',
                'is_active' => true,
            ],
            [
                'name' => 'Cleaning Supplies',
                'description' => 'Products for maintaining hygiene and cleanliness',
                'is_active' => true,
            ],
            [
                'name' => 'Kitchen Equipment',
                'description' => 'Tools and utensils for food preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery and administrative materials',
                'is_active' => true,
            ],
            [
                'name' => 'Dry Goods',
                'description' => 'Non-perishable food items with long shelf life',
                'is_active' => true,
            ],
            [
                'name' => 'Frozen Foods',
                'description' => 'Perishable items stored at freezing temperatures',
                'is_active' => true,
            ],
            [
                'name' => 'Dairy Products',
                'description' => 'Milk-based products and alternatives',
                'is_active' => true,
            ],
            [
                'name' => 'Bar Supplies',
                'description' => 'Items specifically for bar operations',
                'is_active' => true,
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($categories as $category) {
            // Check if category already exists by name
            if (!InventoryCategory::where('name', $category['name'])->exists()) {
                InventoryCategory::create($category);
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->command->info('Inventory categories seeding completed!');
        $this->command->info("Created {$createdCount} new categories.");
        $this->command->info("Skipped {$skippedCount} existing categories.");
    }
}
