<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CheckAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:check {--create : Create super admin if not exists} {--test-passwords : Test common passwords}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check admin accounts and create super admin if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CHECKING ADMINS IN DATABASE ===');
        
        $admins = Admin::all();
        $this->info("Total admins found: " . $admins->count());
        $this->newLine();
        
        foreach ($admins as $admin) {
            $this->info("ID: " . $admin->id);
            $this->info("Email: " . $admin->email);
            $this->info("Name: " . $admin->name);
            $this->info("Active: " . ($admin->is_active ? 'Yes' : 'No'));
            $this->info("Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No'));
            $this->info("Status: " . ($admin->status ?? 'null'));
            $this->info("Password exists: " . (!empty($admin->password) ? 'Yes' : 'No'));
            $this->info("Created: " . $admin->created_at);
            
            if ($this->option('test-passwords') && $admin->is_super_admin) {
                $this->info("Testing passwords:");
                $passwords = ['password123', 'Password123', 'SuperAdmin123!', 'admin123', 'password'];
                foreach ($passwords as $password) {
                    $matches = Hash::check($password, $admin->password);
                    $this->info("  - '$password': " . ($matches ? 'MATCH' : 'no match'));
                    if ($matches) {
                        $this->warn("  🔑 FOUND WORKING PASSWORD: $password");
                    }
                }
            }
            
            $this->line("---");
        }
        
        // Test authentication manually
        if ($this->option('test-passwords')) {
            $this->newLine();
            $this->info('=== TESTING AUTHENTICATION ===');
            
            $testCredentials = [
                ['email' => 'superadmin@restaurant-system.com', 'passwords' => ['password123', 'Password123', 'SuperAdmin123!']],
                ['email' => 'superadmin@rms.com', 'passwords' => ['SuperAdmin123!']],
            ];
            
            foreach ($testCredentials as $test) {
                $this->info("Testing authentication for: " . $test['email']);
                foreach ($test['passwords'] as $password) {
                    $result = Auth::guard('admin')->attempt(['email' => $test['email'], 'password' => $password]);
                    $this->info("  Password '$password': " . ($result ? 'SUCCESS' : 'FAILED'));
                    if ($result) {
                        Auth::guard('admin')->logout(); // Logout after test
                        $this->warn("  🔑 AUTHENTICATION SUCCESS with password: $password");
                    }
                }
            }
        }
        
        if ($this->option('create') || $admins->isEmpty()) {
            $this->newLine();
            $this->info('=== CREATING SUPER ADMIN ===');
            
            // Get or create organization
            $organization = Organization::first();
            if (!$organization) {
                $organization = Organization::create([
                    'name' => 'System Administration',
                    'description' => 'Primary system administration organization',
                    'contact_person_name' => 'System Administrator',
                    'contact_person_email' => 'admin@system.rms',
                    'contact_person_phone' => '+1234567890',
                    'contact_person_designation' => 'System Administrator',
                    'address' => 'System Headquarters',
                    'city' => 'System City',
                    'state' => 'System State',
                    'country' => 'System Country',
                    'postal_code' => '12345',
                    'is_active' => true,
                    'status' => 'active',
                    'activated_at' => now()
                ]);
                $this->info("Created organization: " . $organization->name);
            }
            
            $superAdmin = Admin::updateOrCreate(
                ['email' => 'superadmin@rms.com'],
                [
                    'name' => 'Super Administrator',
                    'password' => Hash::make('SuperAdmin123!'),
                    'organization_id' => $organization->id,
                    'is_super_admin' => true,
                    'is_active' => true,
                    'status' => 'active',
                    'role' => 'superadmin',
                    'job_title' => 'System Administrator',
                    'department' => 'Administration',
                    'email_verified_at' => now(),
                ]
            );
            
            $this->info("Super admin created/updated:");
            $this->info("ID: " . $superAdmin->id);
            $this->info("Email: " . $superAdmin->email);
            $this->info("Name: " . $superAdmin->name);
            $this->info("Password Hash: " . substr($superAdmin->password, 0, 20) . "...");
            $this->info("Is Super Admin: " . ($superAdmin->is_super_admin ? 'Yes' : 'No'));
            $this->info("Is Active: " . ($superAdmin->is_active ? 'Yes' : 'No'));
            $this->info("Status: " . $superAdmin->status);
            
            // Test password verification
            $testPassword = 'SuperAdmin123!';
            $passwordMatches = Hash::check($testPassword, $superAdmin->password);
            $this->info("Password verification test: " . ($passwordMatches ? 'PASS' : 'FAIL'));
            
            $this->newLine();
            $this->info('=== LOGIN CREDENTIALS ===');
            $this->info('URL: /admin/login');
            $this->info('Email: superadmin@rms.com');
            $this->info('Password: SuperAdmin123!');
            $this->warn('Please change the password after first login!');
        }
    }
}
