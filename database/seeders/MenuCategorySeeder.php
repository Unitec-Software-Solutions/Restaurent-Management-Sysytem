<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;

class MenuCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Main Dishes',
                'description' => 'Primary entrees and main course items',
                'is_active' => true
            ],
            [
                'name' => 'Appetizers',
                'description' => 'Starters and small plates',
                'is_active' => true
            ],
            [
                'name' => 'Beverages',
                'description' => 'Drinks and refreshments',
                'is_active' => true
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet treats and desserts',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            MenuCategory::create($category);
        }

        $this->command->info('Menu categories seeded successfully!');
    }
}