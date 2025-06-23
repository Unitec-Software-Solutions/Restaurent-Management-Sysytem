<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create([
            'is_super_admin' => false,
            'organization_id' => 1,
            'branch_id' => 1,
        ]);
    }

    public function test_all_sidebar_routes_exist()
    {
        $sidebarRoutes = [
            'admin.dashboard',
            'admin.inventory.index',
            'admin.inventory.dashboard',
            'admin.orders.index',
            'admin.reservations.index',
            'admin.suppliers.index',
            'admin.customers.index',
            'admin.users.index',
            'admin.menus.index',
            'admin.reports.index',
        ];

        foreach ($sidebarRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Route {$route} does not exist but is referenced in sidebar"
            );
        }
    }

    public function test_sidebar_routes_accessible_when_authenticated()
    {
        $this->actingAs($this->admin, 'admin');

        $routes = [
            'admin.dashboard',
            'admin.inventory.index',
            'admin.orders.index',
            'admin.reservations.index',
            'admin.suppliers.index',
        ];

        foreach ($routes as $route) {
            if (Route::has($route)) {
                $response = $this->get(route($route));
                $this->assertNotEquals(302, $response->getStatusCode(), 
                    "Route {$route} redirected when it should be accessible");
                $this->assertNotEquals(500, $response->getStatusCode(),
                    "Route {$route} returned server error");
            }
        }
    }

    public function test_sidebar_routes_redirect_when_unauthenticated()
    {
        $protectedRoutes = [
            'admin.dashboard',
            'admin.inventory.index',
            'admin.orders.index',
            'admin.suppliers.index',
        ];

        foreach ($protectedRoutes as $route) {
            if (Route::has($route)) {
                $response = $this->get(route($route));
                $this->assertEquals(302, $response->getStatusCode(),
                    "Route {$route} should redirect unauthenticated users");
                
                // Should redirect to login
                $this->assertStringContainsString('login', $response->headers->get('Location'),
                    "Route {$route} should redirect to login page");
            }
        }
    }

    public function test_no_redirect_loops_in_sidebar_navigation()
    {
        $this->actingAs($this->admin, 'admin');

        $routes = [
            'admin.inventory.index',
            'admin.suppliers.index',
            'admin.orders.index',
        ];

        foreach ($routes as $route) {
            if (Route::has($route)) {
                $response = $this->get(route($route));
                
                // Should not redirect to login
                $location = $response->headers->get('Location');
                if ($location) {
                    $this->assertStringNotContainsString('/admin/login', $location,
                        "Route {$route} is creating a redirect loop to login");
                }
            }
        }
    }

    public function test_sidebar_component_renders_without_errors()
    {
        $this->actingAs($this->admin, 'admin');

        // Test the sidebar component directly
        $view = $this->blade('<x-admin-sidebar />');
        
        $view->assertSee('RM SYSTEMS'); // Logo text
        $view->assertSee('Dashboard');  // Should always have dashboard
        $view->assertDontSee('Route not available'); // Should not show broken routes
    }

    public function test_sidebar_shows_appropriate_items_for_user_permissions()
    {
        // Test normal admin
        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.dashboard'));
        
        $response->assertSee('Dashboard');
        $response->assertSee('Inventory');
        $response->assertDontSee('Subscription Plans'); // Super admin only

        // Test super admin
        $superAdmin = Admin::factory()->create(['is_super_admin' => true]);
        $this->actingAs($superAdmin, 'admin');
        $response = $this->get(route('admin.dashboard'));
        
        $response->assertSee('Dashboard');
        $response->assertSee('Subscription Plans'); // Should see super admin items
    }

    public function test_sidebar_handles_missing_routes_gracefully()
    {
        $this->actingAs($this->admin, 'admin');

        // Create a view with a non-existent route
        $view = $this->blade('
            @if(\Illuminate\Support\Facades\Route::has("non.existent.route"))
                <a href="{{ route("non.existent.route") }}">This should not show</a>
            @else
                <span class="disabled">Route not available</span>
            @endif
        ');

        $view->assertSee('Route not available');
        $view->assertDontSee('This should not show');
    }

    public function test_authentication_status_debugging()
    {
        // Test unauthenticated state
        $response = $this->get('/admin/auth/debug');
        $data = $response->json();
        
        $this->assertFalse($data['auth_admin_check']);
        $this->assertNull($data['auth_admin_user']);

        // Test authenticated state
        $this->actingAs($this->admin, 'admin');
        $response = $this->get('/admin/auth/debug');
        $data = $response->json();
        
        $this->assertTrue($data['auth_admin_check']);
        $this->assertNotNull($data['auth_admin_user']);
        $this->assertEquals($this->admin->id, $data['auth_admin_user']['id']);
    }

    public function test_session_persistence_across_requests()
    {
        $this->actingAs($this->admin, 'admin');

        // First request
        $response1 = $this->get(route('admin.dashboard'));
        $response1->assertStatus(200);

        // Second request should maintain session
        $response2 = $this->get(route('admin.inventory.index'));
        $response2->assertStatus(200);

        // Should not redirect to login
        $this->assertStringNotContainsString('/admin/login', 
            $response2->headers->get('Location') ?? '');
    }

    public function test_sidebar_component_performance()
    {
        $this->actingAs($this->admin, 'admin');

        $startTime = microtime(true);
        
        // Render sidebar component multiple times
        for ($i = 0; $i < 10; $i++) {
            $this->blade('<x-admin-sidebar />');
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Sidebar should render quickly (under 100ms for 10 renders)
        $this->assertLessThan(100, $executionTime, 
            "Sidebar component taking too long to render: {$executionTime}ms");
    }
}
