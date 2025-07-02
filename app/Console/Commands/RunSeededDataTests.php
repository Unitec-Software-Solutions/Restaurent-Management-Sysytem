<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class RunSeededDataTests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:seeded-data 
                           {--coverage : Generate code coverage report}
                           {--filter= : Filter tests by pattern}
                           {--group= : Run tests from specific group}';

    /**
     * The console command description.
     */
    protected $description = 'Run test suite against seeded data with comprehensive reporting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Running Test Suite Against Seeded Data');
        $this->info('==========================================');

        // Check if we have seeded data
        if (!$this->hasSeededData()) {
            $this->error('❌ No seeded data found. Please run comprehensive seeding first.');
            $this->info('💡 Run: php artisan seed:comprehensive --fresh');
            return 1;
        }

        $testCommands = $this->buildTestCommands();
        
        $allPassed = true;
        foreach ($testCommands as $description => $command) {
            $this->info("🔍 {$description}");
            
            $exitCode = Artisan::call($command['command'], $command['options'], $this->getOutput());
            
            if ($exitCode === 0) {
                $this->info("✅ {$description} - PASSED");
            } else {
                $this->error("❌ {$description} - FAILED");
                $allPassed = false;
            }
            
            $this->line(''); // Add spacing
        }

        // Generate comprehensive report
        $this->generateTestReport($allPassed);

        return $allPassed ? 0 : 1;
    }

    /**
     * Check if we have basic seeded data
     */
    private function hasSeededData(): bool
    {
        try {
            return DB::table('users')->where('email', 'super@admin.com')->exists() &&
                   DB::table('organizations')->count() > 0 &&
                   DB::table('branches')->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Build test commands based on options
     */
    private function buildTestCommands(): array
    {
        $commands = [];

        // Base test command options
        $baseOptions = [
            '--env' => 'testing'
        ];

        if ($this->option('filter')) {
            $baseOptions['--filter'] = $this->option('filter');
        }

        if ($this->option('group')) {
            $baseOptions['--group'] = $this->option('group');
        }

        // Feature Tests
        $commands['Feature Tests'] = [
            'command' => 'test',
            'options' => array_merge($baseOptions, [
                '--testsuite' => 'Feature'
            ])
        ];

        // Unit Tests
        $commands['Unit Tests'] = [
            'command' => 'test',
            'options' => array_merge($baseOptions, [
                '--testsuite' => 'Unit'
            ])
        ];

        // Coverage report if requested
        if ($this->option('coverage')) {
            $commands['Coverage Report'] = [
                'command' => 'test',
                'options' => array_merge($baseOptions, [
                    '--coverage-html' => 'tests/coverage',
                    '--coverage-clover' => 'tests/coverage/clover.xml'
                ])
            ];
        }

        return $commands;
    }

    /**
     * Generate comprehensive test report
     */
    private function generateTestReport(bool $allPassed): void
    {
        $this->info('📊 Test Report Summary');
        $this->info('======================');

        // Database state summary
        $this->displayDatabaseState();

        // Test results summary
        if ($allPassed) {
            $this->info('🎉 ALL TESTS PASSED!');
            $this->info('✅ Your seeded data is working correctly with the test suite');
        } else {
            $this->error('❌ SOME TESTS FAILED');
            $this->error('⚠️  Please review the failed tests and fix any issues');
        }

        // Coverage report location
        if ($this->option('coverage')) {
            $coverageDir = base_path('tests/coverage');
            if (File::exists($coverageDir)) {
                $this->info("📋 Coverage report generated: {$coverageDir}/index.html");
            }
        }

        // Recommendations
        $this->displayRecommendations($allPassed);
    }

    /**
     * Display current database state
     */
    private function displayDatabaseState(): void
    {
        try {
            $entities = [
                'Users' => DB::table('users')->count(),
                'Organizations' => DB::table('organizations')->count(),
                'Branches' => DB::table('branches')->count(),
                'Suppliers' => DB::table('suppliers')->count(),
                'Customers' => DB::table('customers')->count(),
                'Orders' => DB::table('orders')->count(),
                'Reservations' => DB::table('reservations')->count(),
                'Inventory Items' => DB::table('inventory_items')->count(),
            ];

            $this->info('💾 Database State:');
            foreach ($entities as $entity => $count) {
                $this->line("  • {$entity}: {$count}");
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not retrieve database state: ' . $e->getMessage());
        }
    }

    /**
     * Display recommendations based on test results
     */
    private function displayRecommendations(bool $allPassed): void
    {
        $this->info('💡 Recommendations:');

        if ($allPassed) {
            $this->line('  • Your system is ready for production deployment');
            $this->line('  • Consider running performance tests with: php artisan test --group=performance');
            $this->line('  • Monitor logs during high-load scenarios');
        } else {
            $this->line('  • Review failed test output for specific issues');
            $this->line('  • Check if seeded data meets test expectations');
            $this->line('  • Verify database relationships and constraints');
            $this->line('  • Run individual test files to isolate issues');
        }

        $this->line('  • For continuous integration, use: php artisan test:seeded-data --coverage');
        $this->line('  • Keep your test data fresh with: php artisan seed:comprehensive --fresh');
    }
}
