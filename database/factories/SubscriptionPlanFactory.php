<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word() . ' Plan',
            'modules' => $this->faker->randomElements(['orders','inventory','staff','analytics'], $this->faker->numberBetween(1,4)),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'currency' => $this->faker->randomElement(['USD','EUR','GBP']),
            'description' => $this->faker->sentence(),
            'is_trial' => $this->faker->boolean(),
            'trial_period_days' => $this->faker->numberBetween(0, 30),
        ];
    }
}
