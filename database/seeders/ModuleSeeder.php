<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Seed the application's modules (for permissions, features, etc).
     *
     * @return void
     */
    public function run()
    {
        // Seed modules for the system
        $modules = [
            [
                'slug' => 'menu',
                'name' => 'Menu Management',
                'description' => 'Manage restaurant menus, categories, and items.',
                'is_active' => true,
            ],
            [
                'slug' => 'orders',
                'name' => 'Order Management',
                'description' => 'Handle dine-in, takeaway, and delivery orders.',
                'is_active' => true,
            ],
            [
                'slug' => 'inventory',
                'name' => 'Inventory Management',
                'description' => 'Track and manage inventory, suppliers, and stock.',
                'is_active' => true,
            ],
            [
                'slug' => 'reservations',
                'name' => 'Reservation Management',
                'description' => 'Manage table reservations and bookings.',
                'is_active' => true,
            ],
            [
                'slug' => 'staff',
                'name' => 'Staff Management',
                'description' => 'Manage staff, roles, and schedules.',
                'is_active' => true,
            ],
            [
                'slug' => 'reports',
                'name' => 'Reporting',
                'description' => 'View and export business reports.',
                'is_active' => true,
            ],
            [
                'slug' => 'settings',
                'name' => 'System Settings',
                'description' => 'Configure system-wide settings and integrations.',
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            \App\Models\Module::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
