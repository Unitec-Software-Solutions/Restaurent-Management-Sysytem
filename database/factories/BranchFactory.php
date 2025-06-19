<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'opening_time' => $this->faker->time(),
            'closing_time' => $this->faker->time(),
            'total_capacity' => $this->faker->numberBetween(20, 200),
            'reservation_fee' => $this->faker->randomFloat(2, 0, 100),
            'cancellation_fee' => $this->faker->randomFloat(2, 0, 100),
            'contact_person' => $this->faker->name(),
            'contact_person_designation' => $this->faker->jobTitle(),
            'contact_person_phone' => $this->faker->phoneNumber(),
            'is_active' => $this->faker->boolean(),
            'activation_key' => $this->faker->uuid(),
            'activated_at' => $this->faker->optional()->dateTime(),
            'organization_id' => Organization::factory(),
        ];
    }
}
