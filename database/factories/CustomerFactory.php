<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => '+1-555-' . sprintf('%04d', $this->faker->unique()->numberBetween(1000, 9999)),
            'name' => $this->faker->name,
            'email' => $this->faker->optional(0.8)->safeEmail, // 80% have email
            'preferred_contact' => $this->faker->randomElement(['email', 'sms']),
            'date_of_birth' => $this->faker->optional(0.6)->dateTimeBetween('-65 years', '-18 years'),
            'anniversary_date' => $this->faker->optional(0.3)->dateTimeBetween('-20 years', 'now'),
            'dietary_preferences' => $this->faker->optional(0.2)->randomElement(['vegetarian', 'vegan', 'gluten-free', 'dairy-free']),
            'special_notes' => $this->faker->optional(0.3)->sentence,
            'is_active' => true,
            'last_visit_date' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'total_orders' => $this->faker->numberBetween(0, 50),
            'total_spent' => $this->faker->randomFloat(2, 0, 2000),
            'loyalty_points' => $this->faker->numberBetween(0, 500),
        ];
    }
}
