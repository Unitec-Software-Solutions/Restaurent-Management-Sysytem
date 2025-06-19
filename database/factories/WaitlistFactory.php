<?php

namespace Database\Factories;

use App\Models\Waitlist;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitlistFactory extends Factory
{
    protected $model = Waitlist::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'preferred_time' => $this->faker->dateTimeBetween('now', '+1 month'),
            'number_of_people' => $this->faker->numberBetween(1, 12),
            'comments' => $this->faker->optional()->sentence(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['waiting', 'contacted', 'seated', 'cancelled', 'expired']),
            'notify_when_available' => $this->faker->boolean(70),
        ];
    }

    public function waiting()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'waiting',
            ];
        });
    }

    public function contacted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'contacted',
            ];
        });
    }

    public function seated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'seated',
            ];
        });
    }

    public function withNotifications()
    {
        return $this->state(function (array $attributes) {
            return [
                'notify_when_available' => true,
            ];
        });
    }
}
