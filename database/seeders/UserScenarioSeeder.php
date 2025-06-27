<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Role;
use App\Services\MenuSystemService;
use App\Services\OrderManagementService;
use Carbon\Carbon;

class UserScenarioSeeder extends Seeder
{
    private MenuSystemService $menuService;
    private OrderManagementService $orderService;

    public function __construct()
    {
        $this->menuService = new MenuSystemService();
        $this->orderService = new OrderManagementService();
    }

    public function run(): void
    {
        $this->command->info('🧪 Seeding User Scenario Test Data...');
        
        $this->seedGuestScenarios();
        $this->seedPermissionScenarios();
        $this->seedMenuScenarios();
        $this->seedOrderScenarios();
        $this->seedEdgeCaseScenarios();
        
        $this->command->info('✅ User scenario seeding completed!');
    }

    private function seedGuestScenarios(): void
    {
        $this->command->line('  👥 Creating guest access scenarios...');
        
        $branches = Branch::take(3)->get();
        
        foreach ($branches as $branch) {
            // Create varied menu for guest viewing
            $this->createVariedMenu($branch);
            
            // Create sample guest orders
            $this->createGuestOrders($branch);
        }
    }

    private function seedPermissionScenarios(): void
    {
        $this->command->line('  🔐 Creating permission test scenarios...');
        
        $organizations = Organization::take(2)->get();
        
        foreach ($organizations as $org) {
            foreach ($org->branches as $branch) {
                $this->createPermissionTestUsers($org, $branch);
            }
        }
    }

    private function seedMenuScenarios(): void
    {
        $this->command->line('  🍽️ Creating menu system scenarios...');
        
        $branches = Branch::take(2)->get();
        
        foreach ($branches as $branch) {
            // Create time-based availability scenarios
            $this->createTimeBasedMenus($branch);
            
            // Create special menu scenarios
            $this->createSpecialMenus($branch);
            
            // Create inventory-dependent scenarios
            $this->createInventoryDependentMenus($branch);
        }
    }

    private function seedOrderScenarios(): void
    {
        $this->command->line('  📦 Creating order management scenarios...');
        
        $branches = Branch::take(2)->get();
        
        foreach ($branches as $branch) {
            $this->createOrderWorkflowScenarios($branch);
        }
    }

    private function seedEdgeCaseScenarios(): void
    {
        $this->command->line('  ⚡ Creating edge case scenarios...');
        
        $this->createStockOutageScenarios();
        $this->createHighVolumeOrderScenarios();
        $this->createPermissionEdgeCases();
    }

    private function createVariedMenu(Branch $branch): void
    {
        $menuItems = [
            [
                'name' => 'Breakfast Special',
                'price' => 850,
                'available_from' => '06:00:00',
                'available_until' => '11:00:00',
                'day_availability' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
            ],
            [
                'name' => 'Lunch Combo',
                'price' => 1200,
                'available_from' => '11:30:00',
                'available_until' => '16:00:00'
            ],
            [
                'name' => 'Weekend Brunch',
                'price' => 1500,
                'day_availability' => ['saturday', 'sunday'],
                'available_from' => '09:00:00',
                'available_until' => '14:00:00'
            ],
            [
                'name' => 'Dinner Special',
                'price' => 2000,
                'available_from' => '18:00:00',
                'available_until' => '22:00:00'
            ]
        ];

        foreach ($menuItems as $itemData) {
            MenuItem::create([
                'branch_id' => $branch->id,
                'name' => $itemData['name'],
                'description' => "Delicious {$itemData['name']} prepared fresh",
                'price' => $itemData['price'],
                'is_active' => true,
                'available_from' => $itemData['available_from'] ?? null,
                'available_until' => $itemData['available_until'] ?? null,
                'day_availability' => $itemData['day_availability'] ?? null
            ]);
        }
    }

    private function createGuestOrders(Branch $branch): void
    {
        // Create sample guest orders for testing
        for ($i = 1; $i <= 3; $i++) {
            Order::create([
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_number' => "GUEST-{$branch->id}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'customer_name' => "Guest Customer {$i}",
                'customer_phone' => "077123456{$i}",
                'status' => 'pending',
                'total_amount' => rand(800, 3000),
                'notes' => 'Guest order - no account required'
            ]);
        }
    }

