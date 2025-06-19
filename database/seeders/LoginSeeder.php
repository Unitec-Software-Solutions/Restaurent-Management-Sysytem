<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = \App\Models\Organization::first();
        $role = \App\Models\Role::first();

        // Super Admin: always seed, even if no org/role
        User::updateOrCreate(
            ['id' => 2, 'email' => 'superadmin@rms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_registered' => true,
                'role_id' => $role ? $role->id : null,
                'organization_id' => $organization ? $organization->id : null,
                'is_admin' => true,
                'created_by' => null,
                'is_super_admin' => true,
            ]
        );

        $this->command->info('  ✅ Super Admin user seeded successfully.');

        // Optionally, add a warning if no org/role
        if (!$organization) {
            $this->command->warn('⚠️ No organizations found. Super Admin seeded with organization_id = null.');
        }
        if (!$role) {
            $this->command->warn('⚠️ No roles found. Super Admin seeded with role_id = null.');
        }
    }
}