<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'test:create-user {name} {email} {--password=TestPassword123!}';
    protected $description = 'Create a test user to demonstrate superadmin functionality';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password');
        
        $this->info('👤 Creating Test User...');
        
        // Get the first organization
        $organization = Organization::first();
        if (!$organization) {
            $this->error('❌ No organization found. Please create an organization first.');
            return 1;
        }
        
        // Get a role for the user
        $role = Role::where('guard_name', 'web')->first();
        if (!$role) {
            // Create a basic role for users
            $role = Role::create([
                'name' => 'Customer',
                'guard_name' => 'web',
                'organization_id' => $organization->id
            ]);
            $this->info("✅ Created role: {$role->name}");
        }
        
        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'organization_id' => $organization->id,
            'role_id' => $role->id,
            'is_registered' => true,
            'is_admin' => false,
            'is_super_admin' => false,
            'created_by' => Admin::where('is_super_admin', true)->first()->id
        ]);
        
        // Assign the role
        $user->assignRole($role);
        
        $this->info("✅ User created successfully!");
        $this->info("   - Name: {$user->name}");
        $this->info("   - Email: {$user->email}");
        $this->info("   - Password: {$password}");
        $this->info("   - Organization: {$organization->name}");
        $this->info("   - Role: {$role->name}");
        $this->info("   - Created by: Super Admin");
        
        $this->line('');
        $this->info('🎉 User can now log in to the system!');
        
        return 0;
    }
}
