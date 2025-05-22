<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    public function run()
    {
        MenuItem::create([
            'menu_category_id' => 1, // <-- required!
            'name' => 'Margherita Pizza',
            'price' => 1200,
            'is_active' => true,
        ]);
        MenuItem::create([
            'menu_category_id' => 1,
            'name' => 'Chicken Burger',
            'price' => 900,
            'is_active' => true,
        ]);
        MenuItem::create([
            'menu_category_id' => 1,
            'name' => 'French Fries',
            'price' => 400,
            'is_active' => false,
        ]);
        // Add more items as needed

    }
}
