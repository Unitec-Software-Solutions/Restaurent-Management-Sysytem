<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table first
        DB::table('menu_items')->truncate();

        $menuItems = [
            // ==================== Breakfast Items ====================
            [
                'menu_category_id' => 1, // Breakfast
                'name' => 'Egg Hoppers',
                'description' => 'Traditional bowl-shaped pancakes with a perfectly cooked egg in the center',
                'price' => 350,
                'image_path' => 'breakfast/egg-hoppers.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 15,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => json_encode(['eggs']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 1,
                'name' => 'String Hoppers with Coconut Sambol',
                'description' => 'Steamed rice noodle nests served with freshly grated coconut mixed with chili, onion, and lime',
                'price' => 400,
                'image_path' => 'breakfast/string-hoppers.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 20,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],
            [
                'menu_category_id' => 1,
                'name' => 'Kottu Roti',
                'description' => 'Chopped roti stir-fried with vegetables, egg, and spices (vegetarian option available)',
                'price' => 600,
                'image_path' => 'breakfast/kottu.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 25,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['gluten', 'eggs']),
                'is_active' => true,
            ],

            // ==================== Rice & Curry ====================
            [
                'menu_category_id' => 13, // Rice & Curry
                'name' => 'White Rice with 3 Curries',
                'description' => 'Steamed rice with choice of three curries (meat, fish, or vegetable options)',
                'price' => 800,
                'image_path' => 'rice-curry/rice-three-curry.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 15,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],
            [
                'menu_category_id' => 13,
                'name' => 'Chicken Curry Special',
                'description' => 'Traditional Sri Lankan chicken curry with potatoes, served with rice, papadum, and sambol',
                'price' => 950,
                'image_path' => 'rice-curry/chicken-curry.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 30,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],
            [
                'menu_category_id' => 13,
                'name' => 'Jackfruit Curry (Vegetarian)',
                'description' => 'Young jackfruit cooked in aromatic spices, a popular meat substitute',
                'price' => 750,
                'image_path' => 'rice-curry/jackfruit-curry.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 25,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],

            // ==================== Colombo Favorites ====================
            [
                'menu_category_id' => 5, // Colombo Favorites
                'name' => 'Lamprais',
                'description' => 'Dutch-influenced dish with rice, meat curry, and accompaniments baked in banana leaf',
                'price' => 1200,
                'image_path' => 'colombo/lamprais.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 40,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['gluten']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 5,
                'name' => 'Kottu with Cheese',
                'description' => 'Colombo street food favorite - chopped roti with vegetables, egg, and melted cheese',
                'price' => 850,
                'image_path' => 'colombo/cheese-kottu.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 20,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['gluten', 'dairy']),
                'is_active' => true,
            ],

            // ==================== Kandy Specials ====================
            [
                'menu_category_id' => 6, // Kandy Specials
                'name' => 'Kandy Mixed Rice',
                'description' => 'Fragrant rice cooked with spices, served with accompaniments typical of hill country cuisine',
                'price' => 900,
                'image_path' => 'kandy/mixed-rice.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 25,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],

            // ==================== Jaffna Cuisine ====================
            [
                'menu_category_id' => 7, // Jaffna Cuisine
                'name' => 'Jaffna Crab Curry',
                'description' => 'Spicy crab curry with Jaffna-style roasted curry powder',
                'price' => 1800,
                'image_path' => 'jaffna/crab-curry.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 35,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['shellfish']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 7,
                'name' => 'Jaffna Mutton Rolls',
                'description' => 'Spicy mutton wrapped in a crisp pastry - a Northern specialty',
                'price' => 300,
                'image_path' => 'jaffna/mutton-rolls.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 20,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['gluten']),
                'is_active' => true,
            ],

            // ==================== Coastal Delights ====================
            [
                'menu_category_id' => 8, // Coastal Delights
                'name' => 'Ambul Thiyal (Sour Fish Curry)',
                'description' => 'Traditional sour fish curry preserved with goraka (tamarind-like fruit)',
                'price' => 1100,
                'image_path' => 'coastal/ambal-thiyal.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 30,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['fish']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 8,
                'name' => 'Prawns in Coconut Milk',
                'description' => 'Fresh prawns cooked in rich coconut milk with spices',
                'price' => 1600,
                'image_path' => 'coastal/prawns-coconut.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 25,
                'station' => 'kitchen',
                'is_vegetarian' => false,
                'contains_alcohol' => false,
                'allergens' => json_encode(['shellfish']),
                'is_active' => true,
            ],

            // ==================== Avurudu Special ====================
            [
                'menu_category_id' => 9, // Avurudu Special
                'name' => 'Kiribath (Milk Rice)',
                'description' => 'Traditional Sinhala New Year rice cooked in coconut milk, served with lunu miris',
                'price' => 500,
                'image_path' => 'seasonal/kiribath.jpg',
                'is_available' => false, // Only available during Avurudu season
                'requires_preparation' => true,
                'preparation_time' => 30,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => json_encode(['dairy']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 9,
                'name' => 'Kokis',
                'description' => 'Crispy, deep-fried snack made from rice flour and coconut milk',
                'price' => 250,
                'image_path' => 'seasonal/kokis.jpg',
                'is_available' => false,
                'requires_preparation' => true,
                'preparation_time' => 20,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => json_encode(['gluten']),
                'is_active' => true,
            ],

            // ==================== Beverages ====================
            [
                'menu_category_id' => 16, // Beverages
                'name' => 'King Coconut Water',
                'description' => 'Fresh king coconut (thambili) water, naturally sweet and refreshing',
                'price' => 200,
                'image_path' => 'beverages/king-coconut.jpg',
                'is_available' => true,
                'requires_preparation' => false,
                'preparation_time' => 2,
                'station' => 'bar',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],
            [
                'menu_category_id' => 16,
                'name' => 'Wood Apple Juice',
                'description' => 'Traditional Sri Lankan wood apple (divul) juice with honey',
                'price' => 300,
                'image_path' => 'beverages/wood-apple.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 10,
                'station' => 'bar',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => null,
                'is_active' => true,
            ],
            [
                'menu_category_id' => 16,
                'name' => 'Ceylon Arrack Cocktail',
                'description' => 'Signature cocktail made with Sri Lankan coconut arrack',
                'price' => 1200,
                'image_path' => 'beverages/arrack-cocktail.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 8,
                'station' => 'bar',
                'is_vegetarian' => true,
                'contains_alcohol' => true,
                'allergens' => null,
                'is_active' => true,
            ],

            // ==================== Desserts ====================
            [
                'menu_category_id' => 17, // Desserts
                'name' => 'Watalappan',
                'description' => 'Sri Lankan coconut custard pudding with jaggery and cardamom',
                'price' => 450,
                'image_path' => 'desserts/watalappan.jpg',
                'is_available' => true,
                'requires_preparation' => true,
                'preparation_time' => 15,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => json_encode(['eggs', 'dairy']),
                'is_active' => true,
            ],
            [
                'menu_category_id' => 17,
                'name' => 'Kiri Pani (Curd & Treacle)',
                'description' => 'Traditional buffalo milk curd with kithul palm treacle',
                'price' => 400,
                'image_path' => 'desserts/kiri-pani.jpg',
                'is_available' => true,
                'requires_preparation' => false,
                'preparation_time' => 5,
                'station' => 'kitchen',
                'is_vegetarian' => true,
                'contains_alcohol' => false,
                'allergens' => json_encode(['dairy']),
                'is_active' => true,
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}