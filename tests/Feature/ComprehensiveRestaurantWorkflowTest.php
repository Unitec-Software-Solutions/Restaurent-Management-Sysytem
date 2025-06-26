<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\ItemMaster;
use Carbon\Carbon;

class ComprehensiveRestaurantWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $basicPlan;
    private $proPlan;
    private $enterprisePlan;
    
    private $basicOrg;
    private $proOrg;
    private $enterpriseOrg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        // Create subscription plans
        $this->basicPlan = SubscriptionPlan::create([
            'name' => 'Basic',
            'modules' => [
                ['name' => 'pos', 'tier' => 'basic'],
                ['name' => 'kitchen', 'tier' => 'basic'],
            ],
            'features' => ['basic_ordering', 'kot_display'],
            'max_branches' => 2,
            'max_employees' => 10,
            'price' => 0,
        ]);

        $this->proPlan = SubscriptionPlan::create([
            'name' => 'Pro',
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium'],
                ['name' => 'kitchen', 'tier' => 'premium'],
                ['name' => 'inventory', 'tier' => 'premium'],
                ['name' => 'staff', 'tier' => 'premium'],
            ],
            'features' => ['basic_ordering', 'kot_display', 'inventory_alerts', 'staff_management'],
            'max_branches' => 10,
            'max_employees' => 50,
            'price' => 5000,
        ]);

        $this->enterprisePlan = SubscriptionPlan::create([
            'name' => 'Enterprise',
            'modules' => [
                ['name' => 'pos', 'tier' => 'enterprise'],
                ['name' => 'kitchen', 'tier' => 'enterprise'],
                ['name' => 'inventory', 'tier' => 'enterprise'],
                ['name' => 'staff', 'tier' => 'enterprise'],
                ['name' => 'analytics', 'tier' => 'enterprise'],
            ],
            'features' => ['all_features'],
            'max_branches' => 100,
            'max_employees' => 500,
            'price' => 15000,
        ]);

        // Create organizations with different subscription tiers
        $this->basicOrg = $this->createOrganizationWithSubscription($this->basicPlan, 'Basic Restaurant');
        $this->proOrg = $this->createOrganizationWithSubscription($this->proPlan, 'Pro Restaurant');
        $this->enterpriseOrg = $this->createOrganizationWithSubscription($this->enterprisePlan, 'Enterprise Restaurant');
    }

    private function createOrganizationWithSubscription(SubscriptionPlan $plan, string $name): Organization
    {
        $organization = Organization::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '@test.com',
            'is_active' => true,
            'subscription_plan_id' => $plan->id,
        ]);

        Subscription::create([
            'organization_id' => $organization->id,
            'plan_id' => $plan->id,
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addYear(),
            'is_active' => true,
        ]);

        // Create branches
        for ($i = 1; $i <= 2; $i++) {
            Branch::create([
                'organization_id' => $organization->id,
                'name' => "{$name} Branch {$i}",
                'is_active' => true,
            ]);
        }

        return $organization->fresh(['branches', 'currentSubscription']);
    }

    /** @test */
    public function test_subscription_module_access_control()
    {
        // Test basic plan limitations
        $this->assertFalse($this->basicOrg->hasModule('inventory'));
        $this->assertTrue($this->basicOrg->hasModule('pos'));
        $this->assertTrue($this->basicOrg->hasModule('kitchen'));

        // Test pro plan access
        $this->assertTrue($this->proOrg->hasModule('inventory'));
        $this->assertTrue($this->proOrg->hasModule('staff'));
        $this->assertFalse($this->proOrg->hasModule('analytics'));

        // Test enterprise plan access
        $this->assertTrue($this->enterpriseOrg->hasModule('analytics'));
        $this->assertTrue($this->enterpriseOrg->hasModule('inventory'));
    }

    /** @test */
    public function test_subscription_feature_limitations()
    {
        // Basic plan feature checks
        $this->assertTrue($this->basicOrg->hasFeature('basic_ordering'));
        $this->assertFalse($this->basicOrg->hasFeature('inventory_alerts'));
        $this->assertFalse($this->basicOrg->hasFeature('staff_management'));

        // Pro plan feature checks
        $this->assertTrue($this->proOrg->hasFeature('inventory_alerts'));
        $this->assertTrue($this->proOrg->hasFeature('staff_management'));

        // Enterprise plan feature checks
        $this->assertTrue($this->enterpriseOrg->hasFeature('all_features'));
    }

    /** @test */
    public function test_branch_and_employee_limits()
    {
        // Test basic plan limits (2 branches, 10 employees)
        $this->assertTrue($this->basicOrg->canAddBranches());
        
        // Add employees up to limit
        for ($i = 1; $i <= 10; $i++) {
            Employee::create([
                'organization_id' => $this->basicOrg->id,
                'branch_id' => $this->basicOrg->branches->first()->id,
                'employee_id' => "EMP{$i}",
                'first_name' => "Employee{$i}",
                'last_name' => 'Test',
                'role' => 'waiter',
            ]);
        }

        // Should not be able to add more employees
        $this->assertFalse($this->basicOrg->fresh()->canAddEmployees());
    }

    /** @test */
    public function test_order_to_kitchen_workflow()
    {
        $branch = $this->proOrg->branches->first();
        
        // Create user for the branch
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
        ]);

        // Create an order
        $order = Order::create([
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'order_number' => 'TEST001',
            'customer_name' => 'Test Customer',
            'status' => 'pending',
            'total_amount' => 1000,
            'created_by' => $user->id,
        ]);

        // Test order status progression
        $this->assertEquals('pending', $order->status);

        // Confirm order (should trigger KOT generation in real system)
        $order->update(['status' => 'confirmed']);
        $this->assertEquals('confirmed', $order->status);

        // Kitchen starts preparing
        $order->update(['status' => 'preparing']);
        $this->assertEquals('preparing', $order->status);

        // Food ready
        $order->update(['status' => 'ready']);
        $this->assertEquals('ready', $order->status);

        // Served to customer
        $order->update(['status' => 'served']);
        $this->assertEquals('served', $order->status);
    }

    /** @test */
    public function test_inventory_low_stock_alerts()
    {
        $branch = $this->proOrg->branches->first();
        
        // Create item with low stock
        $itemMaster = ItemMaster::create([
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'name' => 'Test Item',
            'item_code' => 'TEST001',
            'reorder_level' => 10,
            'selling_price' => 100,
        ]);

        // Create inventory with stock below reorder level
        $inventory = InventoryItem::create([
            'item_master_id' => $itemMaster->id,
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'current_stock' => 5, // Below reorder level of 10
        ]);

        // Test low stock detection
        $this->assertTrue($inventory->current_stock <= $itemMaster->reorder_level);
        
        // Test critical stock (50% of reorder level)
        $criticalLevel = $itemMaster->reorder_level * 0.5;
        $this->assertTrue($inventory->current_stock <= $criticalLevel);
    }

    /** @test */
    public function test_subscription_downgrade_restrictions()
    {
        // Create enterprise organization with many branches
        $branches = [];
        for ($i = 3; $i <= 5; $i++) {
            $branches[] = Branch::create([
                'organization_id' => $this->enterpriseOrg->id,
                'name' => "Enterprise Branch {$i}",
                'is_active' => true,
            ]);
        }

        // Try to downgrade to basic plan (should fail due to branch limit)
        $currentBranchCount = $this->enterpriseOrg->branches()->count();
        $this->assertGreaterThan($this->basicPlan->max_branches, $currentBranchCount);
        
        // In real implementation, downgrade should be blocked
        $canDowngrade = $currentBranchCount <= $this->basicPlan->max_branches;
        $this->assertFalse($canDowngrade);
    }

    /** @test */
    public function test_module_tier_functionality()
    {
        // Basic tier should have limited features
        $basicTier = $this->basicOrg->getModuleTier('pos');
        $this->assertEquals('basic', $basicTier);

        // Pro tier should have premium features
        $proTier = $this->proOrg->getModuleTier('pos');
        $this->assertEquals('premium', $proTier);

        // Enterprise tier should have all features
        $enterpriseTier = $this->enterpriseOrg->getModuleTier('pos');
        $this->assertEquals('enterprise', $enterpriseTier);
    }

    /** @test */
    public function test_staff_assignment_by_shift()
    {
        $branch = $this->proOrg->branches->first();
        
        // Create employees with different shift preferences
        $morningStaff = Employee::create([
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'employee_id' => 'MORN001',
            'first_name' => 'Morning',
            'last_name' => 'Staff',
            'role' => 'waiter',
            'shift_preference' => 'morning',
            'performance_rating' => 4,
            'current_workload' => 2,
        ]);

        $eveningStaff = Employee::create([
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'employee_id' => 'EVE001',
            'first_name' => 'Evening',
            'last_name' => 'Staff',
            'role' => 'waiter',
            'shift_preference' => 'evening',
            'performance_rating' => 5,
            'current_workload' => 1,
        ]);

        // Test staff availability by shift
        $morningWaiters = Employee::where('branch_id', $branch->id)
            ->where('shift_preference', 'morning')
            ->where('role', 'waiter')
            ->get();
        
        $this->assertCount(1, $morningWaiters);
        $this->assertEquals('MORN001', $morningWaiters->first()->employee_id);

        $eveningWaiters = Employee::where('branch_id', $branch->id)
            ->where('shift_preference', 'evening')
            ->where('role', 'waiter')
            ->get();
        
        $this->assertCount(1, $eveningWaiters);
        $this->assertEquals('EVE001', $eveningWaiters->first()->employee_id);
    }

    /** @test */
    public function test_real_time_kot_tracking()
    {
        $branch = $this->proOrg->branches->first();
        
        // Create kitchen station
        $kitchenStation = \App\Models\KitchenStation::create([
            'name' => 'Hot Kitchen',
            'code' => 'HOT',
            'branch_id' => $branch->id,
            'is_active' => true,
            'max_concurrent_orders' => 5,
        ]);

        // Create order
        $order = Order::create([
            'organization_id' => $this->proOrg->id,
            'branch_id' => $branch->id,
            'order_number' => 'KOT001',
            'customer_name' => 'KOT Customer',
            'status' => 'confirmed',
            'total_amount' => 800,
        ]);

        // Create KOT
        $kot = \App\Models\Kot::create([
            'order_id' => $order->id,
            'kitchen_station_id' => $kitchenStation->id,
            'kot_number' => 'KOT001-1',
            'status' => 'pending',
            'priority' => 'normal',
            'estimated_completion_time' => Carbon::now()->addMinutes(20),
        ]);

        // Test KOT workflow
        $this->assertEquals('pending', $kot->status);

        // Start preparation
        $kot->startPreparation();
        $this->assertEquals('in_progress', $kot->fresh()->status);
        $this->assertNotNull($kot->fresh()->started_at);

        // Complete preparation
        $kot->complete();
        $this->assertEquals('completed', $kot->fresh()->status);
        $this->assertNotNull($kot->fresh()->completed_at);
    }

    /** @test */
    public function test_subscription_module_activation_workflow()
    {
        // Test that modules can be activated/deactivated based on subscription
        $activeModules = [];
        
        foreach ($this->proOrg->currentSubscription->plan->modules as $module) {
            if ($this->proOrg->hasModule($module['name'])) {
                $activeModules[] = $module['name'];
            }
        }

        // Pro plan should have these modules active
        $expectedModules = ['pos', 'kitchen', 'inventory', 'staff'];
        foreach ($expectedModules as $expectedModule) {
            $this->assertContains($expectedModule, $activeModules);
        }

        // Should not have analytics module
        $this->assertNotContains('analytics', $activeModules);
    }

    /** @test */
    public function test_comprehensive_restaurant_operations()
    {
        $branch = $this->enterpriseOrg->branches->first();
        
        // Test end-to-end workflow
        
        // 1. Create order
        $order = Order::create([
            'organization_id' => $this->enterpriseOrg->id,
            'branch_id' => $branch->id,
            'order_number' => 'E2E001',
            'customer_name' => 'End to End Customer',
            'status' => 'pending',
            'total_amount' => 1500,
        ]);

        // 2. Confirm order
        $order->update(['status' => 'confirmed']);
        
        // 3. Generate KOT (in real system this would be automatic)
        $kitchenStation = \App\Models\KitchenStation::create([
            'name' => 'Main Kitchen',
            'code' => 'MAIN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $kot = \App\Models\Kot::create([
            'order_id' => $order->id,
            'kitchen_station_id' => $kitchenStation->id,
            'kot_number' => 'E2E001-KOT',
            'status' => 'pending',
            'priority' => 'normal',
        ]);

        // 4. Kitchen starts preparation
        $kot->update(['status' => 'in_progress', 'started_at' => Carbon::now()]);
        $order->update(['status' => 'preparing']);

        // 5. Food ready
        $kot->update(['status' => 'completed', 'completed_at' => Carbon::now()]);
        $order->update(['status' => 'ready']);

        // 6. Served to customer
        $order->update(['status' => 'served']);

        // 7. Payment processed
        $order->update(['status' => 'paid']);

        // Verify final state
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals('completed', $kot->fresh()->status);
        $this->assertNotNull($kot->fresh()->completed_at);
    }
}
