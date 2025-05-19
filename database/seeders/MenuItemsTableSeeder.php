<?php

// database/seeders/MenuItemsTableSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert data into the menu_items table
        DB::table('menu_items')->insert([
            [
                'name' => 'Pizza Margherita',
                'description' => 'Classic Italian pizza with tomatoes, mozzarella, and basil',
                'price' => 10.99,
                
                'is_available' => 1,
            ],
            [
                'name' => 'Spaghetti Carbonara',
                'description' => 'Pasta with eggs, cheese, pancetta, and pepper',
                'price' => 12.99,
                'category_id' => 2,
                'is_available' => 1,
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Romaine lettuce, croutons, parmesan, and Caesar dressing',
                'price' => 8.99,
                'category_id' => 3,
                'is_available' => 1,
            ],
            [
                'name' => 'Grilled Salmon',
                'description' => 'Freshly grilled salmon with a side of vegetables',
                'price' => 15.99,
                'category_id' => 4,
                'is_available' => 1,
            ],
            [
                'name' => 'Tiramisu',
                'description' => 'Classic Italian dessert with coffee, mascarpone, and cocoa',
                'price' => 6.99,
                'category_id' => 5,
                'is_available' => 1,
            ],
        ]);
    }
}