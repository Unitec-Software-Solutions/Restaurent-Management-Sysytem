<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class RouteValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test admin user
        $this->admin = Admin::factory()->create([
            'email' => 'test@admin.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);
    }

    /** @test */
    public function admin_routes_require_authentication()
    {
        $response = $this->get(route('admin.dashboard'));
        $this->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function authenticated_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin, 'admin');
        
        $response = $this->get(route('admin.dashboard'));
        $this->assertSuccessful();
    }

    /** @test */
    public function inventory_routes_are_accessible()
    {
        $this->actingAs($this->admin, 'admin');
        
        $routes = [
            'admin.inventory.index',
            'admin.inventory.items.index',
            'admin.inventory.items.create',
            'admin.inventory.stock.index',
        ];
        
        foreach ($routes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$routeName} returned 404");
            }
        }
    }

    /** @test */
    public function supplier_routes_are_accessible()
    {
        $this->actingAs($this->admin, 'admin');
        
        $routes = [
            'admin.suppliers.index',
            'admin.suppliers.create',
        ];
        
        foreach ($routes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$routeName} returned 404");
            }
        }
    }

    /** @test */
    public function order_routes_are_accessible()
    {
        $this->actingAs($this->admin, 'admin');
        
        $routes = [
            'admin.orders.index',
            'admin.orders.create',
        ];
        
        foreach ($routes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$routeName} returned 404");
            }
        }
    }

    /** @test */
    public function grn_routes_are_accessible()
    {
        $this->actingAs($this->admin, 'admin');
        
        $routes = [
            'admin.grn.index',
            'admin.grn.create',
        ];
        
        foreach ($routes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$routeName} returned 404");
            }
        }
    }

    /** @test */
    public function guest_routes_are_accessible()
    {
        $routes = [
            'guest.menu.branch-selection',
            'guest.cart.view',
        ];
        
        foreach ($routes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $response = $this->get(route($routeName));
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$routeName} returned 404");
            }
        }
    }

    /** @test */
    public function super_admin_routes_require_proper_permissions()
    {
        $this->admin->is_super_admin = false;
        $this->admin->save();
        
        $this->actingAs($this->admin, 'admin');
        
        if (\Illuminate\Support\Facades\Route::has('admin.organizations.index')) {
            $response = $this->get(route('admin.organizations.index'));
            $this->assertEquals(403, $response->getStatusCode());
        }
    }
}
