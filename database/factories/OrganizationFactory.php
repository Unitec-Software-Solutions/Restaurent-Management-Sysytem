<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'contact_person_designation' => $this->faker->jobTitle(),
            'contact_person_phone' => $this->faker->phoneNumber(),
            'is_active' => $this->faker->boolean(),
            'subscription_plan_id' => $this->faker->randomNumber(),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
