<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteFixesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function all_routes_are_accessible()
    {
        // TODO: Add authentication setup
        // $admin = Admin::factory()->create();
        // $this->actingAs($admin, 'admin');

        $this->get(route('menu.branch-selection'))->assertSuccessful();
        $this->get(route('menu.view'))->assertSuccessful();
        $this->get(route('menu.date'))->assertSuccessful();
        $this->get(route('menu.special'))->assertSuccessful();
        $this->get(route('cart.view'))->assertSuccessful();
    }
}
