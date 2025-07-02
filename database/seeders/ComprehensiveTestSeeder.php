<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestSeeder extends Seeder
{
    /**
     * Run comprehensive test data seeding in proper dependency order.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Test Data Seeding...');
        
        // Track seeding progress
        $seeders = [
            'Foundation & Permissions' => PermissionModuleSubscriptionSeeder::class,
            'Super Admin & Organizations' => SuperAdminOrganizationSeeder::class,
            'Branch Networks & Infrastructure' => BranchNetworkSeeder::class,
            'Inventory & Supplier Management' => InventorySupplierSeeder::class,
            'Reservation Lifecycle Workflows' => ReservationLifecycleSeeder::class,
            'Order Simulation & Processing' => OrderSimulationSeeder::class,
            'Guest Activity & Public Interface' => GuestActivitySeeder::class,
        ];

        $totalSeeders = count($seeders);
        $currentSeeder = 0;

        foreach ($seeders as $description => $seederClass) {
            $currentSeeder++;
            $this->command->info("📊 Progress: [{$currentSeeder}/{$totalSeeders}] {$description}");
            
            try {
                $startTime = microtime(true);
                $this->call($seederClass);
                $duration = round(microtime(true) - $startTime, 2);
                
                $this->command->info("✅ Completed: {$description} (in {$duration}s)");
                Log::info("Seeder completed successfully", [
                    'seeder' => $seederClass,
                    'description' => $description,
                    'duration' => $duration
                ]);
                
            } catch (\Exception $e) {
                $this->command->error("❌ Failed: {$description}");
                $this->command->error("Error: " . $e->getMessage());
                
                Log::error("Seeder failed", [
                    'seeder' => $seederClass,
                    'description' => $description,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Continue with other seeders unless critical failure
                if (str_contains($seederClass, 'Permission') || str_contains($seederClass, 'SuperAdmin')) {
                    $this->command->error("🚨 Critical seeder failed - stopping execution");
                    throw $e;
                }
            }
        }

        $this->generateSeedingSummary();
        $this->command->info('🎉 Comprehensive Test Data Seeding Completed!');
    }

    /**
     * Generate a summary report of seeded data
     */
    private function generateSeedingSummary(): void
    {
        $this->command->info('📋 Seeding Summary Report:');
        
        try {
            $summary = [
                'Users' => DB::table('users')->count(),
                'Organizations' => DB::table('organizations')->count(),
                'Branches' => DB::table('branches')->count(),
                'Modules' => DB::table('modules')->count(),
                'Permissions' => DB::table('permissions')->count(),
                'Subscription Plans' => DB::table('subscription_plans')->count(),
                'Suppliers' => DB::table('suppliers')->count(),
                'Item Categories' => DB::table('item_categories')->count(),
                'Item Masters' => DB::table('item_masters')->count(),
                'Inventory Items' => DB::table('item_masters')->count(),
                'Tables' => DB::table('tables')->count(),
                'Customers' => DB::table('customers')->count(),
                'Reservations' => DB::table('reservations')->count(),
                'Menu Categories' => DB::table('menu_categories')->count(),
                'Menu Items' => DB::table('menu_items')->count(),
                'Orders' => DB::table('orders')->count(),
                'KOTs' => DB::table('kots')->count(),
                'Bills' => DB::table('bills')->count(),
            ];

            foreach ($summary as $entity => $count) {
                $this->command->line("  • {$entity}: {$count}");
            }

            // Check for potential issues
            $this->validateSeedingIntegrity($summary);
            
        } catch (\Exception $e) {
            $this->command->warn("Could not generate complete summary: " . $e->getMessage());
        }
    }

    /**
     * Validate the integrity of seeded data
     */
    private function validateSeedingIntegrity(array $summary): void
    {
        $issues = [];

        // Check if we have organizations but no branches
        if ($summary['Organizations'] > 0 && $summary['Branches'] == 0) {
            $issues[] = "Organizations exist but no branches found";
        }

        // Check if we have inventory items but no suppliers
        if ($summary['Inventory Items'] > 0 && $summary['Suppliers'] == 0) {
            $issues[] = "Inventory items exist but no suppliers found";
        }

        // Check if we have orders but no customers
        if ($summary['Orders'] > 0 && $summary['Customers'] == 0) {
            $issues[] = "Orders exist but no customers found";
        }

        // Check if we have reservations but no tables
        if ($summary['Reservations'] > 0 && $summary['Tables'] == 0) {
            $issues[] = "Reservations exist but no tables found";
        }

        if (!empty($issues)) {
            $this->command->warn('⚠️  Data Integrity Issues Detected:');
            foreach ($issues as $issue) {
                $this->command->warn("  • {$issue}");
            }
        } else {
            $this->command->info('✅ Data integrity validation passed');
        }
    }
}
