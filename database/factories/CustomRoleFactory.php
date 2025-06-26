<?php

namespace Database\Factories;

use App\Models\CustomRole;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomRoleFactory extends Factory
{
    protected $model = CustomRole::class;

    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
            'organization_id' => Organization::factory(),
            'branch_id' => Branch::factory(),
            'guard_name' => 'web',
        ];
    }
}
