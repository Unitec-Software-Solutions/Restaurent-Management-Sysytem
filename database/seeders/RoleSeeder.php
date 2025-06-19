<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Organization;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $olu = Organization::where('name', 'Olu cafe and restaurent')->first();
        $urban = Organization::where('name', 'Urban Cafe')->first();

        // Roles for Olu
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'organization_id' => null,
            'guard_name' => 'admin',
        ]);
        Role::firstOrCreate([
            'name' => 'Admin',
            'organization_id' => $olu->id, 
            'guard_name' => 'admin',
        ]);
        Role::firstOrCreate([
            'name' => 'Staff',
            'organization_id' => $olu->id,
            'guard_name' => 'admin',
        ]);

        // Roles for Urban
        Role::firstOrCreate([
            'name' => 'Admin',
            'organization_id' => $urban->id,
            'guard_name' => 'admin',
        ]);
        Role::firstOrCreate([
            'name' => 'Staff',
            'organization_id' => $urban->id,
            'guard_name' => 'admin',
        ]);
    }
}
