<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExhaustiveSystemSeeder2 extends Seeder
{
    /**
     * Run exhaustive seeding covering all possible restaurant management scenarios
     */
    public function run(): void
    {
        $this->command->info('🌟 Starting Exhaustive Restaurant Management System Seeding...');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        
        // Disable foreign key checks (database-agnostic)
        $databaseType = DB::connection()->getDriverName();
        if ($databaseType === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($databaseType === 'pgsql') {
            $this->command->info('🔄 Using PostgreSQL-compatible seeding...');
        }
        
        try {
            // Phase 1: Core Foundation
            $this->command->info('📋 Phase 1: Core Foundation & Subscription Plans');
            $this->call([
                ExhaustiveSubscriptionSeeder::class,
                ExhaustiveOrganizationSeeder::class,
                ExhaustiveBranchSeeder::class,
            ]);
            
            // Phase 2: User Management & Permissions
            $this->command->info('👥 Phase 2: User Management & Permission Systems');
            $this->call([
                ExhaustiveUserPermissionSeeder::class,
                ExhaustiveRoleSeeder::class,
            ]);
            
            // Phase 3: Menu Configuration & Versioning
            $this->command->info('🍽️ Phase 3: Menu Configuration & Time-Based Availability');
            $this->call([
                ExhaustiveMenuSeeder::class,
                ExhaustiveInventorySeeder::class,
            ]);
            
            // Phase 4: Order Lifecycle & Kitchen Operations
            $this->command->info('🛒 Phase 4: Order Lifecycle & Kitchen Operations');
            $this->call([
                ExhaustiveOrderSeeder::class,
                ExhaustiveKitchenWorkflowSeeder::class,
            ]);
            
            // Phase 5: Reservation System & Scheduling
            $this->command->info('📅 Phase 5: Reservation System & Complex Scheduling');
            $this->call([
                ExhaustiveReservationSeeder::class,
            ]);
            
            // Phase 6: Edge Cases & Business Continuity
            $this->command->info('⚡ Phase 6: Edge Cases & Business Continuity');
            $this->call([
                ExhaustiveEdgeCaseSeeder::class,
                ExhaustiveValidationSeeder::class,
            ]);
            
            // Re-enable foreign key checks
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            
            $this->displaySystemSummary();
            
        } catch (\Exception $e) {
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            $this->command->error('❌ Exhaustive seeding failed: ' . $e->getMessage());
            Log::error('Exhaustive seeding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Display comprehensive system summary
     */
    private function displaySystemSummary(): void
    {
        $this->command->info('');
        $this->command->info('🎯 EXHAUSTIVE SYSTEM SEEDING COMPLETED');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        
        // Subscription & Organization Overview
        $orgCount = \App\Models\Organization::count();
        $subscriptionPlans = \App\Models\SubscriptionPlan::count();
        $activeSubscriptions = \App\Models\Subscription::where('is_active', true)->count();
        $expiredSubscriptions = \App\Models\Subscription::where('is_active', false)->count();
        
        $this->command->info("🏢 Organizations: {$orgCount} (Single/Multi-branch scenarios)");
        $this->command->info("💳 Subscription Plans: {$subscriptionPlans} (Basic/Pro/Enterprise/Expired)");
        $this->command->info("✅ Active Subscriptions: {$activeSubscriptions}");
        $this->command->info("❌ Expired Subscriptions: {$expiredSubscriptions}");
        
        // Branch & Infrastructure
        $branchCount = \App\Models\Branch::count();
        $headOffices = \App\Models\Branch::where('is_head_office', true)->count();
        $regularBranches = \App\Models\Branch::where('is_head_office', false)->count();
        $kitchenStations = \App\Models\KitchenStation::count();
        
        $this->command->info("🏪 Total Branches: {$branchCount} (Head: {$headOffices}, Regular: {$regularBranches})");
        $this->command->info("👨‍🍳 Kitchen Stations: {$kitchenStations} (Custom configurations)");
        
        // User Management & Permissions
        $userCount = \App\Models\User::count();
        $superAdmins = \App\Models\User::where('is_super_admin', true)->count();
        $orgAdmins = \App\Models\User::where('is_admin', true)->where('is_super_admin', false)->count();
        $staffUsers = \App\Models\User::where('is_admin', false)->count();
        $roleCount = \App\Models\Role::count();
        
        $this->command->info("👤 Total Users: {$userCount}");
        $this->command->info("  - Super Admins: {$superAdmins}");
        $this->command->info("  - Org Admins: {$orgAdmins}");
        $this->command->info("  - Staff: {$staffUsers}");
        $this->command->info("🎭 Roles: {$roleCount} (Cross-branch permissions)");
        
        // Menu & Inventory
        $menuCount = \App\Models\Menu::count();
        $activeMenus = \App\Models\Menu::where('is_active', true)->count();
        $menuItemCount = \App\Models\MenuItem::count();
        $inventoryItems = \App\Models\InventoryItem::count() ?? 0;
        $lowStockItems = \App\Models\InventoryItem::where('stock_status', 'low_stock')->count() ?? 0;
        
        $this->command->info("📋 Menus: {$menuCount} (Active: {$activeMenus})");
        $this->command->info("🍽️ Menu Items: {$menuItemCount} (Time-based availability)");
        $this->command->info("📦 Inventory Items: {$inventoryItems} (Low Stock: {$lowStockItems})");
        
        // Orders & Kitchen Operations
        $orderCount = \App\Models\Order::count();
        $completedOrders = \App\Models\Order::where('status', 'completed')->count();
        $kotCount = \App\Models\Kot::count() ?? 0;
        $reservationCount = \App\Models\Reservation::count();
        
        $this->command->info("🛒 Orders: {$orderCount} (Completed: {$completedOrders})");
        $this->command->info("📋 KOTs: {$kotCount} (Kitchen workflow)");
        $this->command->info("📅 Reservations: {$reservationCount} (Complex scheduling)");
        
        // System Health & Edge Cases
        $tablesCount = \App\Models\Table::count();
        $supplierCount = \App\Models\Supplier::count();
        $employeeCount = \App\Models\Employee::count();
        
        $this->command->info("🪑 Tables: {$tablesCount}");
        $this->command->info("🚚 Suppliers: {$supplierCount}");
        $this->command->info("👥 Employees: {$employeeCount}");
        
        $this->command->info('');
        $this->command->info('🔐 Test Credentials:');
        $this->command->info('  Super Admin: superadmin@rms.com / password123');
        $this->command->info('  Org Admin: admin@spicegarden.lk / password123');
        $this->command->info('  Branch Manager: manager.1@spicegarden.com / password123');
        $this->command->info('  Staff: chef.1@spicegarden.com / password123');
        
        $this->command->info('');
        $this->command->info('✨ All restaurant management scenarios have been seeded!');
        $this->command->info('   - Subscription tier testing');
        $this->command->info('   - Multi-branch operations');
        $this->command->info('   - Role-based access control');
        $this->command->info('   - Time-based menu management');
        $this->command->info('   - Complex order workflows');
        $this->command->info('   - Inventory edge cases');
        $this->command->info('   - Reservation conflicts');
        $this->command->info('   - Business continuity scenarios');
        $this->command->info('');
    }
}
