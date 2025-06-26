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

    public function run(): void
    {
        // Clear existing data first
        DB::statement('TRUNCATE tables RESTART IDENTITY CASCADE;');

        // Run basic seeders first
        $this->call([
            SubscriptionPlanSeeder::class, // Creates subscription plans
            EnhancedPermissionSeeder::class, // Creates roles and permissions for automation
            OrganizationSeeder::class,
            BranchSeeder::class,
            // TableSeeder::class,
            LoginSeeder::class,
            // SupplierSeeder::class,
            ItemCategorySeeder::class,
            // ItemMasterSeeder::class,
            AdminSeeder::class,
            // ReservationSeeder::class,
            // PurchaseOrderSeeder::class,
            // GRNSeeder::class,
            // SupplierPaymentSeeder::class,
            // ItemTransactionSeeder::class,
            //EmployeeSeeder::class,
            // ModulePermissionSeeder::class,
            SuperAdminSeeder::class,
            ModulesTableSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,


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


        // Get the created subscription plans for factory data
        $plans = \App\Models\SubscriptionPlan::all();


        $planIds = $plans->pluck('id')->toArray();


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