    private function createPermissionTestUsers(Organization $org, Branch $branch): void
    {
        $testUsers = [
            [
                'role' => 'org_admin',
                'name' => 'Org Admin Test User',
                'email' => "orgadmin.test.{$org->id}@example.com"
            ],
            [
                'role' => 'branch_admin',
                'name' => 'Branch Admin Test User',
                'email' => "branchadmin.test.{$branch->id}@example.com"
            ],
            [
                'role' => 'cashier',
                'name' => 'Cashier Test User',
                'email' => "cashier.test.{$branch->id}@example.com"
            ],
            [
                'role' => 'waiter',
                'name' => 'Waiter Test User',
                'email' => "waiter.test.{$branch->id}@example.com"
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('password123'),
                'organization_id' => $org->id,
                'branch_id' => $branch->id,
                'is_active' => true
            ]);

            $role = Role::where('name', $userData['role'])
                ->where('organization_id', $org->id)
                ->first();

            if ($role) {
                $user->assignRole($role);
            }
        }
    }

    private function createTimeBasedMenus(Branch $branch): void
    {
        // Create items with specific time availability
        $timeItems = [
            ['name' => 'Early Bird Coffee', 'from' => '05:00:00', 'until' => '08:00:00'],
            ['name' => 'Happy Hour Drinks', 'from' => '17:00:00', 'until' => '19:00:00'],
            ['name' => 'Late Night Snacks', 'from' => '22:00:00', 'until' => '02:00:00']
        ];

        foreach ($timeItems as $item) {
            MenuItem::create([
                'branch_id' => $branch->id,
                'name' => $item['name'],
                'price' => rand(500, 1500),
                'available_from' => $item['from'],
                'available_until' => $item['until'],
                'is_active' => true
            ]);
        }
    }

    private function createSpecialMenus(Branch $branch): void
    {
        // Create special menus for upcoming dates
        $dates = [
            Carbon::now()->addDays(1),
            Carbon::now()->addDays(7),
            Carbon::now()->addDays(14)
        ];

        foreach ($dates as $date) {
            $this->menuService->createSpecialMenu($branch, $date, [
                [
                    'name' => "Special for {$date->format('M j')}",
                    'description' => 'Limited time special menu item',
                    'price' => rand(1800, 3500),
                    'display_order' => 1
                ]
            ]);
        }
    }

    private function createInventoryDependentMenus(Branch $branch): void
    {
        // Create menu items that depend on inventory
        MenuItem::create([
            'branch_id' => $branch->id,
            'name' => 'Fresh Fish of the Day',
            'description' => 'Subject to availability',
            'price' => 2500,
            'requires_inventory_check' => true,
            'ingredients' => [
                ['item_master_id' => 1, 'required_quantity' => 1]
            ],
            'is_active' => true
        ]);
    }

    private function createOrderWorkflowScenarios(Branch $branch): void
    {
        $statusScenarios = ['pending', 'confirmed', 'preparing', 'ready', 'served'];

        foreach ($statusScenarios as $index => $status) {
            Order::create([
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_number' => "WORKFLOW-{$branch->id}-" . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'customer_name' => 'Workflow Test Customer ' . ($index + 1),
                'status' => $status,
                'total_amount' => rand(1000, 4000),
                'created_at' => Carbon::now()->subMinutes(($index + 1) * 15)
            ]);
        }
    }

    private function createStockOutageScenarios(): void
    {
        // Create scenarios where inventory is low/out for testing
        $branch = Branch::first();
        
        MenuItem::create([
            'branch_id' => $branch->id,
            'name' => 'Out of Stock Item',
            'price' => 1500,
            'requires_inventory_check' => true,
            'ingredients' => [
                ['item_master_id' => 999, 'required_quantity' => 1] // Non-existent item
            ],
            'is_active' => true
        ]);
    }

    private function createHighVolumeOrderScenarios(): void
    {
        // Create multiple orders to test high volume scenarios
        $branch = Branch::first();
        
        for ($i = 1; $i <= 20; $i++) {
            Order::create([
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_number' => "VOLUME-{$branch->id}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'customer_name' => "Volume Test Customer {$i}",
                'status' => collect(['pending', 'confirmed', 'preparing'])->random(),
                'total_amount' => rand(800, 5000),
                'created_at' => Carbon::now()->subMinutes(rand(1, 120))
            ]);
        }
    }

    private function createPermissionEdgeCases(): void
    {
        // Create edge case scenarios for permission testing
        $org = Organization::first();
        
        // User with no roles
        User::create([
            'name' => 'No Role User',
            'email' => 'norole@test.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org->id,
            'is_active' => true
        ]);

        // User with multiple conflicting roles
        $multiRoleUser = User::create([
            'name' => 'Multi Role User',
            'email' => 'multirole@test.com',
            'password' => bcrypt('password123'),
            'organization_id' => $org->id,
            'is_active' => true
        ]);

        $roles = Role::where('organization_id', $org->id)->take(2)->get();
        foreach ($roles as $role) {
            $multiRoleUser->assignRole($role);
        }
    }
}