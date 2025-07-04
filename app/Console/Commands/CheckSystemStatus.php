<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class CheckSystemStatus extends Command
{
    protected $signature = 'system:check';
    protected $description = 'Check system status for superadmin functionality';

    public function handle()
    {
        $this->info('🔍 Restaurant Management System Status Check');
        $this->info('==========================================');
        
        // Check Super Admin
        $superAdmin = Admin::where('is_super_admin', true)->first();
        if ($superAdmin) {
            $this->info("✅ Super Admin exists: {$superAdmin->email}");
            $this->info("   - Name: {$superAdmin->name}");
            $this->info("   - Active: " . ($superAdmin->is_active ? 'Yes' : 'No'));
            $this->info("   - Organization: " . ($superAdmin->organization_id ? 'Assigned' : 'System Level'));
        } else {
            $this->error("❌ No Super Admin found");
        }
        
        $this->line('');
        
        // Check Roles
        $roles = Role::all();
        $this->info("📋 Roles ({$roles->count()}):");
        foreach ($roles as $role) {
            $this->info("   - {$role->name} (Guard: {$role->guard_name})");
        }
        
        $this->line('');
        
        // Check Permissions
        $permissions = Permission::count();
        $this->info("🔐 Permissions: {$permissions}");
        
        $this->line('');
        
        // Check Organizations
        $organizations = Organization::count();
        $this->info("🏢 Organizations: {$organizations}");
        
        if ($organizations === 0) {
            $this->warn("⚠️  No organizations found. Super admin needs organizations to create users.");
        }
        
        $this->line('');
        
        // Check Users
        $users = User::count();
        $this->info("👥 Users: {$users}");
        
        $this->line('');
        $this->info('🎯 System Status Summary:');
        $this->info('========================');
        
        if ($superAdmin && $permissions > 0 && $roles->count() > 0) {
            $this->info("✅ Super admin can log in and has permissions");
            
            if ($organizations > 0) {
                $this->info("✅ Super admin can create users (organizations exist)");
            } else {
                $this->warn("⚠️  Super admin needs organizations to assign users to");
                $this->info("💡 Suggestion: Create an organization first");
            }
        } else {
            $this->error("❌ System is not properly configured");
        }
        
        return 0;
    }
}
