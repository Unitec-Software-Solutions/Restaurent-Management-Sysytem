<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {        return [
            'emp_id' => $this->faker->unique()->numerify('EMP###'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'role' => $this->faker->randomElement(['steward', 'manager', 'chef', 'waiter', 'cashier']),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'is_active' => $this->faker->boolean(),
            'joined_date' => $this->faker->dateTimeThisDecade(),
            'address' => $this->faker->address(),
            'emergency_contact' => $this->faker->phoneNumber(),
        ];
    }
}
