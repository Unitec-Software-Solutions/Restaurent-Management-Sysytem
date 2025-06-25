<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SeederValidationService;
use App\Services\SeederErrorResolutionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseSeedSafeCommand extends Command
{
    protected $signature = 'db:seed-safe 
                            {--class= : Specific seeder class to run}
                            {--auto-fix : Automatically fix detected issues}
                            {--dry-run : Show what would be fixed without applying changes}
                            {--force : Force seeding even with warnings}
                            {--report : Generate detailed validation report}';
    protected $description = 'Run database seeders with comprehensive validation, auto-fix, and error handling';

    protected SeederValidationService $validationService;
    protected SeederErrorResolutionService $errorResolutionService;    public function __construct(
        SeederValidationService $validationService,
        SeederErrorResolutionService $errorResolutionService
    ) {
        parent::__construct();
        $this->validationService = $validationService;
        $this->errorResolutionService = $errorResolutionService;
    }

    public function handle(): int
    {
        $this->info('🌱 Starting comprehensive safe database seeding...');
        $this->newLine();

        // Get seeder class
        $seederClass = $this->option('class') ?: 'DatabaseSeeder';
        $autoFix = $this->option('auto-fix');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $generateReport = $this->option('report');

        $startTime = microtime(true);
        $validationResults = [];
        
        try {
            // 1. Check database connection
            $this->checkDatabaseConnection();
            
            // 2. Run comprehensive pre-seeding validation
            $this->info('🔍 Running comprehensive pre-seeding validation...');
            $preValidation = $this->runComprehensiveValidation($seederClass);
            $validationResults['pre_validation'] = $preValidation;
            
            // 3. Handle detected issues with auto-fix if enabled
            if (!empty($preValidation['issues'])) {
                $this->displayValidationIssues($preValidation['issues']);
                
                if ($autoFix || $dryRun) {
                    $this->info($dryRun ? '🧪 Dry run - showing what would be fixed:' : '🔧 Auto-fixing detected issues...');
                    $fixResults = $this->errorResolutionService->resolveSeederErrors($preValidation['issues'], $dryRun);
                    $validationResults['auto_fix'] = $fixResults;
                    
                    if ($dryRun) {
                        $this->displayFixPreview($fixResults);
                        return Command::SUCCESS;
                    }
                    
                    if (!empty($fixResults['fixed'])) {
                        $this->info('✅ Fixed ' . count($fixResults['fixed']) . ' issues automatically');
                        foreach ($fixResults['fixed'] as $fix) {
                            $this->line("  • {$fix}");
                        }
                    }
                    
                    if (!empty($fixResults['failed'])) {
                        $this->error('❌ Could not auto-fix ' . count($fixResults['failed']) . ' issues:');
                        foreach ($fixResults['failed'] as $issue) {
                            $this->line("  • {$issue}");
                        }
                        
                        if (!$force) {
                            $this->error('Use --force to proceed anyway or fix manually');
                            return Command::FAILURE;
                        }
                    }
                } elseif (!$force) {
                    $this->error('❌ Validation issues found. Use --auto-fix to resolve automatically or --force to proceed anyway');
                    return Command::FAILURE;
                }
            }

            // 4. Run seeding with transaction safety
            $this->info("🌱 Running seeder with transaction safety: {$seederClass}");
            $seedingResult = $this->runTransactionalSeeding($seederClass);
            $validationResults['seeding'] = $seedingResult;

            if (!$seedingResult['success']) {
                $this->error('❌ Seeding failed: ' . $seedingResult['message']);
                $this->logSeedingError($seederClass, $seedingResult);
                return Command::FAILURE;
            }

            $this->info('✅ ' . $seedingResult['message']);
            
            // 5. Post-seeding validation
            $this->info('🔍 Running post-seeding validation...');
            $postValidation = $this->runPostSeedValidation();
            $validationResults['post_validation'] = $postValidation;
            
            // 6. Generate report if requested
            if ($generateReport) {
                $this->generateValidationReport($validationResults, $startTime);
            }

            $this->newLine();
            $this->info('🎉 Safe seeding completed successfully!');
            $duration = round(microtime(true) - $startTime, 2);
            $this->info("⏱️ Total time: {$duration} seconds");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Safe seeding failed: ' . $e->getMessage());
            $this->logSeedingError($seederClass, ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            if ($generateReport) {
                $validationResults['error'] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()
                ];
                $this->generateValidationReport($validationResults, $startTime);
            }
            
            return Command::FAILURE;
        }
    }    protected function checkDatabaseConnection(): void
    {
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection verified');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function runComprehensiveValidation(string $seederClass): array
    {
        $issues = [];
        
        // 1. Check for required tables and migrations
        $tableIssues = $this->checkRequiredTables();
        if (!empty($tableIssues)) {
            $issues = array_merge($issues, $tableIssues);
        }

        // 2. Check existing data constraints
        $constraintIssues = $this->checkDataConstraints();
        if (!empty($constraintIssues)) {
            $issues = array_merge($issues, $constraintIssues);
        }

        // 3. Use validation service for seeder-specific checks
        try {
            $seederValidation = $this->validationService->validateSeederRequirements($seederClass);
            if (!empty($seederValidation['issues'])) {
                $issues = array_merge($issues, $seederValidation['issues']);
            }
        } catch (\Exception $e) {
            $issues[] = "Seeder validation failed: " . $e->getMessage();
        }

        return [
            'issues' => $issues,
            'status' => empty($issues) ? 'passed' : 'failed',
            'timestamp' => now()
        ];
    }

    protected function checkRequiredTables(): array
    {
        $issues = [];
        $requiredTables = [
            'organizations' => 'Organization management',
            'branches' => 'Branch management', 
            'kitchen_stations' => 'Kitchen station management',
            'users' => 'User management',
            'roles' => 'Role-based access control',
            'permissions' => 'Permission system'
        ];

        foreach ($requiredTables as $table => $purpose) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Missing required table '{$table}' for {$purpose}";
            }
        }

        if (!empty($issues)) {
            $issues[] = "SOLUTION: Run 'php artisan migrate' to create missing tables";
        }

        return $issues;
    }

    protected function checkDataConstraints(): array
    {
        $issues = [];

        try {
            // Check for orphaned foreign key references
            $orphanChecks = [
                [
                    'table' => 'branches',
                    'foreign_key' => 'organization_id',
                    'reference_table' => 'organizations',
                    'reference_key' => 'id'
                ],
                [
                    'table' => 'kitchen_stations', 
                    'foreign_key' => 'branch_id',
                    'reference_table' => 'branches',
                    'reference_key' => 'id'
                ],
                [
                    'table' => 'users',
                    'foreign_key' => 'branch_id', 
                    'reference_table' => 'branches',
                    'reference_key' => 'id'
                ]
            ];

            foreach ($orphanChecks as $check) {
                if (Schema::hasTable($check['table']) && Schema::hasTable($check['reference_table'])) {
                    $orphanedCount = DB::table($check['table'])
                        ->leftJoin($check['reference_table'], 
                            $check['table'] . '.' . $check['foreign_key'], 
                            '=', 
                            $check['reference_table'] . '.' . $check['reference_key'])
                        ->whereNull($check['reference_table'] . '.' . $check['reference_key'])
                        ->whereNotNull($check['table'] . '.' . $check['foreign_key'])
                        ->count();

                    if ($orphanedCount > 0) {
                        $issues[] = "{$orphanedCount} records in '{$check['table']}' have invalid {$check['foreign_key']} references";
                    }
                }
            }            // Check for duplicate unique constraints that could cause seeding failures
            if (Schema::hasTable('kitchen_stations')) {
                $duplicateCodes = DB::table('kitchen_stations')
                    ->select('code')
                    ->whereNotNull('code')
                    ->groupBy('code')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                if ($duplicateCodes->count() > 0) {
                    $codes = $duplicateCodes->pluck('code')->implode(', ');
                    $issues[] = "Duplicate kitchen station codes found: {$codes} - will cause unique constraint violations";
                }
            }

        } catch (\Exception $e) {
            $issues[] = "Could not check data constraints: " . $e->getMessage();
        }

        return $issues;
    }

    protected function runTransactionalSeeding(string $seederClass): array
    {
        try {
            DB::beginTransaction();
            
            $startTime = microtime(true);
            
            // Run the seeder
            Artisan::call('db:seed', [
                '--class' => $seederClass, 
                '--force' => true
            ]);
            
            $output = Artisan::output();
            $duration = round(microtime(true) - $startTime, 2);
            
            // Check for seeding errors in output
            if (str_contains($output, 'ERROR') || str_contains($output, 'Exception')) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Seeding failed with errors in output',
                    'output' => $output,
                    'duration' => $duration
                ];
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Seeder '{$seederClass}' completed successfully in {$duration}s",
                'output' => $output,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Seeding failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }    protected function runPostSeedValidation(): array
    {
        $validationResults = [];
        
        // Check if seeding was successful by counting records
        $tableChecks = [
            'organizations' => 'Organizations should exist for multi-tenant setup',
            'branches' => 'Branches should exist for location management',
            'kitchen_stations' => 'Kitchen stations should exist for order routing'
        ];

        foreach ($tableChecks as $table => $description) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $validationResults[$table] = [
                    'count' => $count,
                    'status' => $count > 0 ? 'success' : 'warning',
                    'message' => "{$description} - found {$count} records"
                ];
                
                if ($count === 0) {
                    $this->warn("⚠️ {$description} - found {$count} records");
                } else {
                    $this->info("✅ {$description} - found {$count} records");
                }
            }
        }

        // Specific validation for kitchen stations
        if (Schema::hasTable('kitchen_stations')) {
            $stationsWithoutCodes = DB::table('kitchen_stations')
                ->whereNull('code')
                ->orWhere('code', '')
                ->count();

            $validationResults['kitchen_station_codes'] = [
                'missing_codes' => $stationsWithoutCodes,
                'status' => $stationsWithoutCodes === 0 ? 'success' : 'error'
            ];

            if ($stationsWithoutCodes > 0) {
                $this->error("❌ {$stationsWithoutCodes} kitchen stations are missing required codes");
            } else {
                $this->info('✅ All kitchen stations have required codes');
            }

            // Check JSON field validity for printer configurations
            try {
                $invalidPrinterConfigs = DB::table('kitchen_stations')
                    ->whereRaw("JSON_VALID(printer_config) = 0")
                    ->whereNotNull('printer_config')
                    ->count();

                $validationResults['printer_config_json'] = [
                    'invalid_json' => $invalidPrinterConfigs,
                    'status' => $invalidPrinterConfigs === 0 ? 'success' : 'warning'
                ];

                if ($invalidPrinterConfigs > 0) {
                    $this->warn("⚠️ {$invalidPrinterConfigs} kitchen stations have invalid printer_config JSON");
                } else {
                    $this->info('✅ All printer configurations have valid JSON');
                }
            } catch (\Exception $e) {
                $validationResults['printer_config_json'] = [
                    'status' => 'skipped',
                    'reason' => 'JSON validation not supported by database'
                ];
                $this->info('ℹ️ JSON validation skipped (database-specific)');
            }
        }

        return $validationResults;
    }

    protected function displayValidationIssues(array $issues): void
    {
        $this->error('❌ Validation issues found:');
        foreach ($issues as $issue) {
            $this->line("  • {$issue}");
        }
        $this->newLine();
    }

    protected function displayFixPreview(array $fixResults): void
    {
        if (!empty($fixResults['would_fix'])) {
            $this->info('✅ Would fix ' . count($fixResults['would_fix']) . ' issues:');
            foreach ($fixResults['would_fix'] as $fix) {
                $this->line("  • {$fix}");
            }
        }
        
        if (!empty($fixResults['cannot_fix'])) {
            $this->warn('⚠️ Cannot auto-fix ' . count($fixResults['cannot_fix']) . ' issues:');
            foreach ($fixResults['cannot_fix'] as $issue) {
                $this->line("  • {$issue}");
            }
        }
    }

    protected function logSeedingError(string $seederClass, array $errorData): void
    {
        $logData = [
            'seeder_class' => $seederClass,
            'timestamp' => now(),
            'error_data' => $errorData,
            'context' => [
                'command' => 'db:seed-safe',
                'database' => config('database.default'),
                'environment' => app()->environment()
            ]
        ];

        Log::error('Database seeding failed', $logData);
    }

    protected function generateValidationReport(array $validationResults, float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);
        $reportPath = storage_path('logs/seeding-validation-report-' . now()->format('Y-m-d-H-i-s') . '.json');
        
        $report = [
            'timestamp' => now(),
            'duration_seconds' => $duration,
            'command_options' => [
                'class' => $this->option('class'),
                'auto_fix' => $this->option('auto-fix'),
                'dry_run' => $this->option('dry-run'),
                'force' => $this->option('force')
            ],
            'validation_results' => $validationResults,
            'summary' => [
                'overall_status' => $this->determineOverallStatus($validationResults),
                'issues_found' => $this->countIssues($validationResults),
                'auto_fixes_applied' => $this->countAutoFixes($validationResults)
            ]
        ];

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("📊 Validation report generated: {$reportPath}");
    }

    protected function determineOverallStatus(array $validationResults): string
    {
        if (isset($validationResults['error'])) {
            return 'failed';
        }
        
        if (isset($validationResults['pre_validation']) && 
            $validationResults['pre_validation']['status'] === 'failed') {
            return 'validation_failed';
        }
        
        if (isset($validationResults['seeding']) && 
            !$validationResults['seeding']['success']) {
            return 'seeding_failed';
        }
        
        return 'success';
    }

    protected function countIssues(array $validationResults): int
    {
        $count = 0;
        
        if (isset($validationResults['pre_validation']['issues'])) {
            $count += count($validationResults['pre_validation']['issues']);
        }
        
        return $count;
    }

    protected function countAutoFixes(array $validationResults): int
    {
        if (isset($validationResults['auto_fix']['fixed'])) {
            return count($validationResults['auto_fix']['fixed']);
        }
        
        return 0;
    }
}
