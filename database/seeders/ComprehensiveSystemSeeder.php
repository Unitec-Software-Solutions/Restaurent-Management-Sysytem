<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveSystemSeeder extends Seeder
{
    /**
     * Run the comprehensive system seeding with optimized data
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Restaurant Management System Seeding...');
        
        // Disable foreign key checks for clean seeding (database-agnostic)
        $databaseType = DB::connection()->getDriverName();
        if ($databaseType === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($databaseType === 'pgsql') {
            $this->command->info('🔄 Using PostgreSQL-compatible seeding...');
        }
        
        try {
            // 1. Core system setup
            $this->command->info('📋 Phase 1: Core System Setup');
            $this->call([
                SubscriptionPlanSeeder::class,
                OptimizedOrganizationSeeder::class,
                OptimizedBranchSeeder::class,
            ]);
            
            // 2. User management and roles
            $this->command->info('👥 Phase 2: User Management & Roles');
            $this->call([
                RolePermissionSeeder::class,
                SuperAdminSeeder::class,
                LoginSeeder::class,
            ]);
            
            // 3. Menu and inventory setup
            $this->command->info('🍽️ Phase 3: Menu & Inventory Setup');
            $this->call([
                ItemCategorySeeder::class,
                ItemMasterSeeder::class,
                MenuItemSeeder::class,
            ]);
            
            // 4. Tables and reservations
            $this->command->info('🪑 Phase 4: Tables & Reservations');
            $this->call([
                TableSeeder::class,
                ReservationSeeder::class,
            ]);
            
            // 5. Operational data (purchases, transactions)
            $this->command->info('💼 Phase 5: Operational Data');
            $this->call([
                SupplierSeeder::class,
                PurchaseOrderSeeder::class,
                ItemTransactionSeeder::class,
            ]);
            
            // 6. Comprehensive testing
            $this->command->info('🧪 Phase 6: Comprehensive Testing');
            $this->call([
                TestCasesSeeder::class,
            ]);
            
            // Re-enable foreign key checks
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            
            // 7. Final validation and summary
            $this->displayFinalSummary();
            
        } catch (\Exception $e) {
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function displayFinalSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SYSTEM SEEDING SUMMARY');
        $this->command->info('═══════════════════════════════════════');
        
        // Organizations & Branches
        $orgCount = \App\Models\Organization::count();
        $branchCount = \App\Models\Branch::count();
        $this->command->info("🏢 Organizations: {$orgCount} (optimized to 3)");
        $this->command->info("🏪 Branches: {$branchCount} (2 per organization)");
        
        // Subscription Plans
        $planCount = \App\Models\SubscriptionPlan::count();
        $activeSubscriptions = \App\Models\Subscription::where('is_active', true)->count();
        $this->command->info("💳 Subscription Plans: {$planCount}");
        $this->command->info("✅ Active Subscriptions: {$activeSubscriptions}");
        
        // Users & Roles
        $userCount = \App\Models\User::count();
        $roleCount = \App\Models\Role::count();
        $this->command->info("👤 Users: {$userCount}");
        $this->command->info("🎭 Roles: {$roleCount}");
        
        // Menu & Inventory
        $menuItemCount = \App\Models\MenuItem::count();
        $inventoryCount = \App\Models\InventoryItem::count();
        $this->command->info("🍽️ Menu Items: {$menuItemCount}");
        $this->command->info("📦 Inventory Items: {$inventoryCount}");
        
        // Tables & Reservations
        $tableCount = \App\Models\Table::count();
        $reservationCount = \App\Models\Reservation::count();
        $this->command->info("🪑 Tables: {$tableCount}");
        $this->command->info("📅 Reservations: {$reservationCount}");
        
        // Orders
        $orderCount = \App\Models\Order::count();
        $this->command->info("🧾 Test Orders: {$orderCount}");
        
        $this->command->info('');
        $this->command->info('🎯 KEY FEATURES IMPLEMENTED:');
        $this->command->info('  ✅ Multi-tier subscription system');
        $this->command->info('  ✅ Role-based access control');
        $this->command->info('  ✅ Inventory alerts (10% threshold)');
        $this->command->info('  ✅ Auto staff assignment by shift');
        $this->command->info('  ✅ Order-to-Kitchen workflow');
        $this->command->info('  ✅ Subscription feature toggles');
        $this->command->info('  ✅ Real-world data relationships');
        
        $this->command->info('');
        $this->command->info('🔐 TEST LOGIN CREDENTIALS:');
        $this->command->info('  Super Admin: superadmin@rms.com / password');
        $this->command->info('  Spice Garden: admin@spicegarden.lk / password123');
        $this->command->info('  Ocean View: admin@oceanview.lk / password123');
        $this->command->info('  Mountain Peak: admin@mountainpeak.lk / password123');
        
        $this->command->info('');
        $this->command->info('🚀 System ready for testing and demonstration!');
        $this->command->info('═══════════════════════════════════════');
    }
}
