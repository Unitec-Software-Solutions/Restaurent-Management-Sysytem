<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company() . ' Gateway',
            'provider' => $this->faker->randomElement(['stripe', 'paypal', 'square', 'braintree']),
            'credentials' => [
                'api_key' => $this->faker->sha256(),
                'secret_key' => $this->faker->sha256(),
                'webhook_secret' => $this->faker->sha256(),
            ],
            'is_active' => $this->faker->boolean(80),
            'is_test_mode' => $this->faker->boolean(60),
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

    public function testMode()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_test_mode' => true,
            ];
        });
    }

    public function live()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_test_mode' => false,
            ];
        });
    }
}
