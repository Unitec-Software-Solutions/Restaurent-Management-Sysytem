<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

class AdminAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization
        $this->organization = Organization::factory()->create();
    }

    /** @test */
    public function admin_can_login_successfully()
    {
        // Create single admin model (not collection)
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password', // Default factory password
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /** @test */
    public function admin_can_access_dashboard_when_authenticated()
    {
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function admin_can_access_inventory_when_authenticated()
    {
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('admin.inventory.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.inventory.index');
    }

    /** @test */
    public function admin_can_access_suppliers_when_authenticated()
    {
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('admin.suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.suppliers.index');
    }

    /** @test */
    public function inactive_admin_cannot_access_protected_routes()
    {
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => $this->organization->id,
            'is_active' => false, // Inactive admin
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function admin_without_organization_cannot_access_dashboard()
    {
        /** @var Authenticatable $admin */
        $admin = Admin::factory()->create([
            'organization_id' => null, // No organization
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }
}
