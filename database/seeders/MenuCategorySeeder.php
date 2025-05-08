<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MenuCategory;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MenuCategory::create([
            'name' => 'Pizza',
            'description' => 'Delicious cheese pizza',
            'price' => 10.99,
            'img' => 'images/pizza.jpg',
            'is_available' => true,
            'category_id' => 1,
            'inventory_item_id' => 1,
        ]);

        // Add more seed data as needed
    }
}
