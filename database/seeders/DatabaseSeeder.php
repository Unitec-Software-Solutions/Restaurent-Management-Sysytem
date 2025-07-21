<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database
     */
    public function run(): void
    {
        // Create Super Admin if not exists
        $superAdminEmail = 'superadmin@system.local';
        $superAdmin = Admin::firstOrCreate([
            'email' => $superAdminEmail
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('superadmin123'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        // Seed all system permissions for admin guard
        $this->call(SystemPermissionsSeeder::class);

        if (method_exists($superAdmin, 'assignRole')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}
