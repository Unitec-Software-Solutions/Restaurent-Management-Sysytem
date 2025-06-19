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
        // Seed subscription plans first
        $plans = \App\Models\SubscriptionPlan::factory()->count(3)->create([
            ['name' => 'Basic', 'price' => 0, 'currency' => 'LKR', 'modules' => [], 'description' => 'Basic free plan', 'is_trial' => true, 'trial_period_days' => 30],
            ['name' => 'Pro', 'price' => 5000, 'currency' => 'LKR', 'modules' => [], 'description' => 'Pro annual plan', 'is_trial' => false, 'trial_period_days' => null],
            ['name' => 'Legacy', 'price' => 1000, 'currency' => 'LKR', 'modules' => [], 'description' => 'Legacy plan', 'is_trial' => false, 'trial_period_days' => null],
        ]);

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
            Table::factory(5)->create(['branch_id' => $branches->random()->id]);
            User::factory(5)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            Reservation::factory(5)->create(['branch_id' => $branches->random()->id]);
            $orders = Order::factory(5)->create(['branch_id' => $branches->random()->id]);
            $orders->each(function ($order) {
                OrderItem::factory(3)->create(['order_id' => $order->id]);
            });
            $gtns = GoodsTransferNote::factory(2)->create(['organization_id' => $organization->id, 'from_branch_id' => $branches->random()->id, 'to_branch_id' => $branches->random()->id]);
            $gtns->each(function ($gtn) {
                GoodsTransferItem::factory(3)->create(['gtn_id' => $gtn->id]);
            });
            $grns = GrnMaster::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $grns->each(function ($grn) {
                GrnItem::factory(3)->create(['grn_id' => $grn->id]);
            });
            ItemMaster::factory(5)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            CustomerAuthenticationMethod::factory(2)->create();
            AuditLog::factory(2)->create();
            NotificationProvider::factory(1)->create();
            Permission::factory(2)->create();
            $pos = PurchaseOrder::factory(2)->create(['organization_id' => $organization->id, 'branch_id' => $branches->random()->id]);
            $pos->each(function ($po) {
                PurchaseOrderItem::factory(3)->create(['po_id' => $po->id]);
            });
            Payment::factory(2)->create();
            PaymentAllocation::factory(2)->create();
        });
    }
}
