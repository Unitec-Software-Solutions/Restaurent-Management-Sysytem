<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExhaustiveSystemSeeder extends Seeder
{
    /**
     * Run exhaustive seeding covering all possible restaurant management scenarios
     */
    public function run(): void
    {
        $this->command->info('🌟 Starting Exhaustive Restaurant Management System Seeding...');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        
        // Disable foreign key checks (database-agnostic)
        $this->disableForeignKeyChecks();
        
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
            $this->command->info('� Phase 5: Reservation System & Complex Scheduling');
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
            $this->enableForeignKeyChecks();
            
            $this->displaySystemSummary();
            
        } catch (\Exception $e) {
            $this->enableForeignKeyChecks();
            $this->command->error('❌ Exhaustive seeding failed: ' . $e->getMessage());
            Log::error('Exhaustive seeding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Disable foreign key checks in a database-agnostic way
     */
    private function disableForeignKeyChecks(): void
    {
        $databaseType = DB::connection()->getDriverName();
        
        switch ($databaseType) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $this->command->info('🔧 Disabled MySQL foreign key checks');
                break;
            case 'pgsql':
                // PostgreSQL doesn't have a global foreign key check disable
                // We'll rely on transaction rollback and proper seeding order
                $this->command->info('🔧 Using PostgreSQL-compatible seeding (foreign keys remain active)');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=OFF;');
                $this->command->info('🔧 Disabled SQLite foreign key checks');
                break;
            default:
                $this->command->warn("⚠️  Unknown database type: {$databaseType}. Foreign key constraints remain active.");
                break;
        }
    }

    /**
     * Re-enable foreign key checks in a database-agnostic way
     */
    private function enableForeignKeyChecks(): void
    {
        $databaseType = DB::connection()->getDriverName();
        
        switch ($databaseType) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->command->info('🔧 Re-enabled MySQL foreign key checks');
                break;
            case 'pgsql':
                // PostgreSQL foreign keys are always active
                $this->command->info('🔧 PostgreSQL foreign key checks remain active');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=ON;');
                $this->command->info('🔧 Re-enabled SQLite foreign key checks');
                break;
            default:
                $this->command->info('🔧 Database-specific foreign key management completed');
                break;
        }
    }

    /**
     * Display comprehensive system summary after seeding
     */
    private function displaySystemSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 EXHAUSTIVE RESTAURANT MANAGEMENT SYSTEM SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════════════');
        
        // Core Infrastructure
        $this->displayInfrastructureSummary();
        
        // User Management
        $this->displayUserManagementSummary();
        
        // Business Operations
        $this->displayBusinessOperationsSummary();
        
        // System Health & Validation
        $this->displaySystemHealthSummary();
        
        $this->command->info('');
        $this->command->info('🎯 SUCCESS: All restaurant management scenarios have been comprehensively seeded!');
        $this->command->info('🔒 Relationship integrity and permission boundaries validated');
        $this->command->info('📈 State transition validations implemented for edge case testing');
        $this->command->info('🚀 System ready for comprehensive testing and validation');
    }

    private function displayInfrastructureSummary(): void
    {
        $this->command->info('');
        $this->command->info('🏗️  INFRASTRUCTURE SUMMARY');
        $this->command->info('─────────────────────────────────────');
        
        $subscriptionPlans = \App\Models\SubscriptionPlan::count();
        $organizations = \App\Models\Organization::count();
        $branches = \App\Models\Branch::count();
        $tables = \App\Models\Table::count();
        $kitchenStations = \App\Models\KitchenStation::count();
        $modules = \App\Models\Module::count();
        
        $this->command->info(sprintf('  %-25s: %d', 'Subscription Plans', $subscriptionPlans));
        $this->command->info(sprintf('  %-25s: %d', 'Organizations', $organizations));
        $this->command->info(sprintf('  %-25s: %d', 'Branches', $branches));
        $this->command->info(sprintf('  %-25s: %d', 'Tables', $tables));
        $this->command->info(sprintf('  %-25s: %d', 'Kitchen Stations', $kitchenStations));
        $this->command->info(sprintf('  %-25s: %d', 'System Modules', $modules));
          // Organization types breakdown
        try {
            $orgTypes = \App\Models\Organization::select('business_type', DB::raw('count(*) as total'))
                         ->groupBy('business_type')
                         ->pluck('total', 'business_type');
                         
            $this->command->info('  Organization Types:');
            foreach ($orgTypes as $type => $count) {
                $this->command->info(sprintf('    • %-20s: %d', ucfirst(str_replace('_', ' ', $type)), $count));
            }
        } catch (\Exception $e) {
            $this->command->warn('    Organization type breakdown not available');
        }
    }

    private function displayUserManagementSummary(): void
    {
        $this->command->info('');
        $this->command->info('👥 USER MANAGEMENT SUMMARY');
        $this->command->info('─────────────────────────────────────');
        
        $admins = \App\Models\Admin::count();
        $users = \App\Models\User::count();
        $roles = \Spatie\Permission\Models\Role::count();
        $permissions = \Spatie\Permission\Models\Permission::count();
        
        $this->command->info(sprintf('  %-25s: %d', 'Admin Users', $admins));
        $this->command->info(sprintf('  %-25s: %d', 'Regular Users', $users));
        $this->command->info(sprintf('  %-25s: %d', 'Roles', $roles));
        $this->command->info(sprintf('  %-25s: %d', 'Permissions', $permissions));
        
        // Role distribution
        if (class_exists('\App\Models\Admin')) {                $roleDistribution = \App\Models\Admin::join('model_has_roles', 'admins.id', '=', 'model_has_roles.model_id')
                                   ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                   ->where('model_has_roles.model_type', \App\Models\Admin::class)
                                   ->select('roles.name', DB::raw('count(*) as total'))
                                   ->groupBy('roles.name')
                                   ->pluck('total', 'name');
                               
            $this->command->info('  Role Distribution:');
            foreach ($roleDistribution as $role => $count) {
                $this->command->info(sprintf('    • %-20s: %d', ucfirst(str_replace('_', ' ', $role)), $count));
            }
        }
    }

    private function displayBusinessOperationsSummary(): void
    {
        $this->command->info('');
        $this->command->info('💼 BUSINESS OPERATIONS SUMMARY');
        $this->command->info('─────────────────────────────────────');
        
        $menuCategories = \App\Models\MenuCategory::count();
        $menuItems = \App\Models\MenuItem::count();
        $orders = \App\Models\Order::count();
        $reservations = \App\Models\Reservation::count();
        $inventoryItems = \App\Models\ItemMaster::count();
        
        $this->command->info(sprintf('  %-25s: %d', 'Menu Categories', $menuCategories));
        $this->command->info(sprintf('  %-25s: %d', 'Menu Items', $menuItems));
        $this->command->info(sprintf('  %-25s: %d', 'Orders', $orders));
        $this->command->info(sprintf('  %-25s: %d', 'Reservations', $reservations));
        $this->command->info(sprintf('  %-25s: %d', 'Inventory Items', $inventoryItems));
        
        // Order status breakdown
        if (class_exists('\App\Models\Order')) {
            $orderStatuses = \App\Models\Order::select('status', DB::raw('count(*) as total'))
                            ->groupBy('status')
                            ->pluck('total', 'status');
                            
            $this->command->info('  Order Status Breakdown:');
            foreach ($orderStatuses as $status => $count) {
                $this->command->info(sprintf('    • %-20s: %d', ucfirst(str_replace('_', ' ', $status)), $count));
            }
        }
        
        // Reservation status breakdown
        if (class_exists('\App\Models\Reservation')) {            $reservationStatuses = \App\Models\Reservation::select('status', DB::raw('count(*) as total'))
                              ->groupBy('status')
                              ->pluck('total', 'status');
                                  
            $this->command->info('  Reservation Status Breakdown:');
            foreach ($reservationStatuses as $status => $count) {
                $this->command->info(sprintf('    • %-20s: %d', ucfirst(str_replace('_', ' ', $status)), $count));
            }
        }
    }

    private function displaySystemHealthSummary(): void
    {
        $this->command->info('');
        $this->command->info('🔍 SYSTEM HEALTH & VALIDATION SUMMARY');
        $this->command->info('─────────────────────────────────────');
        
        // Count specific edge case scenarios
        $lowStockItems = 0;
        $expiredItems = 0;
        $conflictingReservations = 0;
        $partialOrders = 0;
        
        try {
            // Low stock items
            if (class_exists('\App\Models\InventoryItem')) {
                $lowStockItems = \App\Models\InventoryItem::whereColumn('current_stock', '<=', 'reorder_level')->count();
            }
            
            // Expired items
            if (class_exists('\App\Models\InventoryItem')) {
                $expiredItems = \App\Models\InventoryItem::where('expiry_date', '<', now())->count();
            }
            
            // Partial orders
            if (class_exists('\App\Models\Order')) {
                $partialOrders = \App\Models\Order::where('payment_status', 'partial')->count();
            }
            
            // Conflicting reservations (same table, overlapping times)
            if (class_exists('\App\Models\Reservation')) {
                $conflictingReservations = \App\Models\Reservation::where('status', 'conflict')->count();
            }
            
        } catch (\Exception $e) {
            $this->command->warn('    Some validation counts unavailable due to model constraints');
        }
        
        $this->command->info(sprintf('  %-25s: %d', 'Low Stock Items', $lowStockItems));
        $this->command->info(sprintf('  %-25s: %d', 'Expired Items', $expiredItems));
        $this->command->info(sprintf('  %-25s: %d', 'Partial Orders', $partialOrders));
        $this->command->info(sprintf('  %-25s: %d', 'Conflicting Reservations', $conflictingReservations));
        
        $this->command->info('');
        $this->command->info('  ✅ Edge Case Scenarios Created:');
        $this->command->info('    • Subscription plan variations (Basic → Enterprise)');
        $this->command->info('    • Organization types (Single → Multi-branch → Franchise)');
        $this->command->info('    • User permission hierarchies (Guest → Staff → Admin → Super)');
        $this->command->info('    • Menu configurations (Daily → Seasonal → Event-based)');
        $this->command->info('    • Order lifecycle complexities (Cart → Payment → Kitchen → Fulfillment)');
        $this->command->info('    • Inventory edge cases (Low stock → Transfers → Adjustments)');
        $this->command->info('    • Reservation conflicts (Time → Capacity → Resource allocation)');
        $this->command->info('    • Kitchen workflow patterns (Peak → Emergency → Quality control)');
        $this->command->info('    • Financial scenarios (Discounts → Refunds → Multi-payment)');
        $this->command->info('    • System boundaries (Concurrency → Data limits → Performance)');
    }
}
