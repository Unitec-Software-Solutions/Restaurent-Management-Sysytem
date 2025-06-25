<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
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
        $this->command->info('🌱 Starting comprehensive database seeding...');
        
        // Clear existing data first (but safely)
        $this->command->info('🧹 Clearing existing data...');
        
        // Use database-agnostic approach for disabling foreign key checks
        $databaseType = DB::connection()->getDriverName();
        
        if ($databaseType === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($databaseType === 'pgsql') {
            // PostgreSQL doesn't have a global foreign key disable, so we'll truncate in correct order
            $this->command->info('🔄 Using PostgreSQL-compatible truncation...');
        }
          // Clear tables in dependency order (reverse of creation order)
        $tablesToClear = [
            'payment_allocations', 'order_items', 'orders', 'menu_items', 
            'menu_categories', 'goods_transfer_items', 'goods_transfer_notes',
            'grn_items', 'grn_masters', 'purchase_order_items', 'purchase_orders',            'item_masters', 'item_categories', 'kitchen_stations', 'branches', 
            'organizations', 'admins', 'users', 'roles'
        ];
        
        foreach ($tablesToClear as $table) {
            try {
                // Check if table exists before attempting to clear
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    $this->command->warn("⚠️ Table {$table} does not exist, skipping...");
                    continue;
                }
                
                if ($databaseType === 'mysql') {
                    DB::table($table)->truncate();
                } else {
                    // For PostgreSQL, use TRUNCATE CASCADE to handle foreign keys
                    DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
                }
                $this->command->info("✅ Cleared table: {$table}");
            } catch (\Exception $e) {
                $this->command->warn("⚠️ Could not clear table {$table}: {$e->getMessage()}");
                // For PostgreSQL, try a force delete approach
                if ($databaseType === 'pgsql') {
                    try {
                        DB::table($table)->delete();
                        $this->command->info("✅ Force cleared table: {$table}");
                    } catch (\Exception $innerE) {
                        $this->command->warn("⚠️ Force clear also failed for {$table}: {$innerE->getMessage()}");
                    }
                }
            }
        }
        
        // Re-enable foreign key checks
        if ($databaseType === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
          // Run our stable, tested seeders only
        $this->command->info('🌱 Running core seeders...');
        
        // 0. Create super admin first
        $this->call(SuperAdminSeeder::class);
        
        // 0.1. Also create super admin in users table (LoginSeeder)
        $this->call(LoginSeeder::class);
          // 1. Organizations first (creates kitchen stations automatically)
        $this->call(OrganizationSeeder::class);
        
        // 2. Branches (creates additional kitchen stations)
        $this->call(BranchSeeder::class);
        
        // 2.1. Create roles for the organizations
        $this->call(RoleSeeder::class);
        
        // 3. Item categories (required for item masters)
        $this->call(ItemCategorySeeder::class);
        
        // 4. Item masters with valid references
        $this->call(ItemMasterSeeder::class);
          $this->command->info('✅ Core database seeding completed successfully!');
        $this->command->info('📊 Current state:');
        
        // Show summary
        $this->command->info('  - Organizations: ' . Organization::count());
        $this->command->info('  - Branches: ' . Branch::count());
        $this->command->info('  - Kitchen Stations: ' . \App\Models\KitchenStation::count());
        $this->command->info('  - Item Categories: ' . \App\Models\ItemCategory::count());
        $this->command->info('  - Item Masters: ' . ItemMaster::count());
        $this->command->info('  - Admin Users: ' . \App\Models\Admin::count());
        $this->command->info('  - Regular Users: ' . \App\Models\User::count());
    }
}
