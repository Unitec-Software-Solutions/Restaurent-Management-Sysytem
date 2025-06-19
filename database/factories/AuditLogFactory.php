<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        return [
            'action' => $this->faker->word(),
            'model_type' => $this->faker->word(),
            'model_id' => $this->faker->randomNumber(),
            'user_id' => User::factory(),
            'old_values' => [],
            'new_values' => [],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
