<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TestDirectLogin extends Command
{
    protected $signature = 'debug:test-direct-login';
    protected $description = 'Test login controllers directly without HTTP';

    public function handle()
    {
        $this->info("=== TESTING DIRECT LOGIN CONTROLLERS ===");

        // Test 1: User login with valid user credentials
        $this->info("\n1. Testing User Login with Regular User:");
        try {
            $request = Request::create('/user/login', 'POST', [
                'email' => 'test.user@example.com',
                'password' => 'TestPassword123!'
            ]);
            
            $request->setLaravelSession(app('session'));
            
            $controller = new LoginController();
            $response = $controller->login($request);
            
            $this->info("   ✅ User login successful");
            $this->info("   Response type: " . get_class($response));
            if (method_exists($response, 'getTargetUrl')) {
                $this->info("   Redirect URL: " . $response->getTargetUrl());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ User login failed: " . $e->getMessage());
        }

        // Test 2: User login with admin credentials (org admin)
        $this->info("\n2. Testing User Login with Org Admin:");
        try {
            $request = Request::create('/user/login', 'POST', [
                'email' => 'org.admin@example.com',
                'password' => 'AdminPassword123!'
            ]);
            
            $request->setLaravelSession(app('session'));
            
            $controller = new LoginController();
            $response = $controller->login($request);
            
            $this->info("   ✅ Org admin login through user portal successful");
            $this->info("   Response type: " . get_class($response));
            if (method_exists($response, 'getTargetUrl')) {
                $this->info("   Redirect URL: " . $response->getTargetUrl());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Org admin login through user portal failed: " . $e->getMessage());
        }

        // Test 3: User login with super admin credentials
        $this->info("\n3. Testing User Login with Super Admin:");
        try {
            $request = Request::create('/user/login', 'POST', [
                'email' => 'superadmin@rms.com',
                'password' => 'SuperAdmin123!'
            ]);
            
            $request->setLaravelSession(app('session'));
            
            $controller = new LoginController();
            $response = $controller->login($request);
            
            $this->info("   ✅ Super admin login through user portal successful");
            $this->info("   Response type: " . get_class($response));
            if (method_exists($response, 'getTargetUrl')) {
                $this->info("   Redirect URL: " . $response->getTargetUrl());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Super admin login through user portal failed: " . $e->getMessage());
        }

        // Test 4: Invalid credentials
        $this->info("\n4. Testing Invalid Credentials:");
        try {
            $request = Request::create('/user/login', 'POST', [
                'email' => 'nonexistent@example.com',
                'password' => 'WrongPassword123!'
            ]);
            
            $request->setLaravelSession(app('session'));
            
            $controller = new LoginController();
            $response = $controller->login($request);
            
            $this->error("   ❌ Expected validation exception but login succeeded");
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->info("   ✅ Invalid credentials properly rejected");
            $this->info("   Error message: " . collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            $this->error("   ❌ Unexpected error: " . $e->getMessage());
        }

        $this->info("\n=== DIRECT LOGIN TEST COMPLETE ===");
        return 0;
    }
}
