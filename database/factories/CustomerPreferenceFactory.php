<?php

namespace Database\Factories;

use App\Models\CustomerPreference;
use App\Models\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPreferenceFactory extends Factory
{
    protected $model = CustomerPreference::class;

    public function definition()
    {
        return [
            'customer_profile_id' => CustomerProfile::factory(),
            'dietary_restrictions' => $this->faker->randomElements([
                'vegetarian', 'vegan', 'gluten-free', 'dairy-free', 'nut-free', 
                'kosher', 'halal', 'low-sodium', 'diabetic-friendly'
            ], $this->faker->numberBetween(0, 3)),
            'favorite_dishes' => $this->faker->randomElements([
                'pizza', 'pasta', 'burger', 'salad', 'steak', 'chicken', 
                'seafood', 'soup', 'sandwich', 'dessert'
            ], $this->faker->numberBetween(1, 5)),
            'allergies' => $this->faker->randomElements([
                'peanuts', 'tree nuts', 'dairy', 'eggs', 'fish', 'shellfish', 
                'soy', 'wheat', 'sesame', 'sulfites'
            ], $this->faker->numberBetween(0, 2)),
            'preferred_language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it']),
            'email_notifications' => $this->faker->boolean(70),
            'sms_notifications' => $this->faker->boolean(50),
        ];
    }

    public function withEmailNotifications()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_notifications' => true,
            ];
        });
    }

    public function withSmsNotifications()
    {
        return $this->state(function (array $attributes) {
            return [
                'sms_notifications' => true,
            ];
        });
    }

    public function vegetarian()
    {
        return $this->state(function (array $attributes) {
            return [
                'dietary_restrictions' => ['vegetarian'],
                'favorite_dishes' => ['salad', 'pasta', 'pizza'],
            ];
        });
    }

    public function vegan()
    {
        return $this->state(function (array $attributes) {
            return [
                'dietary_restrictions' => ['vegan', 'dairy-free'],
                'favorite_dishes' => ['salad', 'soup'],
            ];
        });
    }
}
