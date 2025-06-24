<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state following UI/UX guidelines.
     */
    public function definition(): array
    {
        $preparationTime = fake()->numberBetween(5, 45);
        $basePrice = fake()->numberBetween(200, 2000);
        $isOnPromotion = fake()->boolean(20); // 20% chance of promotion
        
        return [
            'menu_category_id' => MenuCategory::factory(),
            'organization_id' => Organization::factory(),
            'branch_id' => Branch::factory(),
            'item_master_id' => ItemMaster::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => $basePrice,
            'promotion_price' => $isOnPromotion ? $basePrice * 0.8 : null,
            'promotion_start' => $isOnPromotion ? now()->subDays(5) : null,
            'promotion_end' => $isOnPromotion ? now()->addDays(10) : null,
            'image_path' => 'menu-items/' . fake()->uuid() . '.jpg',
            'display_order' => fake()->numberBetween(1, 100),
            'is_available' => fake()->boolean(90),
            'is_featured' => fake()->boolean(15),
            'requires_preparation' => fake()->boolean(85),
            'preparation_time' => $preparationTime,
            'station' => fake()->randomElement(['kitchen', 'bar']),
            'is_vegetarian' => fake()->boolean(30),
            'is_spicy' => fake()->boolean(25),
            'contains_alcohol' => fake()->boolean(10),
            'allergens' => fake()->randomElements(['nuts', 'dairy', 'gluten', 'shellfish', 'eggs'], fake()->numberBetween(0, 3)),
            'calories' => fake()->numberBetween(150, 800),
            'ingredients' => fake()->words(8, true),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the menu item is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'display_order' => fake()->numberBetween(1, 10), // Featured items appear first
        ]);
    }

    /**
     * Indicate that the menu item is on promotion.
     */
    public function onPromotion(): static
    {
        return $this->state(function (array $attributes) {
            $originalPrice = $attributes['price'] ?? 1000;
            $discountPercent = fake()->numberBetween(10, 50);
            $promotionPrice = $originalPrice * (1 - $discountPercent / 100);
            
            return [
                'promotion_price' => round($promotionPrice, 2),
                'promotion_start' => now()->subDays(fake()->numberBetween(1, 10)),
                'promotion_end' => now()->addDays(fake()->numberBetween(5, 30)),
                'is_featured' => fake()->boolean(70), // Promoted items likely to be featured
            ];
        });
    }

    /**
     * Indicate that the menu item is vegetarian.
     */
    public function vegetarian(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vegetarian' => true,
            'contains_alcohol' => false,
            'allergens' => fake()->randomElements(['nuts', 'dairy', 'gluten'], fake()->numberBetween(0, 2)),
            'calories' => fake()->numberBetween(150, 600), // Generally lower calories
        ]);
    }

    /**
     * Indicate that the menu item is a beverage.
     */
    public function beverage(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_preparation' => fake()->boolean(30),
            'preparation_time' => fake()->numberBetween(2, 10),
            'station' => 'bar',
            'is_spicy' => false,
            'calories' => fake()->numberBetween(50, 300),
            'ingredients' => fake()->words(3, true),
        ]);
    }

    /**
     * Indicate that the menu item is spicy.
     */
    public function spicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_spicy' => true,
            'preparation_time' => fake()->numberBetween(15, 35), // Spicy dishes often take longer
        ]);
    }

    /**
     * Indicate that the menu item is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
            'is_featured' => false, // Unavailable items shouldn't be featured
        ]);
    }

    /**
     * Indicate that the menu item contains alcohol.
     */
    public function alcoholic(): static
    {
        return $this->state(fn (array $attributes) => [
            'contains_alcohol' => true,
            'is_vegetarian' => fake()->boolean(50),
            'station' => 'bar',
            'requires_preparation' => fake()->boolean(60),
            'preparation_time' => fake()->numberBetween(3, 15),
        ]);
    }
}
