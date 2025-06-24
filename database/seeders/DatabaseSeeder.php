<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\CustomRole;
use App\Models\User;
use App\Models\Employee;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\ItemMaster;
use App\Models\Permission;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function run(): void
    {
        $this->faker = \Faker\Factory::create();
        // Clear existing data first
        DB::statement('TRUNCATE tables RESTART IDENTITY CASCADE;');

        // Run basic seeders first
        $this->call([
            SubscriptionPlanSeeder::class, 
            EnhancedPermissionSeeder::class, 
            AdminPermissionSeeder::class,
            OrganizationSeeder::class,
            BranchSeeder::class,
            LoginSeeder::class,
            ItemCategorySeeder::class,
            AdminSeeder::class,            
            SuperAdminSeeder::class,
            ModulesTableSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        // Get the created subscription plans for factory data
        $plans = \App\Models\SubscriptionPlan::all();

        
        $planIds = $plans->pluck('id')->toArray();

        
        Organization::factory(5)->create()->each(function ($organization) {
            $this->command->info("🏢 Creating data for: {$organization->name}");
            
            // Create branches first
            $branches = Branch::factory(3)->create(['organization_id' => $organization->id]);
            
            // Create menu categories
            $menuCategories = MenuCategory::factory(3)->create([
                'organization_id' => $organization->id,
                'branch_id' => $branches->random()->id
            ]);
            
            // Create item masters (inventory items)
            $itemMasters = ItemMaster::factory(10)->create([
                'organization_id' => $organization->id, 
                'branch_id' => $branches->random()->id,
                'is_menu_item' => true
            ]);
            
            // Create menu items linked to item masters
            $menuItems = collect();
            foreach ($itemMasters->take(8) as $itemMaster) {
                $menuItem = MenuItem::factory()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $itemMaster->branch_id,
                    'menu_category_id' => $menuCategories->random()->id,
                    'item_master_id' => $itemMaster->id,
                    'name' => $itemMaster->name,
                    'price' => $itemMaster->selling_price,
                    'description' => $itemMaster->description ?? "Delicious {$itemMaster->name}"
                ]);
                $menuItems->push($menuItem);
            }
            
            // Create orders with proper menu items
            $orders = Order::factory(5)->create([
                'branch_id' => $branches->random()->id,
                'organization_id' => $organization->id
            ]);
            
            // Create order items for each order
            $orders->each(function ($order) use ($menuItems, $itemMasters) {
                $orderMenuItems = $menuItems->where('branch_id', $order->branch_id)->take(3);
                
                if ($orderMenuItems->isEmpty()) {
                    $orderMenuItems = $menuItems->take(3);
                }
                
                foreach ($orderMenuItems as $menuItem) {
                    $inventoryItem = $itemMasters->find($menuItem->item_master_id);
                    
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'inventory_item_id' => $inventoryItem?->id,
                        'item_name' => $menuItem->name,
                        'unit_price' => $menuItem->price,
                        'quantity' => $this->faker->numberBetween(1, 3)
                    ]);
                }
                
                // Update order total
                $orderTotal = $order->orderItems()->sum('total_price');
                $order->update(['total_amount' => $orderTotal]);
            });
            $gtns = GoodsTransferNote::factory(2)->create(['organization_id' => $organization->id, 'from_branch_id' => $branches->random()->id, 'to_branch_id' => $branches->random()->id]);
            $gtns->each(function ($gtn) {
                GoodsTransferItem::factory(3)->create(['gtn_id' => $gtn->gtn_id]);
            });
            $grns = GrnMaster::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $grns->each(function ($grn) {
                GrnItem::factory(3)->create(['grn_id' => $grn->grn_id]);
            });
            
            Permission::factory(2)->create();
            $pos = PurchaseOrder::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $pos->each(function ($po) {
                PurchaseOrderItem::factory(3)->create(['po_id' => $po->po_id]);
            });
            Payment::factory(2)->create();
            PaymentAllocation::factory(2)->create();
        });

        // Display success message
        $this->displaySuccessMessage();
    }

    private function displaySuccessMessage(): void
    {
        $this->command->newLine();
        $this->command->getOutput()->writeln('<fg=white;bg=green> ✅ DATABASE SEEDING COMPLETED SUCCESSFULLY! </fg=white;bg=green>');
        $this->command->newLine();
        
        $this->command->info('🎉 <fg=green>Database has been seeded with sample data!</fg=green>');
        $this->command->newLine();
        
        $this->command->line('<fg=cyan>📊 Summary of created records:</fg=cyan>');
        $this->command->line('   • Organizations: ' . \App\Models\Organization::count());
        $this->command->line('   • Subscription Plans: ' . \App\Models\SubscriptionPlan::count());
        $this->command->line('   • Branches: ' . \App\Models\Branch::count());
        $this->command->line('   • Employees: ' . \App\Models\Employee::count());
        $this->command->line('   • Menu Items: ' . \App\Models\MenuItem::count());
        $this->command->line('   • Orders: ' . \App\Models\Order::count());
        $this->command->line('   • Reservations: ' . \App\Models\Reservation::count());
        $this->command->line('   • Suppliers: ' . \App\Models\Supplier::count());
        $this->command->line('   • Purchase Orders: ' . \App\Models\PurchaseOrder::count());
        $this->command->line('   • GRNs: ' . \App\Models\GrnMaster::count());
        $this->command->line('   • Payments: ' . \App\Models\Payment::count());
        
        $this->command->newLine();
        $this->command->line('<fg=yellow>🚀 Next steps:</fg=yellow>');
        $this->command->line('   1. Visit your application dashboard');
        $this->command->line('   2. Check views for data display issues');
        $this->command->line('   3. Use @dd() for debugging any unexpected values');
        $this->command->line('   4. Add ?debug=1 to URLs for detailed debugging');
        
        $this->command->newLine();
        $this->command->line('<fg=green>✨ Your Restaurant Management System is ready to go!</fg=green>');
        $this->command->newLine();
    }
}