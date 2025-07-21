<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

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
        $this->call(\Database\Seeders\SystemPermissionsSeeder::class);

        // Assign the super_admin role if it exists
        if (method_exists($superAdmin, 'assignRole')) {
            try {
                $superAdmin->assignRole('super_admin');
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                // Role does not exist yet, skip assignment
            }
        }
    }
}
