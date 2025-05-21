<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    public function run()
    {
        MenuItem::create([
            'name' => 'Margherita Pizza',
            'price' => 1200, // <-- change here
            'is_active' => true,
        ]);
        MenuItem::create([
            'name' => 'Chicken Burger',
            'price' => 900, // <-- change here
            'is_active' => true,
        ]);
        MenuItem::create([
            'name' => 'French Fries',
            'price' => 400, // <-- change here
            'is_active' => false,
        ]);
        // Add more items as needed

    }
}
