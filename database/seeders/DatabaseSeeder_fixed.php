<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
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
            'grn_items', 'grn_masters', 'purchase_order_items', 'purchase_orders',
            'item_masters', 'kitchen_stations', 'branches'
        ];
        
        foreach ($tablesToClear as $table) {
            try {
                if ($databaseType === 'mysql') {
                    DB::table($table)->truncate();
                } else {
                    // For PostgreSQL, use DELETE with RESTART IDENTITY
                    DB::table($table)->delete();
                    DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
                }
                $this->command->info("✅ Cleared table: {$table}");
            } catch (\Exception $e) {
                $this->command->warn("⚠️ Could not clear table {$table}: {$e->getMessage()}");
            }
        }
        
        // Re-enable foreign key checks
        if ($databaseType === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        // Run our stable, tested seeders only
        $this->command->info('🌱 Running core seeders...');
        
        // 1. Organizations first (creates kitchen stations automatically)
        $this->call(OrganizationSeeder::class);
        
        // 2. Branches (creates additional kitchen stations)
        $this->call(BranchSeeder::class);
        
        // 3. Item masters with valid references
        $this->call(ItemMasterSeeder::class);
        
        $this->command->info('✅ Core database seeding completed successfully!');
        $this->command->info('📊 Current state:');
        
        // Show summary
        $this->command->info('  - Organizations: ' . Organization::count());
        $this->command->info('  - Branches: ' . Branch::count());
        $this->command->info('  - Kitchen Stations: ' . \App\Models\KitchenStation::count());
        $this->command->info('  - Item Masters: ' . ItemMaster::count());
    }
}
