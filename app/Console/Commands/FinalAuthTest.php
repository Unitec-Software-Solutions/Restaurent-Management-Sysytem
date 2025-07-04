<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;

class FinalAuthTest extends Command
{
    protected $signature = 'test:final-auth';
    protected $description = 'Final comprehensive authentication test';

    public function handle()
    {
        $this->info("🔐 FINAL AUTHENTICATION SYSTEM TEST");
        $this->line("=" . str_repeat("=", 50));

        // Test 1: Regular User Authentication
        $this->info("\n1️⃣ Testing Regular User Authentication:");
        $this->testUserAuth('test.user@example.com', 'TestPassword123!');

        // Test 2: Organizational Admin Authentication (both guards)
        $this->info("\n2️⃣ Testing Organizational Admin Authentication:");
        $this->testAdminAuth('org.admin@example.com', 'AdminPassword123!');

        // Test 3: Super Admin Authentication (both guards)
        $this->info("\n3️⃣ Testing Super Admin Authentication:");
        $this->testAdminAuth('superadmin@rms.com', 'SuperAdmin123!');

        // Test 4: User Management Access
        $this->info("\n4️⃣ Testing User Management Access:");
        $this->testUserManagementAccess();

        $this->line("\n" . str_repeat("=", 60));
        $this->info("🎉 FINAL TEST COMPLETE!");
        
        $this->table(['Login Portal', 'URL', 'Who Can Use'], [
            ['User Portal', 'http://localhost:8000/user/login', 'Everyone (Users + Admins)'],
            ['Admin Portal', 'http://localhost:8000/admin/login', 'Admins Only'],
            ['User Management', 'http://localhost:8000/admin/users', 'Superadmin Only'],
        ]);

        return 0;
    }

    private function testUserAuth($email, $password)
    {
        try {
            // Test web guard
            $success = Auth::guard('web')->attempt(['email' => $email, 'password' => $password]);
            if ($success) {
                $user = Auth::guard('web')->user();
                $this->info("   ✅ Web Guard: {$user->name} ({$user->email})");
                $this->line("      Role: " . ($user->userRole ? $user->userRole->name : 'No role'));
                Auth::guard('web')->logout();
            } else {
                $this->warn("   ⚠️ Web Guard: Login failed");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Web Guard Error: " . $e->getMessage());
        }
    }

    private function testAdminAuth($email, $password)
    {
        try {
            // Test web guard first (unified login)
            $webSuccess = Auth::guard('web')->attempt(['email' => $email, 'password' => $password]);
            if ($webSuccess) {
                $user = Auth::guard('web')->user();
                $this->info("   ✅ Web Guard: {$user->name} (User found in users table)");
                Auth::guard('web')->logout();
            } else {
                $this->line("   ➡️ Web Guard: No user found, checking admin guard...");
            }

            // Test admin guard
            $adminSuccess = Auth::guard('admin')->attempt(['email' => $email, 'password' => $password]);
            if ($adminSuccess) {
                $admin = Auth::guard('admin')->user();
                $this->info("   ✅ Admin Guard: {$admin->name} ({$admin->email})");
                $this->line("      Type: " . ($admin->is_super_admin ? 'Super Admin' : 'Organization Admin'));
                $this->line("      Organization: " . ($admin->organization_id ?? 'None'));
                Auth::guard('admin')->logout();
            } else {
                $this->warn("   ⚠️ Admin Guard: Login failed");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Admin Auth Error: " . $e->getMessage());
        }
    }

    private function testUserManagementAccess()
    {
        try {
            // Login as superadmin
            $success = Auth::guard('admin')->attempt([
                'email' => 'superadmin@rms.com', 
                'password' => 'SuperAdmin123!'
            ]);

            if ($success) {
                $admin = Auth::guard('admin')->user();
                $this->info("   ✅ Superadmin logged in: {$admin->name}");

                // Check superadmin method
                if ($admin->isSuperAdmin()) {
                    $this->info("   ✅ isSuperAdmin() method works");
                } else {
                    $this->warn("   ⚠️ isSuperAdmin() method failed");
                }

                // Check users count
                $usersCount = User::count();
                $adminsCount = Admin::count();
                $this->info("   📊 Database Status:");
                $this->line("      Users: {$usersCount}");
                $this->line("      Admins: {$adminsCount}");

                Auth::guard('admin')->logout();
            } else {
                $this->error("   ❌ Could not login superadmin");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ User Management Test Error: " . $e->getMessage());
        }
    }
}
