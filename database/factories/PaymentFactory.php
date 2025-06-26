<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;    public function definition()
    {
        return [
            'payable_type' => $this->faker->randomElement(['App\\Models\\Order','App\\Models\\Reservation']),
            'payable_id' => $this->faker->randomNumber(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'payment_method' => $this->faker->randomElement(['cash','card','bank']),
            'status' => $this->faker->randomElement(['pending','completed','failed']),
            'payment_reference' => $this->faker->uuid(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
