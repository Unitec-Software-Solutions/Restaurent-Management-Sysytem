<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        return [
            'branch_id' => Branch::factory(),
            'number' => $this->faker->numberBetween(1, 100),
            'capacity' => $this->faker->numberBetween(2, 20),
            'status' => $this->faker->randomElement(['available','reserved','occupied','maintenance']),
            'location' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
