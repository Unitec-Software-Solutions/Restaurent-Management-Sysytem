<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
        ];
    }
}
