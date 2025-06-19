<?php

namespace Database\Factories;

use App\Models\ItemCategory;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemCategoryFactory extends Factory
{
    protected $model = ItemCategory::class;

    public function definition()
    {        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->word(),
            'code' => $this->faker->bothify('CAT-####'),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
