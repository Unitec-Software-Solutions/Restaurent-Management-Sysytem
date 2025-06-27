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
            ['email' => 'superadmin@rms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_super_admin' => true,
                'organization_id' => null,
                'branch_id' => null,
            ]
        );
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'organization_id' => null,
            'guard_name' => 'web',
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 2. Organization Admins (one per org)
        foreach (Organization::all() as $org) {
            $orgAdmin = User::firstOrCreate(
                ['email' => "admin_{$org->id}@example.com"],
                [
                    'name' => "Org Admin {$org->name}",
                    'password' => Hash::make('password'),
                    'is_admin' => true,
                    'is_super_admin' => false,
                    'organization_id' => $org->id,
                    'branch_id' => null,
                ]
            );
            $orgAdminRole = Role::firstOrCreate([
                'name' => 'Admin',
                'organization_id' => $org->id,
                'guard_name' => 'web',
            ]);
            $orgAdmin->assignRole($orgAdminRole);

            // 3. Branch Admins and Staff (one per branch)
            foreach ($org->branches as $branch) {
                $branchAdmin = User::firstOrCreate(
                    ['email' => "branchadmin_{$branch->id}@example.com"],
                    [
                        'name' => "Branch Admin {$branch->name}",
                        'password' => Hash::make('password'),
                        'is_admin' => true,
                        'is_super_admin' => false,
                        'organization_id' => $org->id,
                        'branch_id' => $branch->id,
                    ]
                );
                $branchAdminRole = Role::firstOrCreate([
                    'name' => 'Admin',
                    'organization_id' => $org->id,
                    'guard_name' => 'web',
                ]);
                $branchAdmin->assignRole($branchAdminRole);

                // Staff
                $staff = User::firstOrCreate(
                    ['email' => "staff_{$branch->id}@example.com"],
                    [
                        'name' => "Staff {$branch->name}",
                        'password' => Hash::make('password'),
                        'is_admin' => false,
                        'is_super_admin' => false,
                        'organization_id' => $org->id,
                        'branch_id' => $branch->id,
                    ]
                );
                $staffRole = Role::firstOrCreate([
                    'name' => 'Staff',
                    'organization_id' => $org->id,
                    'guard_name' => 'web',
                ]);
                $staff->assignRole($staffRole);
            }

            // 4. Regular User (per organization, not admin)
            $user = User::firstOrCreate(
                ['email' => "user_{$org->id}@example.com"],
                [
                    'name' => "Regular User {$org->name}",
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'is_super_admin' => false,
                    'organization_id' => $org->id,
                    'branch_id' => null,
                ]
            );
        }

        $this->command->info('  ✅ User types seeded: Super Admin, Org Admin, Branch Admin, Staff, Regular User.');
    }
}
