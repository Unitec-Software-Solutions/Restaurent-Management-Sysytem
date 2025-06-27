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
        $this->command->info('👤 Seeding roles for existing organizations...');
        
        // Create global Super Admin role (no organization)
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'organization_id' => null,
            'guard_name' => 'admin',
        ]);
        
        $this->command->info('  ✅ Created global Super Admin role');

        // Get all existing organizations
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No organizations found. Run OrganizationSeeder first.');
            return;
        }

        // Create roles for each organization
        foreach ($organizations as $organization) {
            $this->createRolesForOrganization($organization);
        }
        
        $this->command->info('✅ Roles seeded successfully');
    }
    
    private function createRolesForOrganization(Organization $organization): void
    {
        $this->command->info("  🏢 Creating roles for: {$organization->name}");
        
        // Admin role for this organization
        Role::firstOrCreate([
            'name' => 'Admin',
            'organization_id' => $organization->id,
            'guard_name' => 'admin',
        ]);
        
        // Staff role for this organization
        Role::firstOrCreate([
            'name' => 'Staff',
            'organization_id' => $organization->id,
            'guard_name' => 'admin',
        ]);
        
        // Manager role for this organization
        Role::firstOrCreate([
            'name' => 'Manager',
            'organization_id' => $organization->id,
            'guard_name' => 'admin',
        ]);
        
        $this->command->info("    ✅ Created Admin, Staff, and Manager roles for {$organization->name}");
    }
}
