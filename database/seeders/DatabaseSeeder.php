<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database
     */
    public function run(): void
    {
        // Create Super Admin if not exists
        $superAdminEmail = 'superadmin@rms.com';
        $superAdmin = Admin::firstOrCreate([
            'email' => $superAdminEmail
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('SuperAdmin123!'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        // Seed all system permissions for admin guard
        $this->call(SystemPermissionsSeeder::class);

        // Seed all modules
        $this->call(ModuleSeeder::class);

        // Assign the super_admin role if it exists
        if (method_exists($superAdmin, 'assignRole')) {
            try {
                $superAdmin->assignRole('super_admin');
            } catch (RoleDoesNotExist $e) {

            }
        }
    }
}
