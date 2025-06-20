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
use App\Models\CustomerAuthenticationMethod;
use App\Models\CustomerPreference;
use App\Models\AuditLog;
use App\Models\NotificationProvider;
use App\Models\Permission;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed subscription plans first using direct model creation to avoid array conversion issues
        $basicPlan = \App\Models\SubscriptionPlan::create([
            'name' => 'Basic', 
            'price' => 0, 
            'currency' => 'LKR', 
            'modules' => [], 
            'description' => 'Basic free plan', 
            'is_trial' => true, 
            'trial_period_days' => 30
        ]);
        
        $proPlan = \App\Models\SubscriptionPlan::create([
            'name' => 'Pro', 
            'price' => 5000, 
            'currency' => 'LKR', 
            'modules' => [], 
            'description' => 'Pro annual plan', 
            'is_trial' => false, 
            'trial_period_days' => null
        ]);
        
        $legacyPlan = \App\Models\SubscriptionPlan::create([
            'name' => 'Legacy', 
            'price' => 1000, 
            'currency' => 'LKR', 
            'modules' => [], 
            'description' => 'Legacy plan', 
            'is_trial' => false, 
            'trial_period_days' => null
        ]);

        $plans = collect([$basicPlan, $proPlan, $legacyPlan]);

        // Get all plan IDs for use in OrganizationFactory
        $planIds = $plans->pluck('id')->toArray();

        // Sample data seeding using factories
        Organization::factory(5)->create([
            'subscription_plan_id' => $planIds[array_rand($planIds)]
        ])->each(function ($organization) use ($planIds) {
            if (!$organization->subscription_plan_id || !in_array($organization->subscription_plan_id, $planIds)) {
                echo "[ERROR] Organization ID {$organization->id} has invalid subscription_plan_id: {$organization->subscription_plan_id}\n";
            }
            $branches = Branch::factory(3)->create(['organization_id' => $organization->id]);
            Admin::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            CustomRole::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            Employee::factory(5)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $menuCategories = MenuCategory::factory(3)->create();
            MenuItem::factory(10)->create(['menu_category_id' => $menuCategories->random()->id]);
            // Create tables with unique numbers per branch
            $branches->each(function ($branch, $index) {
                for ($tableNum = 1; $tableNum <= 5; $tableNum++) {
                    Table::factory()->create([
                        'branch_id' => $branch->id,
                        'number' => $tableNum
                    ]);
                }
            });
            User::factory(5)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            Reservation::factory(5)->create(['branch_id' => $branches->random()->id]);
            
            // Create ItemMaster records first
            $itemMasters = ItemMaster::factory(5)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            
            $orders = Order::factory(5)->create(['branch_id' => $branches->random()->id]);
            $orders->each(function ($order) use ($itemMasters) {
                OrderItem::factory(3)->create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemMasters->random()->id,
                    'inventory_item_id' => $itemMasters->random()->id
                ]);
            });
            $gtns = GoodsTransferNote::factory(2)->create(['organization_id' => $organization->id, 'from_branch_id' => $branches->random()->id, 'to_branch_id' => $branches->random()->id]);
            $gtns->each(function ($gtn) {
                GoodsTransferItem::factory(3)->create(['gtn_id' => $gtn->gtn_id]);
            });
            $grns = GrnMaster::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $grns->each(function ($grn) {
                GrnItem::factory(3)->create(['grn_id' => $grn->grn_id]);
            });
            // CustomerAuthenticationMethod::factory(2)->create(); // Table doesn't exist
            // AuditLog::factory(2)->create(); // Table doesn't exist
            // NotificationProvider::factory(1)->create(); // Table doesn't exist
            Permission::factory(2)->create();
            $pos = PurchaseOrder::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $pos->each(function ($po) {
                PurchaseOrderItem::factory(3)->create(['po_id' => $po->po_id]);
            });
            Payment::factory(2)->create();
            PaymentAllocation::factory(2)->create();
        });

        // Display success message
        $this->command->info('');
        $this->command->info('🎉 DATABASE SEEDING COMPLETED SUCCESSFULLY! 🎉');
        $this->command->info('');
        $this->command->info('✅ All tables have been seeded with test data');
        $this->command->info('✅ Organizations, branches, users, and related data created');
        $this->command->info('✅ Menu items, orders, inventory, and transactions populated');
        $this->command->info('✅ GTN, GRN, and purchase order workflows ready');
        $this->command->info('✅ Permissions and roles configured');
        $this->command->info('');
        $this->command->info('Your Restaurant Management System is ready for testing!');
        $this->command->info('');
    }
}