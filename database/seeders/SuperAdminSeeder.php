<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@rms.com', 
            'user_type' => 'superadmin',
            'password' => Hash::make('password'),
        ]);
        $superAdminRole = \Spatie\Permission\Models\Role::create([
            'name' => 'Super Admin',
            'organization_id' => null,
            'branch_id' => null
        ]);
        $superAdmin->assignRole($superAdminRole);
        $permissions = \Spatie\Permission\Models\Permission::all();
        $superAdminRole->syncPermissions($permissions);
    }
}
