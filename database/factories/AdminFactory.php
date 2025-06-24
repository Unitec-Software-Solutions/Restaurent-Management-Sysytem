<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * Define the model's default state following UI/UX guidelines.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password for testing
            'organization_id' => Organization::factory(),
            'role' => fake()->randomElement(['admin', 'manager', 'staff']), // Legacy role field
            'department' => fake()->randomElement(['Operations', 'Kitchen', 'Service', 'Management', 'Finance']),
            'job_title' => fake()->randomElement(['Manager', 'Supervisor', 'Coordinator', 'Assistant Manager', 'Team Lead']),
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
            'remember_token' => Str::random(10),
            'branch_id' => null,
            'phone' => fake()->phoneNumber(),
            'ui_settings' => [
                'theme' => fake()->randomElement(['light', 'dark']),
                'sidebar_collapsed' => fake()->boolean(),
                'dashboard_layout' => fake()->randomElement(['grid', 'list', 'cards']),
                'notifications_enabled' => fake()->boolean(80),
                'preferred_language' => 'en',
                'cards_per_row' => fake()->numberBetween(3, 6),
                'show_help_tips' => fake()->boolean(70),
            ],
            'preferences' => [
                'timezone' => 'Asia/Colombo',
                'date_format' => fake()->randomElement(['Y-m-d', 'd/m/Y', 'm/d/Y']),
                'time_format' => fake()->randomElement(['24h', '12h']),
                'currency' => 'LKR',
            ],
        ];
    }

    /**
     * Indicate that the admin is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_super_admin' => true,
            'organization_id' => null,
            'branch_id' => null,
            'role' => 'super_admin',
            'department' => 'System Administration',
            'job_title' => 'Super Administrator',
            'status' => 'active',
            'ui_settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => false,
                'dashboard_layout' => 'grid',
                'notifications_enabled' => true,
                'preferred_language' => 'en',
                'show_all_organizations' => true,
                'show_system_metrics' => true,
            ],
        ]);
    }

    /**
     * Indicate that the admin is an organization admin.
     */
    public function organizationAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'department' => 'Management',
            'job_title' => 'Organization Administrator',
            'branch_id' => null,
            'ui_settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => false,
                'dashboard_layout' => 'cards',
                'show_organization_selector' => false,
            ],
        ]);
    }

    /**
     * Indicate that the admin is a branch admin.
     */
    public function branchAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'branch_admin',
            'department' => 'Branch Operations',
            'job_title' => 'Branch Administrator',
            'branch_id' => Branch::factory(),
            'ui_settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => true,
                'dashboard_layout' => 'compact',
                'show_branch_selector' => false,
            ],
        ]);
    }

    /**
     * Indicate that the admin is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the admin is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'locked_until' => now()->addDays(7),
        ]);
    }
}
