<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin (global, not tied to org/branch)
        $superAdmin = User::firstOrCreate(
            ['email' => 'sampleuser@rms.com'],
            [
                'name' => 'Sample User',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_super_admin' => true,
                'organization_id' => null,
                'branch_id' => null,
            ]
        );
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Sample User',
            'organization_id' => null,
            'guard_name' => 'web',
        ]);
        $superAdmin->assignRole($superAdminRole);

    }
}