<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Models\Organization;


class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state for Laravel + PostgreSQL + Tailwind CSS
     */
    public function definition(): array
    {
        // Safe menu item types with guaranteed minimum items
        $menuItemTypes = [
            'appetizers' => [
                'Caesar Salad', 'Buffalo Wings', 'Mozzarella Sticks', 'Bruschetta Trio',
                'Calamari Rings', 'Spinach Dip', 'Chicken Quesadilla', 'Loaded Nachos'
            ],
            'mains' => [
                'Grilled Salmon', 'Beef Steak', 'Chicken Parmesan', 'Pasta Carbonara',
                'Fish and Chips', 'BBQ Ribs', 'Vegetable Stir Fry', 'Lamb Chops'
            ],
            'beverages' => [
                'Fresh Juice', 'Coffee', 'Tea', 'Smoothie',
                'Iced Coffee', 'Hot Chocolate', 'Lemonade', 'Craft Beer'
            ],
            'desserts' => [
                'Chocolate Cake', 'Ice Cream', 'Cheesecake', 'Fruit Tart',
                'Tiramisu', 'Apple Pie', 'Crème Brûlée', 'Chocolate Mousse'
            ]
        ];
        
        // Safely select category and item
        $categories = array_keys($menuItemTypes);
        $category = $this->faker->randomElement($categories);
        $availableItems = $menuItemTypes[$category];
        
        // Safely get an item name - no array size issues
        $itemName = $this->faker->randomElement($availableItems);
        
        // UI-optimized pricing for Tailwind CSS displays
        $basePrice = $this->faker->randomFloat(2, 8, 45);
        $isPromotional = $this->faker->boolean(25); // 25% chance of promotion
        
        return [
            // Core identification - optimized for search and display
            'name' => $itemName,
            'description' => $this->generateRestaurantDescription($category),
            'price' => $basePrice,
            
            // Foreign key relationships - safe defaults
            'menu_category_id' => MenuCategory::factory(),
            'item_masters_id' => function (array $attributes) {
                return ItemMaster::factory()->create([
                    'organization_id' => $this->getOrganizationId($attributes),
                    'is_menu_item' => true,
                    'is_active' => true,
                ])->id;
            },
            
            // Branch and organization relationships
            'branch_id' => function (array $attributes) {
                return MenuCategory::find($attributes['menu_category_id'])?->branch_id ?? Branch::factory()->create()->id;
            },
            'organization_id' => function (array $attributes) {
                return MenuCategory::find($attributes['menu_category_id'])?->organization_id ?? Organization::factory()->create()->id;
            },
            
            // PostgreSQL-optimized fields
            'unicode_name' => function (array $attributes) {
                return $attributes['name']; // Same as name for simplicity
            },
            'short_description' => function (array $attributes) {
                return substr($attributes['description'], 0, 100);
            },
            
            // Restaurant operation fields
            'cost_price' => $basePrice * 0.6, // 40% markup
            'preparation_time' => $this->faker->numberBetween(5, 30),
            'calories' => $this->faker->numberBetween(150, 800),
            'serving_size' => $this->faker->randomElement(['Small', 'Medium', 'Large', 'Regular']),
            
            // Availability and pricing
            'is_available' => $this->faker->boolean(90), // Most items available
            'is_featured' => $this->faker->boolean(20), // Some items featured
            'promotional_price' => $isPromotional ? ($basePrice * 0.85) : null,
            'promotion_start_date' => $isPromotional ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'promotion_end_date' => $isPromotional ? $this->faker->dateTimeBetween('now', '+1 month') : null,
            
            // Kitchen and dietary information
            'kitchen_station' => $this->getKitchenStation($category),
            'dietary_info' => $this->faker->optional(30)->randomElement([
                'vegetarian', 'vegan', 'gluten-free', 'dairy-free', 'nut-free'
            ]),
            'allergen_info' => $this->faker->optional(25)->randomElement([
                'contains nuts', 'contains dairy', 'contains gluten', 'contains eggs'
            ]),
            'spice_level' => $this->faker->optional(40)->randomElement([
                'mild', 'medium', 'hot', 'extra hot'
            ]),
            
            // Ordering and display
            'sort_order' => $this->faker->numberBetween(1, 100),
            'display_order' => $this->faker->numberBetween(1, 100),
            
            // Tailwind CSS optimized metadata for UI
            'metadata' => [
                'ui_color' => $this->faker->randomElement(['red', 'green', 'blue', 'yellow', 'purple']),
                'display_image' => 'menu-items/' . strtolower(str_replace(' ', '-', $itemName)) . '.jpg',
                'tags' => $this->generateMenuTags($category),
                'nutrition_facts' => [
                    'protein' => $this->faker->numberBetween(5, 40) . 'g',
                    'carbs' => $this->faker->numberBetween(10, 60) . 'g',
                    'fat' => $this->faker->numberBetween(2, 25) . 'g'
                ]
            ],
            
            // Status and tracking
            'is_active' => true,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate restaurant-appropriate description based on category
     */
    private function generateRestaurantDescription(string $category): string
    {
        $descriptions = [
            'appetizers' => [
                'A delightful starter to begin your dining experience',
                'Fresh and flavorful small plate perfect for sharing',
                'Crispy and satisfying appetizer made with premium ingredients',
                'Traditional favorite prepared with our signature touch'
            ],
            'mains' => [
                'Hearty main course prepared with fresh, locally sourced ingredients',
                'Signature dish featuring our chef\'s secret recipe and premium cuts',
                'Satisfying entrée that combines traditional flavors with modern presentation',
                'Generous portion of expertly prepared ingredients'
            ],
            'beverages' => [
                'Refreshing beverage crafted with premium ingredients',
                'Perfectly balanced drink to complement your meal',
                'House specialty beverage made fresh to order',
                'Carefully selected beverage to enhance your dining experience'
            ],
            'desserts' => [
                'Indulgent dessert that provides the perfect sweet ending',
                'Artfully crafted dessert made with finest quality ingredients',
                'Decadent treat that will satisfy your sweet tooth',
                'Classic dessert with our signature presentation'
            ]
        ];
        
        $categoryDescriptions = $descriptions[$category] ?? $descriptions['mains'];
        return $this->faker->randomElement($categoryDescriptions);
    }

    /**
     * Get appropriate kitchen station based on menu category
     */
    private function getKitchenStation(string $category): string
    {
        return match($category) {
            'appetizers' => $this->faker->randomElement(['cold_prep', 'hot_prep', 'fry_station']),
            'mains' => $this->faker->randomElement(['grill', 'sauté', 'roast', 'fry_station']),
            'beverages' => 'beverage_station',
            'desserts' => 'pastry_station',
            default => 'general_prep'
        };
    }

    /**
     * Generate menu tags for Tailwind CSS filtering
     */
    private function generateMenuTags(string $category): array
    {
        $baseTags = [$category];
        
        $additionalTags = [
            'appetizers' => ['starter', 'small-plate', 'sharing'],
            'mains' => ['entrée', 'main-course', 'signature'],
            'beverages' => ['drink', 'refreshing', 'cold'],
            'desserts' => ['sweet', 'dessert', 'indulgent']
        ];
        
        $categoryTags = $additionalTags[$category] ?? ['special'];
        
        // Safely add 1-2 additional tags
        $numAdditionalTags = min(2, count($categoryTags));
        $selectedTags = $this->faker->randomElements($categoryTags, $numAdditionalTags);
        
        return array_merge($baseTags, $selectedTags);
    }

    /**
     * Get organization ID safely
     */
    private function getOrganizationId(array $attributes): int
    {
        if (isset($attributes['organization_id'])) {
            return $attributes['organization_id'];
        }
        
        return Organization::first()?->id ?? Organization::factory()->create()->id;
    }

    /**
     * State for appetizers
     */
    public function appetizer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Caesar Salad', 'Buffalo Wings', 'Mozzarella Sticks', 'Bruschetta'
            ]),
            'preparation_time' => $this->faker->numberBetween(5, 12),
            'kitchen_station' => 'cold_prep',
            'is_available' => true,
        ]);
    }

    /**
     * State for main courses
     */
    public function mainCourse(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Grilled Salmon', 'Beef Steak', 'Chicken Parmesan', 'Pasta Carbonara'
            ]),
            'preparation_time' => $this->faker->numberBetween(15, 35),
            'kitchen_station' => 'grill',
            'is_available' => true,
        ]);
    }

    /**
     * State for beverages
     */
    public function beverage(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Fresh Juice', 'Coffee', 'Tea', 'Smoothie'
            ]),
            'preparation_time' => $this->faker->numberBetween(2, 8),
            'kitchen_station' => 'beverage_station',
            'is_available' => true,
        ]);
    }

    /**
     * State for desserts
     */
    public function dessert(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Chocolate Cake', 'Ice Cream', 'Cheesecake', 'Fruit Tart'
            ]),
            'preparation_time' => $this->faker->numberBetween(5, 15),
            'kitchen_station' => 'pastry_station',
            'is_available' => true,
        ]);
    }

    /**
     * State for featured items
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'promotional_price' => $attributes['price'] * 0.9, // 10% discount
            'promotion_start_date' => now(),
            'promotion_end_date' => now()->addDays(30),
        ]);
    }

    /**
     * State for unavailable items
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
