<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class CreateOrgAdmin extends Command
{
    protected $signature = 'test:create-org-admin {name} {email} {--password=AdminPassword123!}';
    protected $description = 'Create a test organizational admin';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password');

        // Get the first organization
        $org = Organization::first();
        if (!$org) {
            $this->error('No organization found! Please create an organization first.');
            return 1;
        }

        // Check if admin already exists
        if (Admin::where('email', $email)->exists()) {
            $this->error("Admin with email '{$email}' already exists!");
            return 1;
        }

        // Create the admin
        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'organization_id' => $org->id,
            'is_super_admin' => false,
            'is_active' => true
        ]);

        $this->info("✅ Created organizational admin successfully!");
        $this->table(['Field', 'Value'], [
            ['Name', $admin->name],
            ['Email', $admin->email],
            ['Password', $password],
            ['Organization', $org->name],
            ['Organization ID', $org->id],
            ['Is Super Admin', 'No'],
            ['Is Active', 'Yes']
        ]);

        $this->line("🎉 Admin can now log in through both:");
        $this->line("   • User portal: http://localhost:8000/user/login");
        $this->line("   • Admin portal: http://localhost:8000/admin/login");

        return 0;
    }
}
