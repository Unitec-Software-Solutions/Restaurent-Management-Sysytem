<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\Organization;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:create-super 
                           {--email=superadmin@rms.com : Super admin email}
                           {--password=SuperAdmin123! : Super admin password}
                           {--name=Super Administrator : Super admin name}
                           {--reset : Reset existing super admin}';

    /**
     * The console command description.
     */
    protected $description = 'Create or reset the super admin with known credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');
        $reset = $this->option('reset');

        $this->info('🔧 Creating/Updating Super Admin...');

        // Check if admin exists
        $admin = Admin::where('email', $email)->first();

        if ($admin && !$reset) {
            $this->warn("Admin with email {$email} already exists!");
            if (!$this->confirm('Do you want to update the password?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        // Create or update admin (Super admin has NO organization - they control the entire system)
        if ($admin) {
            $admin->update([
                'password' => Hash::make($password),
                'organization_id' => null, // Super admin belongs to no organization
                'is_super_admin' => true,
                'is_active' => true,
                'status' => 'active',
            ]);
            $this->info('✅ Super admin password updated.');
        } else {
            $admin = Admin::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => '+94 11 000 0000',
                'job_title' => 'System Administrator',
                'organization_id' => null, // Super admin belongs to no organization
                'is_super_admin' => true,
                'is_active' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $this->info('✅ Super admin created.');
        }

        // Ensure Super Admin role exists
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin'
        ]);

        // Assign all permissions to the role
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);

        // Assign role to admin
        $admin->syncRoles([$superAdminRole]);

        $this->info('');
        $this->info('🔐 SUPER ADMIN LOGIN CREDENTIALS:');
        $this->info("   Email: {$email}");
        $this->info("   Password: {$password}");
        $this->info("   Type: System Administrator (No Organization)");
        $this->info("   Permissions: {$allPermissions->count()}");
        $this->info('');
        $this->warn('⚠️  Please change the password after first login for security!');
    }
}
