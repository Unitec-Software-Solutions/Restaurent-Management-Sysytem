<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ItemMaster;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\ItemCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemMaster>
 */
class ItemMasterFactory extends Factory
{
    protected $model = ItemMaster::class;

    /**
     * Define the model's default state for Laravel + PostgreSQL + Tailwind CSS
     */
    public function definition(): array
    {
        // Restaurant inventory categories optimized for PostgreSQL and Tailwind CSS
        $categories = [
            'ingredients' => ['vegetables', 'meat', 'dairy', 'grains', 'spices'],
            'beverages' => ['alcoholic', 'non-alcoholic', 'coffee', 'tea'],
            'supplies' => ['kitchen', 'cleaning', 'packaging', 'utensils'],
            'prepared' => ['sauces', 'dressings', 'desserts', 'bakery']
        ];
        
        $category = $this->faker->randomElement(array_keys($categories));
        $subcategory = $this->faker->randomElement($categories[$category]);
        
        // Generate realistic item names based on category
        $itemName = $this->generateItemName($category, $subcategory);
        
        // Pricing with realistic restaurant margins
        $costPrice = $this->faker->randomFloat(2, 0.50, 50.00);
        $markupPercentage = $this->faker->numberBetween(30, 150); // 30-150% markup
        $sellingPrice = $costPrice * (1 + $markupPercentage / 100);
        
        return [
            // Core identification
            'name' => $itemName,
            'unicode_name' => $itemName,
            'description' => $this->generateDescription($category, $subcategory, $itemName),
            
            // Organization relationships
            'organization_id' => Organization::factory(),
            'branch_id' => function (array $attributes) {
                return Branch::where('organization_id', $attributes['organization_id'])->first()?->id;
            },
            
            // Category relationships
            'item_category_id' => function () {
                return ItemCategory::factory()->create()->id;
            },
            
            // Categorization
            'category' => $category,
            'subcategory' => $subcategory,
            'item_type' => $this->faker->randomElement(['inventory', 'consumable', 'equipment']),
            
            // Item identification
            'item_code' => null, // Will be auto-generated
            'barcode' => $this->faker->optional(70)->ean13(),
            'sku' => strtoupper($category[0] . $subcategory[0] . $this->faker->numberBetween(1000, 9999)),
            
            // Units and measurements
            'unit_of_measurement' => $this->getUnitOfMeasurement($category, $subcategory),
            
            // Pricing (PostgreSQL DECIMAL precision)
            'cost_price' => $costPrice,
            'buying_price' => $costPrice * 0.95, // 5% discount from suppliers
            'selling_price' => round($sellingPrice, 2),
            'markup_percentage' => $markupPercentage,
            
            // Inventory management
            'current_stock' => $this->faker->numberBetween(0, 100),
            'minimum_stock' => $this->faker->numberBetween(5, 20),
            'maximum_stock' => $this->faker->numberBetween(50, 200),
            'reorder_level' => $this->faker->numberBetween(10, 30),
            
            // Item specifications
            'brand' => $this->faker->optional(60)->company(),
            'model' => $this->faker->optional(30)->bothify('Model-##??'),
            'specifications' => $this->faker->optional(40)->sentence(),
            
            // Status flags (optimized for Tailwind CSS filtering)
            'is_active' => $this->faker->boolean(95), // Most items active
            'is_menu_item' => $this->faker->boolean(30), // Some items used in menu
            'is_inventory_item' => true,
            'is_perishable' => $this->isPerishable($category, $subcategory),
            'track_expiry' => function (array $attributes) {
                return $attributes['is_perishable'];
            },
            'shelf_life_in_days' => function (array $attributes) {
                return $attributes['is_perishable'] ? $this->faker->numberBetween(1, 30) : null;
            },
            
            // Supplier relationships
            'primary_supplier_id' => function () {
                return Supplier::factory()->create()->id;
            },
            'supplier_ids' => function () {
                $supplierCount = $this->faker->numberBetween(1, 3);
                $suppliers = Supplier::factory()->count($supplierCount)->create();
                return $suppliers->pluck('id')->toArray();
            },
            
            // Storage information
            'storage_location' => $this->getStorageLocation($category),
            'storage_requirements' => $this->getStorageRequirements($category, $subcategory),
            
            // Additional notes
            'additional_notes' => $this->faker->optional(30)->sentence(),
            
            // PostgreSQL JSON for Tailwind CSS UI
            'attributes' => [
                'color' => $this->getCategoryColor($category),
                'icon' => $this->getCategoryIcon($category),
                'tags' => $this->generateTags($category, $subcategory),
            ],
            
            'metadata' => [
                'ui_color' => $this->getCategoryColor($category),
                'display_priority' => $this->faker->numberBetween(1, 10),
                'nutritional_info' => $this->faker->optional(40)->randomElement([
                    ['calories' => $this->faker->numberBetween(10, 500)],
                    ['protein' => $this->faker->numberBetween(1, 50) . 'g'],
                    ['carbs' => $this->faker->numberBetween(1, 100) . 'g']
                ])
            ],
            
            // Audit fields
            'created_by' => null, // Will be set by seeder
            'updated_by' => null,
        ];
    }

