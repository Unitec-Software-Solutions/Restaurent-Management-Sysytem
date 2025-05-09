<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FoodItem;
use Illuminate\Support\Facades\Schema;

class FoodItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FoodItem::create([
            'name' => 'Cheese Pizza',
            'price' => 10.99,
            'cost' => 5.50,
            'ingredients' => 'Cheese, Tomato Sauce, Dough',
            'image_url' => 'images/pizza.jpg',
            'prep_time' => 20,
            'is_active' => true,
            'portion_size' => 'full',
            'display_in_menu' => true,
            'available_from' => '10:00',
            'available_to' => '22:00',
            'days_available' => 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'promotions' => false,
            'discounts' => null,
        ]);

        // Add more seed data as needed
    }
}