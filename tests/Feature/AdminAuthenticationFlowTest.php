<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_flow()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Visit login page
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Login'); // Should see login form

        // Submit login credentials
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Should redirect to dashboard after successful login
        $response->assertRedirect(route('admin.dashboard'));

        // Should be authenticated
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_logout_flow()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // Should be authenticated
        $this->assertAuthenticated('admin');

        // Logout
        $response = $this->post('/admin/logout');
        
        // Should redirect to login page
        $response->assertRedirect(route('admin.login'));

        // Should not be authenticated
        $this->assertGuest('admin');
    }

    public function test_unauthenticated_admin_redirected_to_login()
    {
        $protectedRoutes = [
            'admin.dashboard',
            'admin.inventory.index',
            'admin.orders.index',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect();
            
            $location = $response->headers->get('Location');
            $this->assertStringContainsString('login', $location);
        }
    }

    public function test_admin_session_persistence()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // Make multiple requests
        $routes = ['admin.dashboard', 'admin.inventory.index', 'admin.suppliers.index'];
        
        foreach ($routes as $route) {
            if (\Illuminate\Support\Facades\Route::has($route)) {
                $response = $this->get(route($route));
                $this->assertAuthenticated('admin');
                $this->assertNotEquals(302, $response->getStatusCode(), 
                    "Lost authentication on route: {$route}");
            }
        }
    }

    public function test_invalid_login_credentials()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('correct-password'),
        ]);

        // Try with wrong password
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('admin');

        // Try with non-existent email
        $response = $this->post('/admin/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'any-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('admin');
    }

    public function test_admin_guard_isolation()
    {
        $admin = Admin::factory()->create();
        
        // Login as admin
        $this->actingAs($admin, 'admin');
        
        // Should be authenticated on admin guard
        $this->assertAuthenticated('admin');
        
        // Should NOT be authenticated on web guard
        $this->assertGuest('web');

        // Admin routes should work
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_session_configuration()
    {
        // Check session driver is database
        $this->assertEquals('database', config('session.driver'));
        
        // Check session table exists
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('sessions'));
        
        // Check auth configuration
        $this->assertEquals('admin', config('auth.defaults.guard'));
        $this->assertEquals('session', config('auth.guards.admin.driver'));
        $this->assertEquals('admins', config('auth.guards.admin.provider'));
    }

    public function test_authentication_middleware_protection()
    {
        // Test routes that should be protected
        $protectedRoutes = [
            ['GET', '/admin/inventory', 'admin.inventory.index'],
            ['GET', '/admin/suppliers', 'admin.suppliers.index'],
            ['GET', '/admin/orders', 'admin.orders.index'],
            ['GET', '/admin/dashboard', 'admin.dashboard'],
        ];

        foreach ($protectedRoutes as [$method, $url, $routeName]) {
            // Without authentication
            $response = $this->call($method, $url);
            $this->assertEquals(302, $response->getStatusCode(), 
                "Route {$routeName} should redirect unauthenticated users");

            // With authentication
            $admin = Admin::factory()->create();
            $this->actingAs($admin, 'admin');
            
            $response = $this->call($method, $url);
            $this->assertNotEquals(302, $response->getStatusCode(), 
                "Route {$routeName} should be accessible to authenticated admins");
            
            // Reset authentication for next iteration
            auth()->guard('admin')->logout();
        }
    }

    public function test_no_authentication_bypass_vulnerabilities()
    {
        // Test that authentication cannot be bypassed with manipulated headers
        $protectedUrl = route('admin.dashboard');
        
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => 'fake-token',
            'Authorization' => 'Bearer fake-token',
        ])->get($protectedUrl);
        
        $this->assertEquals(302, $response->getStatusCode(), 
            'Authentication should not be bypassable with fake headers');
    }

    public function test_authentication_debug_endpoint()
    {
        // Test without authentication
        $response = $this->get('/admin/auth/debug');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertFalse($data['auth_admin_check']);
        $this->assertNull($data['auth_admin_user']);

        // Test with authentication
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        
        $response = $this->get('/admin/auth/debug');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertTrue($data['auth_admin_check']);
        $this->assertArrayHasKey('auth_admin_user', $data);
        $this->assertEquals($admin->id, $data['auth_admin_user']['id']);
    }
}
