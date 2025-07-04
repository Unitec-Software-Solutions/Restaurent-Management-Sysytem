<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $spiceLevels = ['mild', 'medium', 'hot', 'very_hot'];
        $stations = ['grill', 'fryer', 'salad', 'dessert', 'beverage', 'hot_kitchen', 'cold_kitchen'];

        return [
            'organization_id' => Organization::factory(),
            'branch_id' => Branch::factory(),
            'menu_category_id' => MenuCategory::factory(),
            'item_master_id' => $this->faker->optional()->randomElement([null]), // Always set, even if null
            'name' => $this->faker->words(2, true),
            'unicode_name' => $this->faker->optional()->words(2, true),
            'description' => $this->faker->sentence(),
            'item_code' => 'MI-' . $this->faker->unique()->numerify('####'),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'cost_price' => $this->faker->randomFloat(2, 2, 50),
            'currency' => 'LKR',
            'promotion_price' => $this->faker->optional(0.3)->randomFloat(2, 4, 80),
            'promotion_start' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'promotion_end' => $this->faker->optional()->dateTimeBetween('+1 month', '+2 months'),
            'image_path' => $this->faker->optional()->imageUrl(300, 300, 'food'),
            'image_url' => $this->faker->optional()->imageUrl(300, 300, 'food'),
            'display_order' => $this->faker->numberBetween(1, 100),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_available' => $this->faker->boolean(85),
            'is_active' => $this->faker->boolean(90),
            'is_featured' => $this->faker->boolean(20),
            'requires_preparation' => $this->faker->boolean(80),
            'preparation_time' => $this->faker->numberBetween(5, 45),
            'station' => $this->faker->randomElement($stations),
            'is_vegetarian' => $this->faker->boolean(40),
            'is_vegan' => $this->faker->boolean(20),
            'is_spicy' => $this->faker->boolean(30),
            'spice_level' => $this->faker->randomElement($spiceLevels),
            'contains_alcohol' => $this->faker->boolean(10),
            'calories' => $this->faker->optional()->numberBetween(100, 800),
            'allergens' => $this->faker->optional()->randomElements(['nuts', 'dairy', 'gluten', 'eggs', 'soy'], $this->faker->numberBetween(0, 3)),
            'allergen_info' => null,
            'nutritional_info' => null,
            'ingredients' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement([MenuItem::TYPE_BUY_SELL, MenuItem::TYPE_KOT]),
            'special_instructions' => $this->faker->optional()->sentence(),
            'customization_options' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the menu item is featured
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the menu item is vegetarian
     */
    public function vegetarian(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vegetarian' => true,
            'is_vegan' => false,
        ]);
    }

    /**
     * Indicate that the menu item is vegan
     */
    public function vegan(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vegetarian' => true,
            'is_vegan' => true,
        ]);
    }

    /**
     * Indicate that the menu item is spicy
     */
    public function spicy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_spicy' => true,
            'spice_level' => $this->faker->randomElement(['medium', 'hot', 'very_hot']),
        ]);
    }

    /**
     * Indicate that the menu item is on promotion
     */
    public function onPromotion(): static
    {
        return $this->state(function (array $attributes) {
            $originalPrice = $attributes['price'] ?? 50;
            return [
                'promotion_price' => $originalPrice * 0.8, // 20% discount
                'promotion_start' => now(),
                'promotion_end' => now()->addDays(30),
            ];
        });
    }

    /**
     * Indicate that the menu item is linked to item master
     */
    public function fromItemMaster(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_master_id' => ItemMaster::factory(),
            'type' => MenuItem::TYPE_BUY_SELL,
        ]);
    }
}