    /**
     * Generate realistic item names based on category
     */
    private function generateItemName(string $category, string $subcategory): string
    {
        $names = [
            'ingredients' => [
                'vegetables' => ['Fresh Tomatoes', 'Organic Lettuce', 'Bell Peppers', 'Onions', 'Carrots'],
                'meat' => ['Chicken Breast', 'Ground Beef', 'Salmon Fillet', 'Pork Tenderloin', 'Turkey'],
                'dairy' => ['Whole Milk', 'Cheddar Cheese', 'Butter', 'Heavy Cream', 'Greek Yogurt'],
                'grains' => ['Basmati Rice', 'Whole Wheat Flour', 'Quinoa', 'Oats', 'Pasta'],
                'spices' => ['Black Pepper', 'Sea Salt', 'Oregano', 'Basil', 'Paprika']
            ],
            'beverages' => [
                'non-alcoholic' => ['Coca Cola', 'Orange Juice', 'Sparkling Water', 'Iced Tea', 'Coffee Beans'],
                'alcoholic' => ['House Wine', 'Craft Beer', 'Vodka', 'Whiskey', 'Rum']
            ],
            'supplies' => [
                'kitchen' => ['Chef Knife', 'Cutting Board', 'Mixing Bowl', 'Tongs', 'Spatula'],
                'cleaning' => ['Dish Soap', 'Sanitizer', 'Paper Towels', 'Cleaning Cloth', 'Bleach'],
                'packaging' => ['Take-out Containers', 'Paper Bags', 'Aluminum Foil', 'Plastic Wrap', 'Napkins']
            ]
        ];

        $categoryItems = $names[$category][$subcategory] ?? ['Generic Item'];
        return $this->faker->randomElement($categoryItems);
    }

    /**
     * Generate descriptions based on category and item
     */
    private function generateDescription(string $category, string $subcategory, string $itemName): string
    {
        $templates = [
            'ingredients' => "Premium quality {$itemName} sourced from trusted suppliers for our restaurant kitchen",
            'beverages' => "Refreshing {$itemName} perfect for enhancing our customers' dining experience",
            'supplies' => "Essential {$itemName} for efficient restaurant operations and food service",
            'prepared' => "House-made {$itemName} prepared fresh daily using traditional recipes"
        ];

        return $templates[$category] ?? "High-quality {$itemName} for restaurant use";
    }

    /**
     * Generate short descriptions for Tailwind CSS cards
     */
    private function generateShortDescription(string $category, string $itemName): string
    {
        return match($category) {
            'ingredients' => "Fresh {$itemName}",
            'beverages' => "Premium {$itemName}",
            'supplies' => "Kitchen {$itemName}",
            'prepared' => "House-made {$itemName}",
            default => $itemName
        };
    }

