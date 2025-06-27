<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\ItemCategory;
use Illuminate\Support\Str;

class ComprehensiveMenuItemsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🍽️ Creating comprehensive menu items with dietary restrictions...');

        $organizations = Organization::with('branches')->get();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        foreach ($organizations as $organization) {
            $this->createOrganizationMenuItems($organization);
        }

        $this->command->info('✅ Menu items seeding completed!');
    }

    private function createOrganizationMenuItems($organization)
    {
        $this->command->info("Creating menu items for: {$organization->name}");

        // Create categories first
        $categories = $this->createCategories($organization);
        
        // Create comprehensive menu items
        $this->createMenuItems($organization, $categories);
        
        // Create seasonal and special items
        $this->createSeasonalItems($organization, $categories);
        
        // Create dietary-specific items
        $this->createDietarySpecificItems($organization, $categories);
    }

    private function createCategories($organization)
    {
        $categoryData = [
            'Appetizers' => [
                'description' => 'Start your meal with our delicious appetizers',
                'display_order' => 1,
                'is_active' => true,
            ],
            'Salads' => [
                'description' => 'Fresh and healthy salad options',
                'display_order' => 2,
                'is_active' => true,
            ],
            'Soups' => [
                'description' => 'Warm and comforting soup selections',
                'display_order' => 3,
                'is_active' => true,
            ],
            'Main Courses' => [
                'description' => 'Our signature main course dishes',
                'display_order' => 4,
                'is_active' => true,
            ],
            'Seafood' => [
                'description' => 'Fresh catch and seafood specialties',
                'display_order' => 5,
                'is_active' => true,
            ],
            'Vegetarian' => [
                'description' => 'Plant-based delicious options',
                'display_order' => 6,
                'is_active' => true,
            ],
            'Vegan' => [
                'description' => 'Completely plant-based dishes',
                'display_order' => 7,
                'is_active' => true,
            ],
            'Gluten-Free' => [
                'description' => 'Gluten-free options for dietary restrictions',
                'display_order' => 8,
                'is_active' => true,
            ],
            'Desserts' => [
                'description' => 'Sweet endings to your meal',
                'display_order' => 9,
                'is_active' => true,
            ],
            'Beverages' => [
                'description' => 'Refreshing drinks and beverages',
                'display_order' => 10,
                'is_active' => true,
            ],
            'Kids Menu' => [
                'description' => 'Special dishes for our young guests',
                'display_order' => 11,
                'is_active' => true,
            ],
            'Breakfast' => [
                'description' => 'Start your day right with our breakfast menu',
                'display_order' => 12,
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoryData as $name => $data) {
            $category = ItemCategory::firstOrCreate([
                'name' => $name,
                'organization_id' => $organization->id,
            ], $data);
            
            $categories[strtolower(str_replace([' ', '-'], '_', $name))] = $category;
        }

        return $categories;
    }

    private function createMenuItems($organization, $categories)
    {
        $menuItems = [
            // Appetizers
            [
                'name' => 'Buffalo Wings',
                'category' => 'appetizers',
                'description' => 'Crispy chicken wings tossed in spicy buffalo sauce, served with celery sticks and blue cheese dip',
                'selling_price' => 1850.00,
                'cost_price' => 950.00,
                'preparation_time' => 15,
                'dietary_restrictions' => ['gluten_containing'],
                'allergens' => ['dairy', 'gluten'],
                'spice_level' => 'hot',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Spinach and Artichoke Dip',
                'category' => 'appetizers',
                'description' => 'Creamy spinach and artichoke dip served hot with tortilla chips',
                'selling_price' => 1650.00,
                'cost_price' => 750.00,
                'preparation_time' => 12,
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Vegan Spring Rolls',
                'category' => 'appetizers',
                'description' => 'Fresh vegetables wrapped in rice paper, served with peanut dipping sauce',
                'selling_price' => 1450.00,
                'cost_price' => 650.00,
                'preparation_time' => 10,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => ['nuts'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Salads
            [
                'name' => 'Caesar Salad',
                'category' => 'salads',
                'description' => 'Crisp romaine lettuce, parmesan cheese, croutons, and classic Caesar dressing',
                'selling_price' => 1750.00,
                'cost_price' => 800.00,
                'preparation_time' => 8,
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy', 'gluten', 'eggs'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Quinoa Power Bowl',
                'category' => 'salads',
                'description' => 'Quinoa, kale, roasted vegetables, avocado, and tahini dressing',
                'selling_price' => 2150.00,
                'cost_price' => 1100.00,
                'preparation_time' => 12,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => ['sesame'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => true,
            ],

            // Soups
            [
                'name' => 'Tom Yum Soup',
                'category' => 'soups',
                'description' => 'Spicy and sour Thai soup with shrimp, mushrooms, and lemongrass',
                'selling_price' => 1950.00,
                'cost_price' => 950.00,
                'preparation_time' => 18,
                'dietary_restrictions' => ['gluten_free'],
                'allergens' => ['shellfish'],
                'spice_level' => 'very_hot',
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Roasted Vegetable Soup',
                'category' => 'soups',
                'description' => 'Hearty soup made with seasonal roasted vegetables and herbs',
                'selling_price' => 1550.00,
                'cost_price' => 700.00,
                'preparation_time' => 15,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => [],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Main Courses
            [
                'name' => 'Grilled Salmon Teriyaki',
                'category' => 'main_courses',
                'description' => 'Fresh Atlantic salmon grilled to perfection with teriyaki glaze, served with steamed rice and vegetables',
                'selling_price' => 3850.00,
                'cost_price' => 2200.00,
                'preparation_time' => 25,
                'dietary_restrictions' => ['gluten_free'],
                'allergens' => ['fish', 'soy'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Ribeye Steak',
                'category' => 'main_courses',
                'description' => '12oz prime ribeye steak grilled to your preference, served with mashed potatoes and seasonal vegetables',
                'selling_price' => 4950.00,
                'cost_price' => 2800.00,
                'preparation_time' => 30,
                'dietary_restrictions' => ['gluten_free'],
                'allergens' => ['dairy'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Mushroom Risotto',
                'category' => 'main_courses',
                'description' => 'Creamy arborio rice with wild mushrooms, parmesan cheese, and truffle oil',
                'selling_price' => 2850.00,
                'cost_price' => 1400.00,
                'preparation_time' => 35,
                'dietary_restrictions' => ['vegetarian', 'gluten_free'],
                'allergens' => ['dairy'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Seafood
            [
                'name' => 'Seafood Paella',
                'category' => 'seafood',
                'description' => 'Traditional Spanish rice dish with shrimp, mussels, clams, and saffron',
                'selling_price' => 4250.00,
                'cost_price' => 2300.00,
                'preparation_time' => 40,
                'dietary_restrictions' => ['gluten_free'],
                'allergens' => ['shellfish'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Fish and Chips',
                'category' => 'seafood',
                'description' => 'Beer-battered cod served with thick-cut fries and mushy peas',
                'selling_price' => 2750.00,
                'cost_price' => 1400.00,
                'preparation_time' => 20,
                'dietary_restrictions' => [],
                'allergens' => ['fish', 'gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Vegetarian
            [
                'name' => 'Eggplant Parmesan',
                'category' => 'vegetarian',
                'description' => 'Breaded and baked eggplant layered with marinara sauce and mozzarella cheese',
                'selling_price' => 2650.00,
                'cost_price' => 1200.00,
                'preparation_time' => 30,
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy', 'gluten', 'eggs'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Vegetable Curry',
                'category' => 'vegetarian',
                'description' => 'Mixed vegetables in aromatic curry sauce, served with basmati rice',
                'selling_price' => 2350.00,
                'cost_price' => 1000.00,
                'preparation_time' => 25,
                'dietary_restrictions' => ['vegetarian', 'gluten_free'],
                'allergens' => ['dairy'],
                'spice_level' => 'hot',
                'is_available' => true,
                'is_featured' => true,
            ],

            // Vegan
            [
                'name' => 'Quinoa Buddha Bowl',
                'category' => 'vegan',
                'description' => 'Roasted vegetables, quinoa, chickpeas, and tahini dressing',
                'selling_price' => 2450.00,
                'cost_price' => 1100.00,
                'preparation_time' => 20,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => ['sesame'],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Vegan Burger',
                'category' => 'vegan',
                'description' => 'Plant-based patty with lettuce, tomato, and vegan mayo on a whole wheat bun',
                'selling_price' => 2250.00,
                'cost_price' => 1000.00,
                'preparation_time' => 15,
                'dietary_restrictions' => ['vegan'],
                'allergens' => ['gluten', 'soy'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Gluten-Free
            [
                'name' => 'Grilled Chicken Breast (GF)',
                'category' => 'gluten_free',
                'description' => 'Herb-seasoned grilled chicken breast with roasted sweet potatoes and green beans',
                'selling_price' => 2950.00,
                'cost_price' => 1500.00,
                'preparation_time' => 22,
                'dietary_restrictions' => ['gluten_free'],
                'allergens' => [],
                'spice_level' => 'mild',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Desserts
            [
                'name' => 'Chocolate Lava Cake',
                'category' => 'desserts',
                'description' => 'Warm chocolate cake with molten center, served with vanilla ice cream',
                'selling_price' => 1450.00,
                'cost_price' => 650.00,
                'preparation_time' => 18,
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy', 'eggs', 'gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Vegan Chocolate Mousse',
                'category' => 'desserts',
                'description' => 'Rich chocolate mousse made with coconut cream and dark chocolate',
                'selling_price' => 1250.00,
                'cost_price' => 550.00,
                'preparation_time' => 10,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => [],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Beverages
            [
                'name' => 'Fresh Mango Lassi',
                'category' => 'beverages',
                'description' => 'Creamy yogurt drink blended with fresh mango and cardamom',
                'selling_price' => 850.00,
                'cost_price' => 350.00,
                'preparation_time' => 5,
                'dietary_restrictions' => ['vegetarian', 'gluten_free'],
                'allergens' => ['dairy'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Green Smoothie',
                'category' => 'beverages',
                'description' => 'Spinach, kale, apple, banana, and coconut water blend',
                'selling_price' => 950.00,
                'cost_price' => 400.00,
                'preparation_time' => 5,
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => [],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => true,
            ],

            // Kids Menu
            [
                'name' => 'Kids Chicken Nuggets',
                'category' => 'kids_menu',
                'description' => 'Crispy chicken nuggets served with fries and apple slices',
                'selling_price' => 1450.00,
                'cost_price' => 700.00,
                'preparation_time' => 12,
                'dietary_restrictions' => [],
                'allergens' => ['gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Mini Cheese Pizza',
                'category' => 'kids_menu',
                'description' => 'Personal-sized cheese pizza with kid-friendly toppings',
                'selling_price' => 1250.00,
                'cost_price' => 600.00,
                'preparation_time' => 15,
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy', 'gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],

            // Breakfast
            [
                'name' => 'Full English Breakfast',
                'category' => 'breakfast',
                'description' => 'Two eggs, bacon, sausage, baked beans, grilled tomato, and toast',
                'selling_price' => 2150.00,
                'cost_price' => 1000.00,
                'preparation_time' => 18,
                'dietary_restrictions' => [],
                'allergens' => ['eggs', 'gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Vegan Pancakes',
                'category' => 'breakfast',
                'description' => 'Fluffy pancakes made without eggs or dairy, served with maple syrup and fresh berries',
                'selling_price' => 1650.00,
                'cost_price' => 750.00,
                'preparation_time' => 15,
                'dietary_restrictions' => ['vegan'],
                'allergens' => ['gluten'],
                'spice_level' => 'none',
                'is_available' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($menuItems as $itemData) {
            $category = $categories[$itemData['category']] ?? null;
            if (!$category) continue;

            ItemMaster::create([
                'organization_id' => $organization->id,
                'category_id' => $category->id,
                'item_code' => $this->generateItemCode($itemData['name']),
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'category' => $category->name,
                'unit_of_measure' => 'each',
                'selling_price' => $itemData['selling_price'],
                'cost_price' => $itemData['cost_price'],
                'is_menu_item' => true,
                'active' => $itemData['is_available'],
                'is_featured' => $itemData['is_featured'],
                'preparation_time' => $itemData['preparation_time'],
                'dietary_restrictions' => json_encode($itemData['dietary_restrictions']),
                'allergens' => json_encode($itemData['allergens']),
                'spice_level' => $itemData['spice_level'],
                'nutritional_info' => json_encode($this->generateNutritionalInfo()),
                'ingredients' => json_encode($this->generateIngredients($itemData['name'])),
            ]);
        }
    }

    private function createSeasonalItems($organization, $categories)
    {
        $seasonalItems = [
            [
                'name' => 'Pumpkin Spice Latte',
                'category' => 'beverages',
                'description' => 'Seasonal coffee drink with pumpkin, cinnamon, and nutmeg',
                'selling_price' => 850.00,
                'cost_price' => 350.00,
                'available_from' => '2024-09-01',
                'available_until' => '2024-11-30',
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy'],
                'seasonal' => true,
            ],
            [
                'name' => 'Summer Berry Salad',
                'category' => 'salads',
                'description' => 'Mixed greens with fresh strawberries, blueberries, and balsamic vinaigrette',
                'selling_price' => 1950.00,
                'cost_price' => 900.00,
                'available_from' => '2024-06-01',
                'available_until' => '2024-08-31',
                'dietary_restrictions' => ['vegan', 'gluten_free'],
                'allergens' => [],
                'seasonal' => true,
            ],
            [
                'name' => 'Winter Warming Soup',
                'category' => 'soups',
                'description' => 'Hearty root vegetable soup with herbs and cream',
                'selling_price' => 1650.00,
                'cost_price' => 750.00,
                'available_from' => '2024-12-01',
                'available_until' => '2025-02-28',
                'dietary_restrictions' => ['vegetarian'],
                'allergens' => ['dairy'],
                'seasonal' => true,
            ],
        ];

        foreach ($seasonalItems as $itemData) {
            $category = $categories[$itemData['category']] ?? null;
            if (!$category) continue;

            ItemMaster::create([
                'organization_id' => $organization->id,
                'category_id' => $category->id,
                'item_code' => $this->generateItemCode($itemData['name']),
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'category' => $category->name,
                'unit_of_measure' => 'each',
                'selling_price' => $itemData['selling_price'],
                'cost_price' => $itemData['cost_price'],
                'is_menu_item' => true,
                'active' => true,
                'is_seasonal' => true,
                'available_from' => $itemData['available_from'],
                'available_until' => $itemData['available_until'],
                'dietary_restrictions' => json_encode($itemData['dietary_restrictions']),
                'allergens' => json_encode($itemData['allergens']),
                'preparation_time' => rand(10, 25),
                'nutritional_info' => json_encode($this->generateNutritionalInfo()),
            ]);
        }
    }

    private function createDietarySpecificItems($organization, $categories)
    {
        $dietaryItems = [
            // Keto-friendly
            [
                'name' => 'Keto Cauliflower Rice Bowl',
                'category' => 'main_courses',
                'description' => 'Cauliflower rice with grilled chicken, avocado, and low-carb vegetables',
                'selling_price' => 2650.00,
                'dietary_restrictions' => ['keto', 'gluten_free', 'low_carb'],
                'allergens' => [],
                'nutrition_highlight' => 'High protein, low carb',
            ],
            
            // Diabetic-friendly
            [
                'name' => 'Diabetic-Friendly Grilled Fish',
                'category' => 'main_courses',
                'description' => 'Grilled white fish with steamed vegetables and herbs',
                'selling_price' => 2850.00,
                'dietary_restrictions' => ['diabetic_friendly', 'gluten_free', 'low_sodium'],
                'allergens' => ['fish'],
                'nutrition_highlight' => 'Low sugar, controlled carbs',
            ],
            
            // High protein
            [
                'name' => 'Protein Power Plate',
                'category' => 'main_courses',
                'description' => 'Grilled chicken breast, Greek yogurt, quinoa, and roasted nuts',
                'selling_price' => 2950.00,
                'dietary_restrictions' => ['high_protein', 'gluten_free'],
                'allergens' => ['dairy', 'nuts'],
                'nutrition_highlight' => 'Over 40g protein',
            ],
            
            // Low sodium
            [
                'name' => 'Herb-Crusted Salmon (Low Sodium)',
                'category' => 'main_courses',
                'description' => 'Fresh herbs and lemon-crusted salmon with herb roasted potatoes',
                'selling_price' => 3250.00,
                'dietary_restrictions' => ['low_sodium', 'gluten_free'],
                'allergens' => ['fish'],
                'nutrition_highlight' => 'Less than 500mg sodium',
            ],
            
            // Raw/Living foods
            [
                'name' => 'Raw Zucchini Noodles',
                'category' => 'vegan',
                'description' => 'Spiralized zucchini with raw cashew cream sauce and fresh herbs',
                'selling_price' => 2150.00,
                'dietary_restrictions' => ['raw', 'vegan', 'gluten_free', 'keto'],
                'allergens' => ['nuts'],
                'nutrition_highlight' => 'Raw, enzyme-rich',
            ],
        ];

        foreach ($dietaryItems as $itemData) {
            $category = $categories[$itemData['category']] ?? $categories['main_courses'];

            ItemMaster::create([
                'organization_id' => $organization->id,
                'category_id' => $category->id,
                'item_code' => $this->generateItemCode($itemData['name']),
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'category' => $category->name,
                'unit_of_measure' => 'each',
                'selling_price' => $itemData['selling_price'],
                'cost_price' => $itemData['selling_price'] * 0.55, // 45% margin
                'is_menu_item' => true,
                'active' => true,
                'is_specialty_diet' => true,
                'dietary_restrictions' => json_encode($itemData['dietary_restrictions']),
                'allergens' => json_encode($itemData['allergens']),
                'preparation_time' => rand(15, 30),
                'nutrition_highlight' => $itemData['nutrition_highlight'],
                'nutritional_info' => json_encode($this->generateAdvancedNutritionalInfo($itemData['dietary_restrictions'])),
            ]);
        }
    }

    private function generateItemCode($name)
    {
        // Create item code from first letters of words
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Add random numbers to ensure uniqueness
        $code .= str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $code;
    }

    private function generateNutritionalInfo()
    {
        return [
            'calories' => rand(200, 800),
            'protein' => rand(5, 45) . 'g',
            'carbs' => rand(10, 60) . 'g',
            'fat' => rand(5, 35) . 'g',
            'fiber' => rand(2, 15) . 'g',
            'sodium' => rand(200, 1500) . 'mg',
            'sugar' => rand(2, 25) . 'g',
        ];
    }

    private function generateAdvancedNutritionalInfo($dietaryRestrictions)
    {
        $base = $this->generateNutritionalInfo();
        
        // Adjust based on dietary restrictions
        if (in_array('keto', $dietaryRestrictions)) {
            $base['carbs'] = rand(5, 15) . 'g';
            $base['fat'] = rand(25, 45) . 'g';
            $base['net_carbs'] = rand(2, 10) . 'g';
        }
        
        if (in_array('low_sodium', $dietaryRestrictions)) {
            $base['sodium'] = rand(100, 400) . 'mg';
        }
        
        if (in_array('high_protein', $dietaryRestrictions)) {
            $base['protein'] = rand(35, 60) . 'g';
        }
        
        if (in_array('diabetic_friendly', $dietaryRestrictions)) {
            $base['sugar'] = rand(2, 8) . 'g';
            $base['glycemic_index'] = rand(25, 45);
        }

        return $base;
    }

    private function generateIngredients($itemName)
    {
        // Generate sample ingredients based on item name
        $commonIngredients = [
            'olive oil', 'salt', 'black pepper', 'garlic', 'onion',
            'fresh herbs', 'lemon juice', 'butter', 'flour', 'eggs'
        ];

        $specificIngredients = [];
        
        if (str_contains(strtolower($itemName), 'chicken')) {
            $specificIngredients = ['chicken breast', 'chicken thigh', 'herbs', 'seasoning'];
        } elseif (str_contains(strtolower($itemName), 'salmon')) {
            $specificIngredients = ['fresh salmon', 'dill', 'lemon', 'capers'];
        } elseif (str_contains(strtolower($itemName), 'vegetable')) {
            $specificIngredients = ['mixed vegetables', 'zucchini', 'bell peppers', 'carrots'];
        } elseif (str_contains(strtolower($itemName), 'chocolate')) {
            $specificIngredients = ['dark chocolate', 'cocoa powder', 'vanilla', 'sugar'];
        }

        return array_merge($specificIngredients, array_slice($commonIngredients, 0, rand(3, 6)));
    }
}
