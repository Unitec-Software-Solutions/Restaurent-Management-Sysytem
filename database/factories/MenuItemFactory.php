<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\KitchenStation;
use App\Models\ItemMaster;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        // UI/UX focused allergen categories for better status indicators
        $allergens = ['dairy', 'eggs', 'nuts', 'gluten', 'soy', 'shellfish', 'fish', 'sesame'];
        
        // Restaurant-focused menu item names for better UI display
        $menuItemTypes = [
            'appetizers' => ['Caesar Salad', 'Bruschetta', 'Mozzarella Sticks', 'Chicken Wings', 'Nachos'],
            'mains' => ['Grilled Salmon', 'Beef Steak', 'Chicken Parmesan', 'Pasta Carbonara', 'BBQ Ribs'],
            'beverages' => ['Fresh Orange Juice', 'Cappuccino', 'Iced Tea', 'Smoothie Bowl', 'Wine Selection'],
            'desserts' => ['Chocolate Cake', 'Tiramisu', 'Ice Cream', 'Cheesecake', 'Fruit Tart']
        ];
        
        $category = $this->faker->randomElement(array_keys($menuItemTypes));
        $itemName = $this->faker->randomElement($menuItemTypes[$category]);
        
        // UI-optimized pricing for card displays
        $basePrice = $this->faker->randomFloat(2, 8, 45);
        $isPromotional = $this->faker->boolean(25); // 25% chance of promotion
        
        return [
            // Core identification - optimized for search and display
            'name' => $itemName,
            'description' => $this->generateRestaurantDescription($category),
            'price' => $basePrice,
            
            // Foreign key relationships - FIXED kitchen station dependency
            'menu_category_id' => MenuCategory::factory(),
            'item_master_id' => ItemMaster::factory(),
            'organization_id' => function (array $attributes) {
                return Organization::first()?->id ?? Organization::factory()->create()->id;
            },
            'branch_id' => function (array $attributes) {
                return Branch::first()?->id ?? Branch::factory()->create()->id;
            },
            
            // Kitchen workflow - properly linked to kitchen stations with code generation
            'kitchen_station_id' => function (array $attributes) {
                $branch_id = $attributes['branch_id'];
                
                // First try to find existing station for this branch
                $existingStation = KitchenStation::where('branch_id', $branch_id)->first();
                
                if ($existingStation) {
                    return $existingStation->id;
                }
                
                // Create station with proper code if none exists
                return KitchenStation::factory()->create([
                    'branch_id' => $branch_id,
                    'code' => 'FACT-' . str_pad($branch_id, 2, '0', STR_PAD_LEFT) . '-' . rand(100, 999)
                ])->id;
            },
            
            // UI/UX Display attributes - following card-based design
            'image_path' => function (array $attributes) {
                return 'menu-items/' . strtolower(str_replace(' ', '-', $attributes['name'])) . '.jpg';
            },
            'display_order' => $this->faker->numberBetween(1, 100),
            
            // Status indicators (using status badge patterns from guidelines)
            'is_available' => $this->faker->boolean(90),  // Green badge - bg-green-100 text-green-800
            'is_active' => $this->faker->boolean(95),     // Success indicator
            'is_featured' => $this->faker->boolean(20),   // Primary highlight - bg-indigo-600
            
            // Dietary filter badges (for responsive filter controls)
            'is_vegetarian' => $this->getVegetarianStatus($category),
            'is_spicy' => $this->faker->boolean(30),
            'contains_alcohol' => $category === 'beverages' ? $this->faker->boolean(30) : false,
            
            // Kitchen workflow (for dashboard views)
            'requires_preparation' => $category !== 'beverages' ? $this->faker->boolean(85) : $this->faker->boolean(40),
            'preparation_time' => $this->getPreparationTime($category),
            'station' => $this->getKitchenStation($category),
            
            // JSON fields for detailed views and modals
            'allergens' => $this->faker->randomElements($allergens, $this->faker->numberBetween(0, 4)),
            'calories' => $this->getCalorieRange($category),
            'ingredients' => $this->generateIngredientsList($category),
            
            // Promotional pricing (for featured card displays)
            'promotion_price' => $isPromotional ? round($basePrice * 0.85, 2) : null,
            'promotion_start' => $isPromotional ? $this->faker->dateTimeBetween('now', '+1 week') : null,
            'promotion_end' => $isPromotional ? $this->faker->dateTimeBetween('+1 week', '+1 month') : null,
        ];
    }

    /**
     * Generate restaurant-appropriate descriptions for UI cards
     */
    private function generateRestaurantDescription(string $category): string
    {
        $descriptions = [
            'appetizers' => [
                'Fresh, crispy starter perfect for sharing',
                'Traditional appetizer with modern twist',
                'Light and flavorful beginning to your meal',
                'Chef\'s signature appetizer selection'
            ],
            'mains' => [
                'Expertly prepared main course with seasonal vegetables',
                'House specialty served with chef\'s choice sides',
                'Premium cut cooked to perfection',
                'Traditional recipe with contemporary presentation'
            ],
            'beverages' => [
                'Refreshing beverage made with premium ingredients',
                'Carefully crafted drink selection',
                'Fresh, natural ingredients in every sip',
                'Perfect complement to any meal'
            ],
            'desserts' => [
                'Decadent dessert to complete your dining experience',
                'Sweet finale made fresh daily',
                'Indulgent treat crafted by our pastry chef',
                'Perfect ending to your meal'
            ]
        ];
        
        return $this->faker->randomElement($descriptions[$category] ?? $descriptions['mains']);
    }

    /**
     * Get appropriate vegetarian status based on category
     */
    private function getVegetarianStatus(string $category): bool
    {
        return match($category) {
            'appetizers' => $this->faker->boolean(60),
            'mains' => $this->faker->boolean(25),
            'beverages' => $this->faker->boolean(80),
            'desserts' => $this->faker->boolean(70),
            default => $this->faker->boolean(40)
        };
    }

    /**
     * Get realistic preparation times for kitchen workflow
     */
    private function getPreparationTime(string $category): int
    {
        return match($category) {
            'appetizers' => $this->faker->numberBetween(8, 15),
            'mains' => $this->faker->numberBetween(20, 35),
            'beverages' => $this->faker->numberBetween(3, 8),
            'desserts' => $this->faker->numberBetween(10, 20),
            default => $this->faker->numberBetween(15, 25)
        };
    }

    /**
     * Assign appropriate kitchen station
     */
    private function getKitchenStation(string $category): string
    {
        return match($category) {
            'appetizers' => $this->faker->randomElement(['prep', 'grill']),
            'mains' => $this->faker->randomElement(['cooking', 'grill']),
            'beverages' => 'beverage',
            'desserts' => 'prep',
            default => 'cooking'
        };
    }

    /**
     * Get realistic calorie ranges
     */
    private function getCalorieRange(string $category): int
    {
        return match($category) {
            'appetizers' => $this->faker->numberBetween(150, 400),
            'mains' => $this->faker->numberBetween(400, 800),
            'beverages' => $this->faker->numberBetween(50, 200),
            'desserts' => $this->faker->numberBetween(250, 500),
            default => $this->faker->numberBetween(300, 600)
        };
    }

    /**
     * Generate realistic ingredients list
     */
    private function generateIngredientsList(string $category): string
    {
        $ingredients = [
            'appetizers' => ['lettuce', 'tomatoes', 'cheese', 'herbs', 'olive oil', 'garlic'],
            'mains' => ['protein', 'vegetables', 'herbs', 'spices', 'sauce', 'sides'],
            'beverages' => ['fresh fruits', 'natural flavors', 'premium ingredients'],
            'desserts' => ['flour', 'sugar', 'eggs', 'cream', 'vanilla', 'chocolate']
        ];
        
        $categoryIngredients = $ingredients[$category] ?? $ingredients['mains'];
        $selected = $this->faker->randomElements($categoryIngredients, $this->faker->numberBetween(3, 5));
        
        return implode(', ', $selected);
    }

    /**
     * UI States for different display contexts following design system
     */

    // Primary button state - featured items (bg-indigo-600)
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_active' => true,
            'is_available' => true,
            'display_order' => $this->faker->numberBetween(1, 20), // High priority display
            'image_path' => 'menu-items/featured/' . strtolower(str_replace(' ', '-', $attributes['name'])) . '.jpg'
        ]);
    }

    // Success state - popular/recommended items (bg-green-600)
    public function recommended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_active' => true,
            'is_available' => true,
            'calories' => $this->faker->numberBetween(300, 500), // Balanced nutrition
        ]);
    }

    // Warning state - items requiring attention (bg-yellow-500)
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => $this->faker->boolean(60), // Sometimes unavailable
            'requires_preparation' => true,
            'preparation_time' => $attributes['preparation_time'] + 10, // Longer prep time
        ]);
    }

    // Info state - special dietary items (bg-blue-600)
    public function specialDiet(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vegetarian' => true,
            'allergens' => ['gluten'], // Common dietary restriction
            'calories' => $this->faker->numberBetween(200, 400), // Lower calorie
        ]);
    }

    // Promotional state (primary button highlight)
    public function onPromotion(): static
    {
        return $this->state(function (array $attributes) {
            $originalPrice = $attributes['price'] ?? $this->faker->randomFloat(2, 10, 50);
            $discountPercent = $this->faker->numberBetween(15, 30);
            $promotionPrice = $originalPrice * (1 - $discountPercent / 100);
            
            return [
                'promotion_price' => round($promotionPrice, 2),
                'promotion_start' => now(),
                'promotion_end' => now()->addDays($this->faker->numberBetween(7, 30)),
                'is_featured' => true, // Highlight in UI
                'display_order' => $this->faker->numberBetween(1, 10), // Top of list
            ];
        });
    }

    // Quick service items (for dashboard quick actions)
    public function quickServe(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_preparation' => true,
            'preparation_time' => $this->faker->numberBetween(5, 12),
            'station' => 'prep',
            'is_available' => true,
        ]);
    }

    // Kitchen workflow states
    public function beverage(): static
    {
        return $this->state(fn (array $attributes) => [
            'station' => 'beverage',
            'requires_preparation' => $this->faker->boolean(60),
            'preparation_time' => $this->faker->numberBetween(2, 8),
            'contains_alcohol' => $this->faker->boolean(40),
            'calories' => $this->faker->numberBetween(50, 200),
        ]);
    }

    public function grillItem(): static
    {
        return $this->state(fn (array $attributes) => [
            'station' => 'grill',
            'requires_preparation' => true,
            'preparation_time' => $this->faker->numberBetween(15, 25),
            'is_spicy' => $this->faker->boolean(40),
        ]);
    }

    // Card display optimization following UI guidelines
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => 'menu-items/featured/' . strtolower(str_replace(' ', '-', $attributes['name'])) . '.jpg',
        ]);
    }
}
