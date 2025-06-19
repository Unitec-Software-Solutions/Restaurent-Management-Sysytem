<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerProfileFactory extends Factory
{
    protected $model = CustomerProfile::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'profile_picture' => $this->faker->optional()->imageUrl(400, 400, 'people'),
            'is_active' => $this->faker->boolean(90),
            'notes' => $this->faker->optional()->sentence(),
            'loyalty_points' => $this->faker->numberBetween(0, 5000),
            'total_spent' => $this->faker->randomFloat(2, 0, 10000),
            'visit_count' => $this->faker->numberBetween(0, 100),
            'last_visit_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function vip()
    {
        return $this->state(function (array $attributes) {
            return [
                'loyalty_points' => $this->faker->numberBetween(1000, 10000),
                'total_spent' => $this->faker->randomFloat(2, 1000, 50000),
                'visit_count' => $this->faker->numberBetween(20, 200),
            ];
        });
    }

    public function recentlyActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_visit_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }
}
