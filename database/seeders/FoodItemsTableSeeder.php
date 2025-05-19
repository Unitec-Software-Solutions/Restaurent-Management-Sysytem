<?php

// database/seeders/FoodItemsTableSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoodItem;

class FoodItemsTableSeeder extends Seeder
{
    public function run()
    {
        // Sample data for the food_items table
        $foodItems = [
            [
                'name' => 'Pizza',
                'price' => 10.99,
                'cost' => 5.99,
                'ingredients' => 'Cheese, Tomato, Dough',
                'img' => 'pizza.jpg',
                'is_active' => true,
                'pre_time' => 20,
                'portion_size' => 'single',
                'display_in_menu' => true,
                'available_from' => '10:00:00',
                'available_to' => '22:00:00',
                'available_days' => 'Mon,Tue,Wed',
                'promotions' => false,
                'discounts' => 0.00,
            ],
            [
                'name' => 'Burger',
                'price' => 8.99,
                'cost' => 4.50,
                'ingredients' => 'Beef, Cheese, Lettuce, Bun',
                'img' => 'burger.jpg',
                'is_active' => true,
                'pre_time' => 15,
                'portion_size' => 'double',
                'display_in_menu' => true,
                'available_from' => '11:00:00',
                'available_to' => '21:00:00',
                'available_days' => 'Mon,Tue,Wed,Thu',
                'promotions' => true,
                'discounts' => 1.00,
            ],
            [
                'name' => 'Pasta',
                'price' => 12.99,
                'cost' => 6.50,
                'ingredients' => 'Pasta, Tomato Sauce, Cheese',
                'img' => 'pasta.jpg',
                'is_active' => true,
                'pre_time' => 25,
                'portion_size' => 'family',
                'display_in_menu' => true,
                'available_from' => '12:00:00',
                'available_to' => '20:00:00',
                'available_days' => 'Mon,Tue,Wed,Thu,Fri',
                'promotions' => false,
                'discounts' => 0.00,
            ],
        ];

        // Insert the sample data into the food_items table
        foreach ($foodItems as $foodItem) {
            FoodItem::create($foodItem);
        }
    }
}