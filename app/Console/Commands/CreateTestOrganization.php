<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Support\Str;

class CreateTestOrganization extends Command
{
    protected $signature = 'setup:test-org';
    protected $description = 'Create a test organization for superadmin to create users';

    public function handle()
    {
        $this->info('🏢 Creating Test Organization...');
        
        // Create organization
        $organization = Organization::create([
            'name' => 'Test Restaurant Group',
            'email' => 'admin@testrestaurant.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main Street, Test City, TC 12345',
            'contact_person' => 'John Smith',
            'contact_person_designation' => 'General Manager',
            'contact_person_phone' => '+1-555-123-4567',
            'password' => bcrypt('TestPassword123!'),
            'business_type' => 'restaurant',
            'is_active' => true,
        ]);
        
        $this->info("✅ Organization created: {$organization->name}");
        
        // Create a branch for the organization
        $branch = Branch::create([
            'organization_id' => $organization->id,
            'name' => 'Main Branch',
            'email' => 'main@testrestaurant.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main Street, Test City, TC 12345',
            'contact_person' => 'Jane Doe',
            'contact_person_designation' => 'Branch Manager',
            'contact_person_phone' => '+1-555-123-4567',
            'opening_time' => '08:00:00',
            'closing_time' => '22:00:00',
            'total_capacity' => 50,
            'reservation_fee' => 10.00,
            'cancellation_fee' => 5.00,
            'is_active' => true,
        ]);
        
        $this->info("✅ Branch created: {$branch->name}");
        
        // Create some basic roles for the organization
        $roles = [
            ['name' => 'Organization Admin', 'guard_name' => 'admin', 'organization_id' => $organization->id],
            ['name' => 'Branch Manager', 'guard_name' => 'admin', 'organization_id' => $organization->id, 'branch_id' => $branch->id],
            ['name' => 'Staff Member', 'guard_name' => 'web', 'organization_id' => $organization->id],
            ['name' => 'Customer', 'guard_name' => 'web', 'organization_id' => $organization->id],
        ];
        
        foreach ($roles as $roleData) {
            $role = Role::create($roleData);
            $this->info("✅ Role created: {$role->name}");
        }
        
        $this->line('');
        $this->info('🎉 Test Setup Complete!');
        $this->info('======================');
        $this->info('Super admin can now:');
        $this->info('1. Log in with: superadmin@rms.com / SuperAdmin123!');
        $this->info('2. Create users and assign them to the test organization');
        $this->info('3. Assign roles to users');
        $this->info('4. Manage the system');
        
        return 0;
    }
}
