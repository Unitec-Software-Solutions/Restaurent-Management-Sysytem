<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\DB;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table first
        DB::table('menu_categories')->truncate();

        $categories = [
            // Standard Meal Periods
            [
                'name' => 'Breakfast',
                'description' => 'Morning delights to start your day',
                'is_inactive' => false,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Brunch',
                'description' => 'Late morning specialties',
                'is_inactive' => false,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Lunch',
                'description' => 'Midday meals and combos',
                'is_inactive' => false,
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Dinner',
                'description' => 'Evening dining selections',
                'is_inactive' => false,
                'display_order' => 4,
                'is_active' => true,
            ],

            // Sri Lankan Regional Specialties
            [
                'name' => 'Colombo Favorites',
                'description' => 'Popular dishes from the capital region',
                'is_inactive' => false,
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Kandy Specials',
                'description' => 'Hill country traditional dishes',
                'is_inactive' => false,
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Jaffna Cuisine',
                'description' => 'Northern Tamil specialties',
                'is_inactive' => false,
                'display_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Coastal Delights',
                'description' => 'Seafood specialties from the Southern and Western coasts',
                'is_inactive' => false,
                'display_order' => 8,
                'is_active' => true,
            ],

            // Seasonal Menus
            [
                'name' => 'Avurudu Special',
                'description' => 'Traditional Sinhala & Tamil New Year dishes',
                'is_inactive' => true, // Only active during April
                'display_order' => 9,
                'is_active' => false,
            ],
            [
                'name' => 'Christmas Feast',
                'description' => 'Seasonal holiday specialties',
                'is_inactive' => true, // Only active during December
                'display_order' => 10,
                'is_active' => false,
            ],
            [
                'name' => 'Mango Season',
                'description' => 'Special dishes featuring seasonal mangoes',
                'is_inactive' => true, // Only active during mango season
                'display_order' => 11,
                'is_active' => false,
            ],
            [
                'name' => 'Monsoon Warmers',
                'description' => 'Comfort food for rainy days',
                'is_inactive' => true, // Only active during monsoon season
                'display_order' => 12,
                'is_active' => false,
            ],

            // Standard Menu Categories
            [
                'name' => 'Rice & Curry',
                'description' => 'Traditional Sri Lankan rice plates',
                'is_inactive' => false,
                'display_order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Hoppers & String Hoppers',
                'description' => 'Traditional Sri Lankan breakfast and dinner items',
                'is_inactive' => false,
                'display_order' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Short Eats',
                'description' => 'Sri Lankan snacks and finger foods',
                'is_inactive' => false,
                'display_order' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Refreshing drinks and traditional beverages',
                'is_inactive' => false,
                'display_order' => 16,
                'is_active' => true,
            ],
            [
                'name' => 'Desserts',
                'description' => 'Traditional Sri Lankan sweets and international desserts',
                'is_inactive' => false,
                'display_order' => 17,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            MenuCategory::create($category);
        }
    }
}