<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Branch;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $branch;
    protected $menuItem;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization
        $organization = Organization::factory()->create([
            'is_active' => true
        ]);

        // Create test branch
        $this->branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'is_active' => true
        ]);

        // Create test menu item
        $this->menuItem = MenuItem::factory()->create([
            'branch_id' => $this->branch->id,
            'type' => MenuItem::TYPE_BUY_SELL,
            'stock' => 10,
            'price' => 25.00
        ]);

        // Create admin user
        $this->user = User::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $this->branch->id
        ]);
    }

    /** @test */
    public function it_can_create_a_takeaway_order()
    {
        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), [
                'customer_name' => 'John Doe',
                'customer_phone' => '1234567890',
                'order_type' => 'takeaway',
                'branch_id' => $this->branch->id,
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem->id,
                        'quantity' => 2
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '1234567890',
            'order_type' => 'takeaway',
            'branch_id' => $this->branch->id
        ]);

        $order = Order::latest()->first();
        $response->assertRedirect(route('orders.summary', $order));
    }

    /** @test */
    public function it_validates_stock_for_buy_sell_items()
    {
        $this->menuItem->update(['stock' => 1]);

        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), [
                'customer_name' => 'John Doe',
                'customer_phone' => '1234567890',
                'order_type' => 'takeaway',
                'branch_id' => $this->branch->id,
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem->id,
                        'quantity' => 5 // More than available stock
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_shows_order_summary_correctly()
    {
        $order = Order::factory()->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '1234567890',
            'order_type' => 'takeaway',
            'branch_id' => $this->branch->id,
            'total' => 50.00
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('orders.summary', $order));

        $response->assertOk();
        $response->assertSee('Order Summary');
        $response->assertSee('John Doe');
        $response->assertSee('Takeaway Order');
        $response->assertSee('LKR 50.00');
    }

    /** @test */
    public function admin_can_create_orders_with_branch_defaults()
    {
        $this->user->assignRole('admin');

        $response = $this->actingAs($this->user)
            ->get(route('admin.orders.create'));

        $response->assertOk();
        $response->assertViewHas('selectedBranch', $this->branch);
    }

    /** @test */
    public function it_handles_kot_type_menu_items_correctly()
    {
        $kotItem = MenuItem::factory()->create([
            'branch_id' => $this->branch->id,
            'type' => MenuItem::TYPE_KOT,
            'price' => 15.00
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), [
                'customer_name' => 'Jane Doe',
                'customer_phone' => '0987654321',
                'order_type' => 'takeaway',
                'branch_id' => $this->branch->id,
                'items' => [
                    [
                        'menu_item_id' => $kotItem->id,
                        'quantity' => 3
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Jane Doe',
            'order_type' => 'takeaway'
        ]);

        // KOT items should not affect stock
        $this->assertEquals(null, $kotItem->fresh()->stock);
    }

    /** @test */
    public function order_policy_prevents_unauthorized_updates()
    {
        $order = Order::factory()->create([
            'branch_id' => $this->branch->id
        ]);

        $otherOrganization = Organization::factory()->create();
        $otherBranch = Branch::factory()->create();
        
        /** @var User $unauthorizedUser */
        $unauthorizedUser = User::factory()->create([
            'organization_id' => $otherOrganization->id,
            'branch_id' => $otherBranch->id
        ]);

        $response = $this->actingAs($unauthorizedUser)
            ->put(route('orders.update', $order), [
                'customer_name' => 'Unauthorized Change'
            ]);

        $response->assertForbidden();
    }
}
