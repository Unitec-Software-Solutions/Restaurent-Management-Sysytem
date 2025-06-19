<?php

namespace Database\Factories;

use App\Models\CustomerPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPreferenceFactory extends Factory
{
    protected $model = CustomerPreference::class;

    public function definition()
    {
        return [
            'customer_profile_id' => $this->faker->randomNumber(),
            'dietary_restrictions' => $this->faker->randomElements(['vegan','vegetarian','halal','kosher'], $this->faker->numberBetween(0,2)),
            'favorite_dishes' => $this->faker->randomElements(['pizza','burger','salad','pasta'], $this->faker->numberBetween(0,2)),
            'allergies' => $this->faker->randomElements(['nuts','gluten','dairy','soy','eggs'], $this->faker->numberBetween(0,2)),
            'preferred_language' => $this->faker->randomElement(['en','es','fr','de']),
            'email_notifications' => $this->faker->boolean(),
            'sms_notifications' => $this->faker->boolean(),
        ];
    }
}