    /**
     * Get appropriate unit of measurement
     */
    private function getUnitOfMeasurement(string $category, string $subcategory): string
    {
        return match($category) {
            'ingredients' => match($subcategory) {
                'vegetables', 'meat' => $this->faker->randomElement(['kg', 'lbs', 'piece']),
                'dairy' => $this->faker->randomElement(['liter', 'gallon', 'kg']),
                'grains' => $this->faker->randomElement(['kg', 'lbs', 'bag']),
                'spices' => $this->faker->randomElement(['gram', 'oz', 'jar']),
                default => 'piece'
            },
            'beverages' => $this->faker->randomElement(['liter', 'bottle', 'case', 'gallon']),
            'supplies' => 'piece',
            default => 'piece'
        };
    }

    /**
     * Determine if item is perishable
     */
    private function isPerishable(string $category, string $subcategory): bool
    {
        return match($category) {
            'ingredients' => match($subcategory) {
                'vegetables', 'meat', 'dairy' => true,
                default => false
            },
            'beverages' => $subcategory === 'non-alcoholic',
            default => false
        };
    }

    /**
     * Get storage location based on category
     */
    private function getStorageLocation(string $category): string
    {
        return match($category) {
            'ingredients' => $this->faker->randomElement(['Walk-in Cooler', 'Freezer', 'Dry Storage', 'Pantry']),
            'beverages' => $this->faker->randomElement(['Beverage Cooler', 'Wine Cellar', 'Bar Storage']),
            'supplies' => $this->faker->randomElement(['Supply Room', 'Kitchen Storage', 'Cleaning Closet']),
            default => 'General Storage'
        };
    }

    /**
     * Get storage requirements
     */
    private function getStorageRequirements(string $category, string $subcategory): ?string
    {
        return match($category) {
            'ingredients' => match($subcategory) {
                'vegetables' => 'Keep refrigerated at 35-40°F',
                'meat' => 'Keep frozen or refrigerated below 40°F',
                'dairy' => 'Refrigerate at 35-40°F',
                'spices' => 'Store in cool, dry place',
                default => 'Store in dry, cool place'
            },
            'beverages' => 'Store in cool, dry place away from direct sunlight',
            default => null
        };
    }

    /**
     * Get Tailwind CSS color for category
     */
    private function getCategoryColor(string $category): string
    {
        return match($category) {
            'ingredients' => 'green',
            'beverages' => 'blue',
            'supplies' => 'gray',
            'prepared' => 'yellow',
            default => 'indigo'
        };
    }

    /**
     * Get icon for category (for Tailwind CSS UI)
     */
    private function getCategoryIcon(string $category): string
    {
        return match($category) {
            'ingredients' => 'utensils',
            'beverages' => 'coffee',
            'supplies' => 'cog',
            'prepared' => 'chef-hat',
            default => 'package'
        };
    }

    /**
     * Generate tags for filtering (Tailwind CSS components)
     */
    private function generateTags(string $category, string $subcategory): array
    {
        $baseTags = [$category, $subcategory];
        
        $additionalTags = [
            'premium', 'fresh', 'organic', 'local', 'imported', 'seasonal'
        ];
        
        $selectedTags = $this->faker->randomElements($additionalTags, $this->faker->numberBetween(0, 2));
        
        return array_merge($baseTags, $selectedTags);
    }

    /**
     * State for menu items
     */
    public function menuItem(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_menu_item' => true,
            'category' => 'prepared',
            'subcategory' => 'menu-ingredient',
        ]);
    }

    /**
     * State for perishable items
     */
    public function perishable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_perishable' => true,
            'track_expiry' => true,
            'shelf_life_days' => $this->faker->numberBetween(3, 14),
        ]);
    }

    /**
     * State for low stock items
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => $this->faker->numberBetween(0, 5),
            'minimum_stock' => 10,
        ]);
    }
}
