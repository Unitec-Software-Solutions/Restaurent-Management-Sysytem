<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\KitchenStation;
use App\Models\Branch;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds following UI/UX guidelines.
     */
    public function run(): void
    {
        $this->command->info('🍽️ Seeding menu items with comprehensive restaurant data...');

        $branches = Branch::with(['kitchenStations', 'menuCategories'])->get();

        foreach ($branches as $branch) {
            // Ensure we have categories and stations
            if ($branch->menuCategories->isEmpty()) {
                $this->createDefaultCategories($branch);
                $branch->refresh();
            }

            if ($branch->kitchenStations->isEmpty()) {
                $this->createDefaultStations($branch);
                $branch->refresh();
            }

            $this->createMenuItems($branch);
        }

        $this->command->info('✅ Menu Items seeded successfully');
    }

    /**
     * Create default menu categories for a branch
     */
    private function createDefaultCategories(Branch $branch): void
    {
        $categories = [
            [
                'name' => 'Appetizers',
                'description' => 'Start your meal with our delicious appetizers',
                'display_order' => 1,
                'image_path' => 'categories/appetizers.jpg'
            ],
            [
                'name' => 'Main Course',
                'description' => 'Hearty main dishes to satisfy your appetite',
                'display_order' => 2,
                'image_path' => 'categories/mains.jpg'
            ],
            [
                'name' => 'Beverages',
                'description' => 'Refreshing drinks and specialty beverages',
                'display_order' => 3,
                'image_path' => 'categories/beverages.jpg'
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet endings to your perfect meal',
                'display_order' => 4,
                'image_path' => 'categories/desserts.jpg'
            ]
        ];

        foreach ($categories as $categoryData) {
            MenuCategory::create([
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                ...$categoryData,
                'is_active' => true
            ]);
        }
    }

    /**
     * Create default kitchen stations with proper codes - FIXED
     */
    private function createDefaultStations(Branch $branch): void
    {
        $stations = [
            [
                'name' => 'Hot Kitchen',
                'code' => 'HOT-' . str_pad($branch->id, 2, '0', STR_PAD_LEFT) . '-001',
                'type' => 'cooking',
                'description' => 'Main cooking station for hot dishes',
                'order_priority' => 1
            ],
            [
                'name' => 'Cold Kitchen',
                'code' => 'COLD-' . str_pad($branch->id, 2, '0', STR_PAD_LEFT) . '-002',
                'type' => 'prep',
                'description' => 'Salads and cold preparations',
                'order_priority' => 2
            ],
            [
                'name' => 'Grill Station',
                'code' => 'GRILL-' . str_pad($branch->id, 2, '0', STR_PAD_LEFT) . '-003',
                'type' => 'grill',
                'description' => 'Grilled items and BBQ',
                'order_priority' => 3
            ],
            [
                'name' => 'Beverage Station',
                'code' => 'BEV-' . str_pad($branch->id, 2, '0', STR_PAD_LEFT) . '-004',
                'type' => 'beverage',
                'description' => 'Drinks and beverages',
                'order_priority' => 4
            ]
        ];

        foreach ($stations as $stationData) {
            KitchenStation::create([
                'branch_id' => $branch->id,
                ...$stationData,
                'is_active' => true,
                'max_capacity' => 50.00,
                'printer_config' => [
                    'printer_name' => null,
                    'paper_size' => '80mm',
                    'auto_print' => false
                ],
                'notes' => 'Auto-created default station'
            ]);
        }
    }

    /**
     * Create sample menu items following UI/UX guidelines
     */
    private function createMenuItems(Branch $branch): void
    {
        $menuItems = [
            // Appetizers with UI-optimized data
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with caesar dressing, croutons, and parmesan cheese',
                'price' => 850.00,
                'category' => 'Appetizers',
                'station_type' => 'prep',
                'preparation_time' => 8,
                'is_vegetarian' => true,
                'allergen_info' => ['dairy', 'eggs'],
                'calories' => 280,
                'is_featured' => false,
                'display_order' => 1
            ],
            [
                'name' => 'Buffalo Chicken Wings',
                'description' => 'Spicy chicken wings served with blue cheese dipping sauce',
                'price' => 1200.00,
                'category' => 'Appetizers',
                'station_type' => 'grill',
                'preparation_time' => 15,
                'is_vegetarian' => false,
                'is_spicy' => true,
                'allergen_info' => ['dairy'],
                'calories' => 450,
                'is_featured' => true,
                'display_order' => 2
            ],

            // Main Course items
            [
                'name' => 'Grilled Salmon',
                'description' => 'Fresh Atlantic salmon grilled to perfection with lemon herb seasoning',
                'price' => 2200.00,
                'category' => 'Main Course',
                'station_type' => 'grill',
                'preparation_time' => 22,
                'is_vegetarian' => false,
                'allergen_info' => ['fish'],
                'calories' => 520,
                'is_featured' => true,
                'display_order' => 1
            ],
            [
                'name' => 'Chicken Kottu Roti',
                'description' => 'Traditional Sri Lankan dish with chopped roti, chicken, and vegetables',
                'price' => 1400.00,
                'category' => 'Main Course',
                'station_type' => 'cooking',
                'preparation_time' => 18,
                'is_vegetarian' => false,
                'is_spicy' => true,
                'allergen_info' => ['gluten'],
                'calories' => 650,
                'is_featured' => false,
                'display_order' => 2
            ],
            [
                'name' => 'Vegetable Fried Rice',
                'description' => 'Aromatic fried rice with fresh vegetables and Sri Lankan spices',
                'price' => 950.00,
                'category' => 'Main Course',
                'station_type' => 'cooking',
                'preparation_time' => 12,
                'is_vegetarian' => true,
                'allergen_info' => [],
                'calories' => 420,
                'is_featured' => false,
                'display_order' => 3
            ],

            // Beverages
            [
                'name' => 'Fresh King Coconut Water',
                'description' => 'Natural king coconut water straight from the shell',
                'price' => 250.00,
                'category' => 'Beverages',
                'station_type' => 'beverage',
                'preparation_time' => 3,
                'is_vegetarian' => true,
                'requires_preparation' => false,
                'allergen_info' => [],
                'calories' => 45,
                'is_featured' => false,
                'display_order' => 1
            ],
            [
                'name' => 'Mango Lassi',
                'description' => 'Creamy yogurt drink blended with fresh mango',
                'price' => 350.00,
                'category' => 'Beverages',
                'station_type' => 'beverage',
                'preparation_time' => 5,
                'is_vegetarian' => true,
                'allergen_info' => ['dairy'],
                'calories' => 180,
                'is_featured' => true,
                'display_order' => 2
            ],

            // Desserts
            [
                'name' => 'Watalappan',
                'description' => 'Traditional Sri Lankan coconut custard with jaggery and spices',
                'price' => 450.00,
                'category' => 'Desserts',
                'station_type' => 'prep',
                'preparation_time' => 10,
                'is_vegetarian' => true,
                'allergen_info' => ['dairy', 'eggs'],
                'calories' => 320,
                'is_featured' => true,
                'display_order' => 1
            ],
            [
                'name' => 'Chocolate Brownie',
                'description' => 'Rich chocolate brownie served with vanilla ice cream',
                'price' => 550.00,
                'category' => 'Desserts',
                'station_type' => 'prep',
                'preparation_time' => 8,
                'is_vegetarian' => true,
                'allergen_info' => ['dairy', 'eggs', 'gluten'],
                'calories' => 485,
                'is_featured' => false,
                'display_order' => 2
            ]
        ];

        foreach ($menuItems as $index => $itemData) {
            $category = $branch->menuCategories()->where('name', $itemData['category'])->first();
            $station = $branch->kitchenStations()->where('type', $itemData['station_type'])->first() 
                      ?? $branch->kitchenStations()->first();

            MenuItem::create([
                // Core attributes
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                
                // Relationships
                'menu_category_id' => $category?->id,
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'item_master_id' => null, // Will be linked later if needed
                'kitchen_station_id' => $station?->id,
                
                // Kitchen workflow
                'preparation_time' => $itemData['preparation_time'],
                'requires_preparation' => $itemData['requires_preparation'] ?? true,
                'station' => $itemData['station_type'],
                
                // Dietary information - following UI badge system
                'is_vegetarian' => $itemData['is_vegetarian'] ?? false,
                'is_spicy' => $itemData['is_spicy'] ?? false,
                'contains_alcohol' => $itemData['contains_alcohol'] ?? false,
                
                // Display attributes - UI/UX optimized
                'is_featured' => $itemData['is_featured'] ?? false,
                'display_order' => $itemData['display_order'],
                'is_active' => true,
                'is_available' => true,
                
                // JSON fields for detailed views
                'allergens' => $itemData['allergen_info'],
                'calories' => $itemData['calories'],
                'ingredients' => $this->generateIngredients($itemData['category']),
                
                // Image path following UI guidelines
                'image_path' => 'menu-items/' . strtolower(str_replace(' ', '-', $itemData['name'])) . '.jpg'
            ]);
        }
    }

    /**
     * Generate realistic ingredients based on category
     */
    private function generateIngredients(string $category): string
    {
        $ingredients = [
            'Appetizers' => ['lettuce', 'tomatoes', 'cheese', 'herbs', 'olive oil', 'garlic'],
            'Main Course' => ['rice', 'vegetables', 'spices', 'coconut milk', 'curry leaves'],
            'Beverages' => ['fresh fruits', 'natural flavors', 'premium ingredients'],
            'Desserts' => ['coconut', 'jaggery', 'cardamom', 'vanilla', 'eggs']
        ];
        
        $categoryIngredients = $ingredients[$category] ?? $ingredients['Main Course'];
        $selected = array_slice($categoryIngredients, 0, rand(3, 5));
        
        return implode(', ', $selected);
    }
}
