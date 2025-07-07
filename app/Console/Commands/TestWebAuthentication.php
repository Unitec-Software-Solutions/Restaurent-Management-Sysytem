<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestWebAuthentication extends Command
{
    protected $signature = 'debug:test-web-auth';
    protected $description = 'Test web authentication by making HTTP requests';

    public function handle()
    {
        $baseUrl = 'http://localhost:8000';
        
        $this->info("=== TESTING WEB AUTHENTICATION ===");

        // Test 1: Check if user login page is accessible
        $this->info("\n1. Testing User Login Page:");
        try {
            $response = Http::get("{$baseUrl}/user/login");
            $this->info("   Status: {$response->status()}");
            if ($response->successful()) {
                $this->info("   ✅ User login page accessible");
            } else {
                $this->error("   ❌ User login page not accessible");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error accessing user login page: " . $e->getMessage());
        }

        // Test 2: Check if admin login page is accessible
        $this->info("\n2. Testing Admin Login Page:");
        try {
            $response = Http::get("{$baseUrl}/admin/login");
            $this->info("   Status: {$response->status()}");
            if ($response->successful()) {
                $this->info("   ✅ Admin login page accessible");
            } else {
                $this->error("   ❌ Admin login page not accessible");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error accessing admin login page: " . $e->getMessage());
        }

        // Test 3: Test user login
        $this->info("\n3. Testing User Login:");
        try {
            // First get CSRF token
            $loginPageResponse = Http::get("{$baseUrl}/user/login");
            preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPageResponse->body(), $matches);
            $csrfToken = $matches[1] ?? null;

            if ($csrfToken) {
                $this->info("   ✅ CSRF token obtained");
                
                // Attempt login
                $loginResponse = Http::asForm()->post("{$baseUrl}/user/login", [
                    '_token' => $csrfToken,
                    'email' => 'test.user@example.com',
                    'password' => 'TestPassword123!'
                ]);
                
                $this->info("   Login response status: {$loginResponse->status()}");
                
                if ($loginResponse->status() === 302) {
                    $location = $loginResponse->header('Location');
                    $this->info("   ✅ Login redirected to: {$location}");
                } else {
                    $this->error("   ❌ Login failed");
                    $this->line("   Response body: " . substr($loginResponse->body(), 0, 200) . "...");
                }
            } else {
                $this->error("   ❌ Could not get CSRF token");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error during user login: " . $e->getMessage());
        }

        // Test 4: Test admin login
        $this->info("\n4. Testing Admin Login:");
        try {
            // First get CSRF token
            $loginPageResponse = Http::get("{$baseUrl}/admin/login");
            preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPageResponse->body(), $matches);
            $csrfToken = $matches[1] ?? null;

            if ($csrfToken) {
                $this->info("   ✅ CSRF token obtained");
                
                // Attempt login
                $loginResponse = Http::asForm()->post("{$baseUrl}/admin/login", [
                    '_token' => $csrfToken,
                    'email' => 'superadmin@rms.com',
                    'password' => 'SuperAdmin123!'
                ]);
                
                $this->info("   Login response status: {$loginResponse->status()}");
                
                if ($loginResponse->status() === 302) {
                    $location = $loginResponse->header('Location');
                    $this->info("   ✅ Admin login redirected to: {$location}");
                    
                    // Get cookies from login response
                    $cookies = $loginResponse->cookies();
                    $sessionCookie = null;
                    
                    foreach ($cookies as $cookie) {
                        if (str_contains($cookie->getName(), 'session') || str_contains($cookie->getName(), 'laravel')) {
                            $sessionCookie = $cookie;
                            break;
                        }
                    }
                    
                    if ($sessionCookie) {
                        $this->info("   ✅ Session cookie obtained");
                        
                        // Test accessing user management page
                        $this->info("\n5. Testing User Management Access:");
                        $userMgmtResponse = Http::withCookies([
                            $sessionCookie->getName() => $sessionCookie->getValue()
                        ], $sessionCookie->getDomain() ?: 'localhost')->get("{$baseUrl}/admin/users");
                        
                        $this->info("   User management response status: {$userMgmtResponse->status()}");
                        
                        if ($userMgmtResponse->successful()) {
                            $this->info("   ✅ User management page accessible after admin login");
                        } else if ($userMgmtResponse->status() === 302) {
                            $redirectLocation = $userMgmtResponse->header('Location');
                            $this->warn("   ⚠️ User management page redirected to: {$redirectLocation}");
                        } else {
                            $this->error("   ❌ User management page not accessible");
                        }
                    } else {
                        $this->error("   ❌ No session cookie in login response");
                    }
                } else {
                    $this->error("   ❌ Admin login failed");
                    $this->line("   Response body: " . substr($loginResponse->body(), 0, 200) . "...");
                }
            } else {
                $this->error("   ❌ Could not get CSRF token for admin login");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error during admin login: " . $e->getMessage());
        }

        $this->info("\n=== WEB AUTHENTICATION TEST COMPLETE ===");
        return 0;
    }
}
