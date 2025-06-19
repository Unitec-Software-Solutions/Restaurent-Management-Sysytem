<?php

namespace Database\Factories;

use App\Models\CustomerAuthenticationMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAuthenticationMethodFactory extends Factory
{
    protected $model = CustomerAuthenticationMethod::class;

    public function definition()
    {
        return [
            'customer_profile_id' => $this->faker->randomNumber(),
            'provider' => $this->faker->randomElement(['email','phone','google','facebook']),
            'provider_id' => $this->faker->uuid(),
            'email' => $this->faker->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'password' => bcrypt('password'),
            'is_verified' => $this->faker->boolean(),
            'email_verified_at' => $this->faker->optional()->dateTime(),
            'phone_verified_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
