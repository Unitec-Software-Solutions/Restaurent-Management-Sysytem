<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Role;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\ItemMaster;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestCasesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Starting comprehensive test case seeding...');

        // Test subscription tier functionality
        $this->testSubscriptionTiers();
        
        // Test role permissions
        $this->testRolePermissions();
        
        // Test critical workflows
        $this->testOrderToKitchenFlow();
        
        // Test inventory management
        $this->testInventoryManagement();
        
        // Test reservation system
        $this->testReservationSystem();
        
        // Test subscription limitations
        $this->testSubscriptionLimitations();

        $this->command->info('✅ Test case seeding completed successfully!');
    }

    protected function testSubscriptionTiers(): void
    {
        $this->command->info('  🔄 Testing subscription tiers...');
        
        $plans = SubscriptionPlan::all();
        
        foreach ($plans as $plan) {
            $org = Organization::where('subscription_plan_id', $plan->id)->first();
            if (!$org) continue;

            // Test feature access based on subscription
            $hasAdvancedFeatures = $org->hasFeature('split_billing');
            $expectedAccess = $plan->name === 'Pro';
            
            if ($hasAdvancedFeatures === $expectedAccess) {
                $this->command->info("    ✅ {$plan->name} plan feature access working correctly");
            } else {
                $this->command->error("    ❌ {$plan->name} plan feature access failed");
            }
        }
    }

    protected function testRolePermissions(): void
    {
        $this->command->info('  🔄 Testing role permissions...');
        
        $organizations = Organization::with('branches')->get();
        
        foreach ($organizations as $org) {
            foreach ($org->branches as $branch) {
                // Create test users with different roles
                $this->createTestUsersForBranch($branch);
            }
        }
        
        $this->command->info('    ✅ Role-based users created for testing');
    }

    protected function createTestUsersForBranch(Branch $branch): void
    {
        $roles = [
            'manager' => ['name' => 'Branch Manager', 'permissions' => ['view_reports', 'manage_staff', 'process_refunds']],
            'cashier' => ['name' => 'Cashier', 'permissions' => ['process_payments', 'handle_orders']],
            'waiter' => ['name' => 'Waiter', 'permissions' => ['take_orders', 'view_menu']],
            'chef' => ['name' => 'Chef', 'permissions' => ['view_kitchen_orders', 'update_order_status']],
        ];

        foreach ($roles as $roleKey => $roleData) {
            // Create or get role
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'guard_name' => 'web',
            ]);

            // Create test user
            $user = User::create([
                'name' => "{$roleData['name']} - {$branch->name}",
                'email' => strtolower($roleKey) . ".{$branch->id}@test.com",
                'password' => Hash::make('password'),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'role_id' => $role->id,
                'is_active' => true,
            ]);

            $user->assignRole($role);
        }
    }

    protected function testOrderToKitchenFlow(): void
    {
        $this->command->info('  🔄 Testing Order-to-Kitchen flow...');
        
        $branches = Branch::with('organization')->get();
        
        foreach ($branches as $branch) {
            // Create menu items if they don't exist
            $this->createTestMenuItems($branch);
            
            // Create test table
            $table = Table::firstOrCreate([
                'branch_id' => $branch->id,
                'number' => 1,
            ], [
                'capacity' => 4,
                'status' => 'available',
                'location' => 'Main Hall',
            ]);

            // Create test order
            $order = Order::create([
                'branch_id' => $branch->id,
                'table_id' => $table->id,
                'order_number' => 'TEST-' . $branch->id . '-' . now()->format('His'),
                'status' => 'pending',
                'order_type' => 'dine_in',
                'total_amount' => 0,
                'created_by' => 1, // Assuming user ID 1 exists
            ]);

            // Add order items
            $menuItems = MenuItem::whereHas('menuCategory', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->take(3)->get();

            $totalAmount = 0;
            foreach ($menuItems as $item) {
                $quantity = rand(1, 3);
                $price = $item->price * $quantity;
                $totalAmount += $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $item->price,
                    'total_price' => $price,
                    'status' => 'pending',
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);
            
            $this->command->info("    ✅ Test order created for {$branch->name}: #{$order->order_number}");
        }
    }

    protected function createTestMenuItems(Branch $branch): void
    {
        // Create menu category
        $category = MenuCategory::firstOrCreate([
            'branch_id' => $branch->id,
            'name' => 'Test Menu',
        ], [
            'description' => 'Test menu items for workflow testing',
            'is_active' => true,
        ]);

        // Create menu items
        $items = [
            ['name' => 'Rice & Curry', 'price' => 450],
            ['name' => 'Fried Rice', 'price' => 550],
            ['name' => 'Kottu', 'price' => 600],
            ['name' => 'Fresh Juice', 'price' => 250],
        ];

        foreach ($items as $itemData) {
            MenuItem::firstOrCreate([
                'menu_category_id' => $category->id,
                'name' => $itemData['name'],
            ], [
                'price' => $itemData['price'],
                'is_active' => true,
                'description' => 'Test menu item',
            ]);
        }
    }

    protected function testInventoryManagement(): void
    {
        $this->command->info('  🔄 Testing inventory management...');
        
        $branches = Branch::all();
        
        foreach ($branches as $branch) {
            // Create test inventory items
            $this->createTestInventoryItems($branch);
        }
        
        $this->command->info('    ✅ Test inventory items created');
    }

    protected function createTestInventoryItems(Branch $branch): void
    {
        $items = [
            ['name' => 'Rice', 'quantity' => 50, 'par_level' => 100, 'unit' => 'kg'],
            ['name' => 'Chicken', 'quantity' => 15, 'par_level' => 30, 'unit' => 'kg'],
            ['name' => 'Vegetables', 'quantity' => 8, 'par_level' => 25, 'unit' => 'kg'], // This will trigger low stock
            ['name' => 'Cooking Oil', 'quantity' => 0, 'par_level' => 10, 'unit' => 'liters'], // This will trigger out of stock
        ];

        foreach ($items as $itemData) {
            // Create item master if it doesn't exist
            $itemMaster = ItemMaster::firstOrCreate([
                'name' => $itemData['name'],
                'organization_id' => $branch->organization_id,
            ], [
                'code' => strtoupper(substr($itemData['name'], 0, 3)) . rand(100, 999),
                'unit' => $itemData['unit'],
                'is_active' => true,
            ]);

            // Create inventory item
            InventoryItem::firstOrCreate([
                'branch_id' => $branch->id,
                'item_master_id' => $itemMaster->id,
            ], [
                'quantity' => $itemData['quantity'],
                'par_level' => $itemData['par_level'],
                'reorder_level' => $itemData['par_level'] * 0.2, // 20% of par level
                'cost_per_unit' => rand(50, 500),
            ]);
        }
    }

    protected function testReservationSystem(): void
    {
        $this->command->info('  🔄 Testing reservation system...');
        
        $branches = Branch::all();
        
        foreach ($branches as $branch) {
            // Create tables if they don't exist
            $this->createTestTables($branch);
            
            // Create test reservations
            $this->createTestReservations($branch);
        }
        
        $this->command->info('    ✅ Test reservations created');
    }

    protected function createTestTables(Branch $branch): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate([
                'branch_id' => $branch->id,
                'number' => $i,
            ], [
                'capacity' => rand(2, 8),
                'status' => 'available',
                'location' => $i <= 5 ? 'Main Hall' : 'Garden Area',
            ]);
        }
    }

    protected function createTestReservations(Branch $branch): void
    {
        $tables = Table::where('branch_id', $branch->id)->get();
        
        foreach ($tables->take(3) as $table) {
            Reservation::create([
                'branch_id' => $branch->id,
                'table_id' => $table->id,
                'customer_name' => 'Test Customer ' . $table->number,
                'customer_phone' => '+94 77 ' . rand(1000000, 9999999),
                'party_size' => rand(2, $table->capacity),
                'date' => now()->addDays(rand(1, 7))->format('Y-m-d'),
                'start_time' => now()->addHours(rand(1, 8))->format('H:i:s'),
                'end_time' => now()->addHours(rand(9, 12))->format('H:i:s'),
                'status' => 'confirmed',
                'reservation_fee' => $branch->reservation_fee ?? 500,
            ]);
        }
    }

    protected function testSubscriptionLimitations(): void
    {
        $this->command->info('  🔄 Testing subscription limitations...');
        
        $basicOrg = Organization::whereHas('plan', function($q) {
            $q->where('name', 'Basic');
        })->first();
        
        if ($basicOrg) {
            // Test branch limitation
            $branchCount = $basicOrg->branches()->count();
            $maxBranches = $basicOrg->plan->max_branches ?? 2;
            
            if ($branchCount <= $maxBranches) {
                $this->command->info("    ✅ Basic plan branch limit respected ({$branchCount}/{$maxBranches})");
            } else {
                $this->command->error("    ❌ Basic plan branch limit exceeded ({$branchCount}/{$maxBranches})");
            }
            
            // Test employee limitation
            $employeeCount = $basicOrg->users()->count();
            $maxEmployees = $basicOrg->plan->max_employees ?? 10;
            
            if ($employeeCount <= $maxEmployees) {
                $this->command->info("    ✅ Basic plan employee limit respected ({$employeeCount}/{$maxEmployees})");
            } else {
                $this->command->error("    ❌ Basic plan employee limit exceeded ({$employeeCount}/{$maxEmployees})");
            }
        }
    }
}
