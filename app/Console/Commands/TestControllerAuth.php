<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TestControllerAuth extends Command
{
    protected $signature = 'debug:test-controller-auth';
    protected $description = 'Test authentication controllers directly';

    public function handle()
    {
        $this->info("=== TESTING CONTROLLER AUTHENTICATION ===");

        // Test 1: User Login Controller
        $this->info("\n1. Testing User Login Controller:");
        try {
            $userController = app(LoginController::class);
            $this->info("   ✅ User LoginController instantiated");
            
            // Create a mock request for user login
            $request = Request::create('/user/login', 'POST', [
                'email' => 'test123@gmail.com',
                'password' => 'TestPassword123!'
            ]);
            
            // Test login method
            $response = $userController->login($request);
            $this->info("   Login response type: " . get_class($response));
            
            if (method_exists($response, 'getStatusCode')) {
                $this->info("   Status code: " . $response->getStatusCode());
            }
            
            if (method_exists($response, 'getTargetUrl')) {
                $this->info("   Redirect URL: " . $response->getTargetUrl());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ User login controller error: " . $e->getMessage());
            $this->line("   " . $e->getTraceAsString());
        }

        // Test 2: Admin Login Controller
        $this->info("\n2. Testing Admin Login Controller:");
        try {
            $adminController = app(AdminAuthController::class);
            $this->info("   ✅ AdminAuthController instantiated");
            
            // Create a mock request for admin login
            $request = Request::create('/admin/login', 'POST', [
                'email' => 'superadmin@rms.com',
                'password' => 'SuperAdmin123!'
            ]);
            
            // Test login method
            $response = $adminController->login($request);
            $this->info("   Login response type: " . get_class($response));
            
            if (method_exists($response, 'getStatusCode')) {
                $this->info("   Status code: " . $response->getStatusCode());
            }
            
            if (method_exists($response, 'getTargetUrl')) {
                $this->info("   Redirect URL: " . $response->getTargetUrl());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Admin login controller error: " . $e->getMessage());
            $this->line("   " . $e->getTraceAsString());
        }

        $this->info("\n=== CONTROLLER AUTHENTICATION TEST COMPLETE ===");
        return 0;
    }
}
