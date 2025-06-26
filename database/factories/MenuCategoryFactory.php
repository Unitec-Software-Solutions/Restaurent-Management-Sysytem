<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuCategoryFactory extends Factory
{
    protected $model = MenuCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_inactive' => $this->faker->boolean(),
            'display_order' => $this->faker->numberBetween(1, 20),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
