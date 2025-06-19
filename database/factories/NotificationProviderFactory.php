<?php

namespace Database\Factories;

use App\Models\NotificationProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationProviderFactory extends Factory
{
    protected $model = NotificationProvider::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['email','sms','push']),
            'credentials' => ['key' => $this->faker->sha256],
            'is_active' => $this->faker->boolean(),
        ];
    }
}
