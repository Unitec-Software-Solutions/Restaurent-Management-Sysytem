<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food',
                'code' => 'FD',
                'description' => 'All food items',
                'is_active' => true,
            ],
            [
                'name' => 'Beverages',
                'code' => 'BV',
                'description' => 'Drinks and beverages',
                'is_active' => true,
            ],
            [
                'name' => 'Cleaning Supplies',
                'code' => 'CS',
                'description' => 'Cleaning and sanitation products',
                'is_active' => true,
            ],
            [
                'name' => 'Kitchen Equipment',
                'code' => 'KE',
                'description' => 'Kitchen tools and equipment',
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OS',
                'description' => 'Office stationery and supplies',
                'is_active' => true,
            ],
        ];
        
        foreach ($categories as $category) {
            // Check if category exists by name or code
            $existingCategory = ItemCategory::where('name', $category['name'])
                ->orWhere('code', $category['code'])
                ->first();

            if (!$existingCategory) {
                ItemCategory::create($category);
            }
        }
    }
}