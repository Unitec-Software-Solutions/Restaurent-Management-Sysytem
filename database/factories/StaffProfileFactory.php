<?php

namespace Database\Factories;

use App\Models\StaffProfile;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    public function definition()
    {
        return [
            'employee_id' => $this->faker->unique()->numerify('EMP####'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['Kitchen', 'Service', 'Management', 'Cleaning', 'Security']),
            'salary' => $this->faker->randomFloat(2, 20000, 80000),
            'hourly_rate' => $this->faker->randomFloat(2, 10, 50),
            'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'intern']),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'manager_id' => null, // Will be set later if needed
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'is_active' => $this->faker->boolean(90),
            'termination_date' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'termination_date' => null,
            ];
        });
    }

    public function terminated()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'termination_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function manager()
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => $this->faker->randomElement(['Manager', 'Team Lead', 'Supervisor']),
                'department' => $this->faker->randomElement(['Management', 'Kitchen', 'Service']),
            ];
        });
    }

    public function fullTime()
    {
        return $this->state(function (array $attributes) {
            return [
                'employment_type' => 'full_time',
                'salary' => $this->faker->randomFloat(2, 30000, 80000),
            ];
        });
    }

    public function partTime()
    {
        return $this->state(function (array $attributes) {
            return [
                'employment_type' => 'part_time',
                'hourly_rate' => $this->faker->randomFloat(2, 15, 35),
            ];
        });
    }
}
