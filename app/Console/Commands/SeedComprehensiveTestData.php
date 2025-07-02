<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedComprehensiveTestData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seed:comprehensive 
                           {--fresh : Drop all tables and migrate fresh before seeding}
                           {--verify : Run verification tests after seeding}
                           {--profile : Profile memory and time usage during seeding}';

    /**
     * The console command description.
     */
    protected $description = 'Seed comprehensive test data for the restaurant management system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $this->info('🍽️  Restaurant Management System - Comprehensive Test Data Seeding');
        $this->info('================================================================');

        try {
            // Option 1: Fresh migration and seeding
            if ($this->option('fresh')) {
                if ($this->confirm('⚠️  This will DROP ALL TABLES and recreate them. Continue?')) {
                    $this->info('🔄 Running fresh migrations...');
                    Artisan::call('migrate:fresh', [], $this->getOutput());
                    $this->info('✅ Fresh migrations completed');
                } else {
                    $this->info('❌ Operation cancelled');
                    return;
                }
            }

            // Check if database is ready
            if (!$this->checkDatabaseReadiness()) {
                $this->error('❌ Database is not ready. Please run migrations first.');
                return 1;
            }

            // Run comprehensive seeding
            $this->info('🌱 Starting comprehensive test data seeding...');
            
            if ($this->option('profile')) {
                $this->info('📊 Profiling enabled - tracking memory and performance');
            }

            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ComprehensiveTestSeeder'
            ], $this->getOutput());

            // Calculate performance metrics
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            $duration = round($endTime - $startTime, 2);
            $memoryUsed = $this->formatBytes($endMemory - $startMemory);
            $peakMemory = $this->formatBytes(memory_get_peak_usage(true));

            $this->info('⚡ Performance Metrics:');
            $this->line("  • Total Duration: {$duration} seconds");
            $this->line("  • Memory Used: {$memoryUsed}");
            $this->line("  • Peak Memory: {$peakMemory}");

            // Option 2: Run verification tests
            if ($this->option('verify')) {
                $this->info('🔍 Running post-seeding verification...');
                $this->runVerificationTests();
            }

            $this->info('🎉 Comprehensive test data seeding completed successfully!');

            // Display next steps
            $this->displayNextSteps();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            $this->error('📝 Check logs for detailed error information');
            
            if ($this->option('verbose')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }
            
            return 1;
        }
    }

    /**
     * Check if the database is ready for seeding
     */
    private function checkDatabaseReadiness(): bool
    {
        try {
            $requiredTables = [
                'users', 'organizations', 'branches', 'modules', 
                'permissions', 'subscription_plans', 'suppliers'
            ];

            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->error("❌ Required table '{$table}' does not exist");
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Run verification tests after seeding
     */
    private function runVerificationTests(): void
    {
        $tests = [
            'Super Admin Exists' => function() {
                return DB::table('users')->where('email', 'super@admin.com')->exists();
            },
            'Organizations Have Branches' => function() {
                $orgCount = DB::table('organizations')->count();
                $branchCount = DB::table('branches')->count();
                return $branchCount >= $orgCount;
            },
            'Inventory Items Have Stock' => function() {
                return DB::table('item_masters')->where('is_active', true)->count() > 0;
            },
            'Orders Have Items' => function() {
                $orderCount = DB::table('orders')->count();
                $orderItemCount = DB::table('order_items')->count();
                return $orderItemCount >= $orderCount;
            },
            'Reservations Have Tables' => function() {
                $reservationCount = DB::table('reservations')->count();
                if ($reservationCount == 0) return true; // No reservations is OK
                return DB::table('tables')->count() > 0;
            }
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $testName => $testFunction) {
            try {
                if ($testFunction()) {
                    $this->line("  ✅ {$testName}");
                    $passed++;
                } else {
                    $this->line("  ❌ {$testName}");
                }
            } catch (\Exception $e) {
                $this->line("  ⚠️  {$testName} (Error: {$e->getMessage()})");
            }
        }

        $this->info("📊 Verification Results: {$passed}/{$total} tests passed");
    }

    /**
     * Display suggested next steps
     */
    private function displayNextSteps(): void
    {
        $this->info('🚀 Suggested Next Steps:');
        $this->line('  1. Run the test suite: php artisan test');
        $this->line('  2. Start the development server: php artisan serve');
        $this->line('  3. Check the application logs: tail -f storage/logs/laravel.log');
        $this->line('  4. Access the admin panel with: super@admin.com / password');
        $this->line('  5. Explore the seeded organizations and branches');
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
