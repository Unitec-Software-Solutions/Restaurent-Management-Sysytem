<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class TestOrgAdminSidebar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:org-admin-sidebar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test organizational admin sidebar functionality with all menu items visible';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 Testing Organizational Admin Sidebar Functionality');
        $this->newLine();

        // Create test organization if not exists
        $organization = Organization::first();
        
        if (!$organization) {
            $organization = Organization::create([
                'name' => 'Test Restaurant Organization',
                'slug' => 'test-restaurant-org',
                'email' => 'contact@testrestaurant.com',
                'password' => Hash::make('password123'), // Add password field
                'phone' => '+1234567890',
                'address' => '123 Test Street, Test City',
                'is_active' => true,
                'subscription_plan_id' => 1, // Assuming basic plan exists
            ]);
        }

        $this->info("✅ Organization: {$organization->name} (ID: {$organization->id})");

        // Use existing branch or skip branch creation
        $branch = Branch::where('organization_id', $organization->id)->first();
        
        if (!$branch) {
            $this->warn('No existing branch found, creating admins without branch assignment.');
            $branch = null;
        } else {
            $this->info("✅ Branch: {$branch->name} (ID: {$branch->id})");
        }

        // Create/update organizational admin
        $orgAdmin = Admin::updateOrCreate([
            'email' => 'org.admin@testrestaurant.com',
        ], [
            'name' => 'Test Org Admin',
            'password' => Hash::make('OrgAdmin123!'),
            'is_super_admin' => false,
            'organization_id' => $organization->id,
            'branch_id' => null, // Org admin has no specific branch
            'is_active' => true,
        ]);

        $this->info("✅ Organizational Admin: {$orgAdmin->name} ({$orgAdmin->email})");

        // Create/update branch admin (only if branch exists)
        if ($branch) {
            $branchAdmin = Admin::updateOrCreate([
                'email' => 'branch.admin@testrestaurant.com',
            ], [
                'name' => 'Test Branch Admin',
                'password' => Hash::make('BranchAdmin123!'),
                'is_super_admin' => false,
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);

            $this->info("✅ Branch Admin: {$branchAdmin->name} ({$branchAdmin->email})");
        } else {
            $this->warn('⚠️  Branch admin not created - no branch available');
        }

        // Show login credentials
        $this->newLine();
        $this->info('🔑 Test Login Credentials:');
        $this->table(
            ['Type', 'Email', 'Password', 'Access Level'],
            [
                ['Super Admin', 'superadmin@rms.com', 'SuperAdmin123!', 'Full Access'],
                ['Org Admin', 'org.admin@testrestaurant.com', 'OrgAdmin123!', 'Organization Level'],
                ['Branch Admin', 'branch.admin@testrestaurant.com', 'BranchAdmin123!', 'Branch Level'],
            ]
        );

        $this->newLine();
        $this->info('🎯 Test Scenarios:');
        $this->line('1. Login as Organizational Admin - should see ALL menu items');
        $this->line('   • Permitted items: clickable and functional');
        $this->line('   • Restricted items: visible but grayed out with lock icon');
        $this->line('   • Clicking restricted items shows permission notice');
        $this->newLine();
        $this->line('2. Login as Branch Admin - should see ALL menu items');
        $this->line('   • Same behavior as org admin but scoped to branch level');
        $this->newLine();
        $this->line('3. Access restricted functions directly via URL');
        $this->line('   • Should show permission-denied page instead of redirecting to login');

        $this->newLine();
        $this->info('🌐 Test URLs:');
        $this->line('• Login: http://127.0.0.1:8000/admin/login');
        $this->line('• User Login: http://127.0.0.1:8000/user/login');
        $this->line('• Admin Dashboard: http://127.0.0.1:8000/admin/dashboard');

        return Command::SUCCESS;
    }
}
