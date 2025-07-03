<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Database\Seeders\ComprehensiveTestSeeder;

class ComprehensiveSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seed:comprehensive 
                            {--verify : Verify seeded data integrity after completion}
                            {--fresh : Run fresh migration before seeding}
                            {--force : Force seeding even in production}
                            {--summary : Generate detailed summary report}';

    /**
     * The console command description.
     */
    protected $description = 'Run comprehensive database seeding with all test data and verification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Comprehensive Database Seeding...');
        
        // Check if we're in production and force flag is not set
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Cannot run comprehensive seeding in production without --force flag');
            return 1;
        }

        try {
            // Run fresh migration if requested
            if ($this->option('fresh')) {
                $this->info('🔄 Running fresh migration...');
                Artisan::call('migrate:fresh');
                $this->info('✅ Fresh migration completed');
            }

            // Run the comprehensive seeder
            $this->info('📊 Running comprehensive seeding...');
            $startTime = microtime(true);
            
            Artisan::call('db:seed', [
                '--class' => ComprehensiveTestSeeder::class
            ]);
            
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("✅ Comprehensive seeding completed in {$duration} seconds");

            // Verify data integrity if requested
            if ($this->option('verify')) {
                $this->info('🔍 Verifying seeded data integrity...');
                $this->verifyDataIntegrity();
            }

            // Generate summary report if requested
            if ($this->option('summary')) {
                $this->generateDetailedSummary();
            }

            $this->info('🎉 Comprehensive seeding process completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Comprehensive seeding failed: ' . $e->getMessage());
            Log::error('Comprehensive seeding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Verify the integrity of seeded data
     */
    private function verifyDataIntegrity(): void
    {
        $this->info('Checking data integrity...');
        
        $checks = [
            'Organizations have branches' => $this->checkOrganizationBranches(),
            'Branches have tables' => $this->checkBranchTables(),
            'Users have roles' => $this->checkUserRoles(),
            'Orders have customers' => $this->checkOrderCustomers(),
            'Reservations have tables' => $this->checkReservationTables(),
            'Menu items have categories' => $this->checkMenuItemCategories(),
            'Inventory items have suppliers' => $this->checkInventorySuppliers(),
            'Orders have items' => $this->checkOrderItems(),
            'Bills have orders' => $this->checkBillOrders(),
        ];

        $passed = 0;
        $failed = 0;

        foreach ($checks as $description => $result) {
            if ($result) {
                $this->info("  ✅ {$description}");
                $passed++;
            } else {
                $this->warn("  ⚠️  {$description}");
                $failed++;
            }
        }

        $total = $passed + $failed;
        $this->info("Integrity check completed: {$passed}/{$total} passed");
        
        if ($failed > 0) {
            $this->warn("⚠️  {$failed} integrity issues found - review data relationships");
        }
    }

    /**
     * Generate detailed summary report
     */
    private function generateDetailedSummary(): void
    {
        $this->info('📋 Generating detailed summary report...');
        
        try {
            $tables = [
                'System' => [
                    'organizations' => 'Organizations',
                    'branches' => 'Branches',
                    'users' => 'Users',
                    'admins' => 'Admins',
                    'roles' => 'Roles',
                    'permissions' => 'Permissions',
                    'modules' => 'Modules',
                ],
                'Inventory' => [
                    'suppliers' => 'Suppliers',
                    'item_categories' => 'Item Categories',
                    'item_masters' => 'Item Masters',
                    'inventory_items' => 'Inventory Items',
                    'item_transactions' => 'Item Transactions',
                ],
                'Menu & Orders' => [
                    'menu_categories' => 'Menu Categories',
                    'menu_items' => 'Menu Items',
                    'orders' => 'Orders',
                    'order_items' => 'Order Items',
                    'kots' => 'KOTs',
                    'bills' => 'Bills',
                ],
                'Reservations' => [
                    'customers' => 'Customers',
                    'tables' => 'Tables',
                    'reservations' => 'Reservations',
                ],
                'Production' => [
                    'production_recipes' => 'Production Recipes',
                    'production_orders' => 'Production Orders',
                    'production_sessions' => 'Production Sessions',
                ],
                'Purchasing' => [
                    'purchase_orders' => 'Purchase Orders',
                    'grn_masters' => 'GRN Masters',
                    'supplier_payments' => 'Supplier Payments',
                ],
            ];

            foreach ($tables as $category => $categoryTables) {
                $this->info("  📁 {$category}:");
                foreach ($categoryTables as $table => $display) {
                    $count = DB::table($table)->count();
                    $this->line("    • {$display}: {$count}");
                }
            }

        } catch (\Exception $e) {
            $this->warn("Could not generate complete summary: " . $e->getMessage());
        }
    }

    // Data integrity check methods
    private function checkOrganizationBranches(): bool
    {
        $orgsWithoutBranches = DB::table('organizations')
            ->leftJoin('branches', 'organizations.id', '=', 'branches.organization_id')
            ->whereNull('branches.id')
            ->count();
        return $orgsWithoutBranches === 0;
    }

    private function checkBranchTables(): bool
    {
        $branchesWithoutTables = DB::table('branches')
            ->leftJoin('tables', 'branches.id', '=', 'tables.branch_id')
            ->whereNull('tables.id')
            ->count();
        return $branchesWithoutTables === 0;
    }

    private function checkUserRoles(): bool
    {
        $usersWithoutRoles = DB::table('users')
            ->leftJoin('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->whereNull('model_has_roles.role_id')
            ->count();
        return $usersWithoutRoles === 0;
    }

    private function checkOrderCustomers(): bool
    {
        $ordersWithoutCustomers = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereNull('customers.id')
            ->whereNotNull('orders.customer_id')
            ->count();
        return $ordersWithoutCustomers === 0;
    }

    private function checkReservationTables(): bool
    {
        $reservationsWithoutTables = DB::table('reservations')
            ->leftJoin('tables', 'reservations.table_id', '=', 'tables.id')
            ->whereNull('tables.id')
            ->whereNotNull('reservations.table_id')
            ->count();
        return $reservationsWithoutTables === 0;
    }

    private function checkMenuItemCategories(): bool
    {
        $menuItemsWithoutCategories = DB::table('menu_items')
            ->leftJoin('menu_categories', 'menu_items.category_id', '=', 'menu_categories.id')
            ->whereNull('menu_categories.id')
            ->count();
        return $menuItemsWithoutCategories === 0;
    }

    private function checkInventorySuppliers(): bool
    {
        $itemsWithoutSuppliers = DB::table('item_masters')
            ->leftJoin('suppliers', 'item_masters.supplier_id', '=', 'suppliers.id')
            ->whereNull('suppliers.id')
            ->whereNotNull('item_masters.supplier_id')
            ->count();
        return $itemsWithoutSuppliers === 0;
    }

    private function checkOrderItems(): bool
    {
        $ordersWithoutItems = DB::table('orders')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.id')
            ->count();
        return $ordersWithoutItems === 0;
    }

    private function checkBillOrders(): bool
    {
        $billsWithoutOrders = DB::table('bills')
            ->leftJoin('orders', 'bills.order_id', '=', 'orders.id')
            ->whereNull('orders.id')
            ->count();
        return $billsWithoutOrders === 0;
    }
}
