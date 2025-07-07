<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\User;

class TestAuthenticationFlow extends Command
{
    protected $signature = 'debug:test-auth';
    protected $description = 'Test authentication flow for admin and user guards';

    public function handle()
    {
        $this->info("=== TESTING AUTHENTICATION FLOW ===");

        // Test Admin Authentication
        $this->info("\n1. Testing Admin Authentication:");
        $admin = Admin::where('email', 'superadmin@rms.com')->first();
        
        if ($admin) {
            $this->info("   ✅ Admin found: {$admin->name} ({$admin->email})");
            
            // Test admin login
            $loginResult = Auth::guard('admin')->attempt([
                'email' => 'superadmin@rms.com',
                'password' => 'SuperAdmin123!'
            ]);
            
            if ($loginResult) {
                $this->info("   ✅ Admin login successful");
                $authenticatedAdmin = Auth::guard('admin')->user();
                $this->info("   ✅ Authenticated admin: {$authenticatedAdmin->name}");
                $this->info("   ✅ Is Super Admin: " . ($authenticatedAdmin->isSuperAdmin() ? 'Yes' : 'No'));
                
                // Test logout
                Auth::guard('admin')->logout();
                $this->info("   ✅ Admin logout successful");
            } else {
                $this->error("   ❌ Admin login failed");
            }
        } else {
            $this->error("   ❌ Admin not found");
        }

        // Test User Authentication
        $this->info("\n2. Testing User Authentication:");
        $user = User::where('email', 'test.user@example.com')->first();
        
        if ($user) {
            $this->info("   ✅ User found: {$user->name} ({$user->email})");
            
            // Test user login
            $loginResult = Auth::guard('web')->attempt([
                'email' => 'test.user@example.com',
                'password' => 'TestPassword123!'
            ]);
            
            if ($loginResult) {
                $this->info("   ✅ User login successful");
                $authenticatedUser = Auth::guard('web')->user();
                $this->info("   ✅ Authenticated user: {$authenticatedUser->name}");
                $this->info("   ✅ User role: " . ($authenticatedUser->userRole ? $authenticatedUser->userRole->name : 'No role'));
                
                // Test logout
                Auth::guard('web')->logout();
                $this->info("   ✅ User logout successful");
            } else {
                $this->error("   ❌ User login failed");
            }
        } else {
            $this->error("   ❌ User not found");
        }

        // Test Guard Configuration
        $this->info("\n3. Testing Guard Configuration:");
        $defaultGuard = config('auth.defaults.guard');
        $this->info("   Default guard: {$defaultGuard}");
        
        $guards = config('auth.guards');
        foreach ($guards as $guardName => $guardConfig) {
            $this->info("   Guard '{$guardName}': driver={$guardConfig['driver']}, provider={$guardConfig['provider']}");
        }

        $this->info("\n=== AUTHENTICATION FLOW TEST COMPLETE ===");
        return 0;
    }
}
