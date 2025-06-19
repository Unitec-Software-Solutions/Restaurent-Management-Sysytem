<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition()
    {
        return [
            'menu_category_id' => MenuCategory::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'image_path' => $this->faker->imageUrl(),
            'is_available' => $this->faker->boolean(),
            'requires_preparation' => $this->faker->boolean(),
            'preparation_time' => $this->faker->numberBetween(5, 60),
            'station' => $this->faker->word(),
            'is_vegetarian' => $this->faker->boolean(),
            'contains_alcohol' => $this->faker->boolean(),
            'allergens' => $this->faker->randomElements(['nuts','gluten','dairy','soy','eggs'], $this->faker->numberBetween(0,3)),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
