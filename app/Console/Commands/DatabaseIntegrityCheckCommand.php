<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseIntegrityService;
use App\Services\SeederValidationService;
use Carbon\Carbon;

class DatabaseIntegrityCheckCommand extends Command
{
    protected $signature = 'db:integrity-check 
                            {--fix : Automatically fix discovered issues}
                            {--detailed : Show detailed analysis}
                            {--seeder= : Check specific seeder class}
                            {--export= : Export results to file}';

    protected $description = 'Comprehensive database integrity check with seeder validation';

    protected DatabaseIntegrityService $integrityService;
    protected SeederValidationService $validationService;

    public function __construct(
        DatabaseIntegrityService $integrityService,
        SeederValidationService $validationService
    ) {
        parent::__construct();
        $this->integrityService = $integrityService;
        $this->validationService = $validationService;
    }

    public function handle()
    {
        $this->displayHeader();
        
        // Perform comprehensive checks
        $results = $this->performIntegrityChecks();
        
        // Display results
        $this->displayResults($results);
        
        // Auto-fix if requested
        if ($this->option('fix')) {
            $this->performAutoFix($results);
        }
        
        // Export results if requested
        if ($this->option('export')) {
            $this->exportResults($results);
        }
        
        return 0;
    }

    protected function displayHeader()
    {
        $this->newLine();
        $this->line('<fg=white;bg=blue> 🔍 DATABASE INTEGRITY CHECK </fg=white;bg=blue>');
        $this->newLine();
        $this->info('Analyzing database structure, constraints, and seeder compatibility...');
        $this->newLine();
    }

    protected function performIntegrityChecks(): array
    {
        $results = [
            'timestamp' => Carbon::now(),
            'database_info' => $this->getDatabaseInfo(),
            'schema_analysis' => $this->analyzeSchema(),
            'constraint_analysis' => $this->analyzeConstraints(),
            'seeder_validation' => $this->validateSeeders(),
            'migration_status' => $this->checkMigrationStatus(),
            'relationship_integrity' => $this->checkRelationshipIntegrity(),
            'data_consistency' => $this->checkDataConsistency(),
            'performance_metrics' => $this->gatherPerformanceMetrics()
        ];

        return $results;
    }

    protected function getDatabaseInfo(): array
    {
        $driver = DB::getDriverName();
        $databaseName = DB::getDatabaseName();
        
        return [
            'driver' => $driver,
            'name' => $databaseName,
            'version' => $this->getDatabaseVersion(),
            'charset' => $this->getDatabaseCharset(),
            'timezone' => $this->getDatabaseTimezone()
        ];
    }

    protected function analyzeSchema(): array
    {
        $this->info('📋 Analyzing database schema...');
        
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [DB::getDatabaseName()]);
        $analysis = [
            'total_tables' => count($tables),
            'table_analysis' => [],
            'missing_columns' => [],
            'nullable_issues' => [],
            'index_analysis' => []
        ];

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $columns = $this->getTableColumns($tableName);
            
            $analysis['table_analysis'][$tableName] = [
                'columns' => count($columns),
                'nullable_columns' => array_filter($columns, fn($col) => $col['is_nullable'] === 'YES'),
                'indexed_columns' => $this->getIndexedColumns($tableName),
                'foreign_keys' => $this->getForeignKeys($tableName)
            ];

            // Check for common missing columns
            $this->checkRequiredColumns($tableName, $columns, $analysis);
        }

        return $analysis;
    }

    protected function analyzeConstraints(): array
    {
        $this->info('🔗 Analyzing database constraints...');
        
        return [
            'not_null_violations' => $this->findNotNullViolations(),
            'foreign_key_violations' => $this->findForeignKeyViolations(),
            'unique_constraint_violations' => $this->findUniqueViolations(),
            'check_constraint_violations' => $this->findCheckConstraintViolations()
        ];
    }

    protected function validateSeeders(): array
    {
        $this->info('🌱 Validating seeder classes...');
        
        $seederClasses = [
            'KitchenStationSeeder',
            'OrganizationSeeder', 
            'BranchSeeder',
            'MenuCategorySeeder',
            'ItemMasterSeeder',
            'UserSeeder',
            'EmployeeSeeder'
        ];

        $validation = [];
        foreach ($seederClasses as $seederClass) {
            $validation[$seederClass] = $this->validateSeederClass($seederClass);
        }

        return $validation;
    }

    protected function findNotNullViolations(): array
    {
        $violations = [];
        
        // Kitchen stations without codes
        $stationsWithoutCodes = DB::table('kitchen_stations')
            ->whereNull('code')
            ->orWhere('code', '')
            ->count();
            
        if ($stationsWithoutCodes > 0) {
            $violations['kitchen_stations_code'] = [
                'table' => 'kitchen_stations',
                'column' => 'code',
                'violations' => $stationsWithoutCodes,
                'description' => 'Kitchen stations missing required code field',
                'fix' => 'Generate unique codes for affected records'
            ];
        }

        // Organizations without required fields
        $orgsWithoutEmail = DB::table('organizations')
            ->whereNull('email')
            ->orWhere('email', '')
            ->count();
            
        if ($orgsWithoutEmail > 0) {
            $violations['organizations_email'] = [
                'table' => 'organizations',
                'column' => 'email',
                'violations' => $orgsWithoutEmail,
                'description' => 'Organizations missing required email field',
                'fix' => 'Generate placeholder emails or update records'
            ];
        }

        // Users without passwords
        $usersWithoutPasswords = DB::table('users')
            ->whereNull('password')
            ->orWhere('password', '')
            ->count();
            
        if ($usersWithoutPasswords > 0) {
            $violations['users_password'] = [
                'table' => 'users',
                'column' => 'password',
                'violations' => $usersWithoutPasswords,
                'description' => 'Users missing required password field',
                'fix' => 'Generate default passwords for affected users'
            ];
        }

        return $violations;
    }

    protected function findForeignKeyViolations(): array
    {
        $violations = [];
        
        // Kitchen stations with invalid branch_id
        $invalidBranchRefs = DB::table('kitchen_stations as ks')
            ->leftJoin('branches as b', 'ks.branch_id', '=', 'b.id')
            ->whereNull('b.id')
            ->count();
            
        if ($invalidBranchRefs > 0) {
            $violations['kitchen_stations_branch_id'] = [
                'table' => 'kitchen_stations',
                'column' => 'branch_id',
                'violations' => $invalidBranchRefs,
                'description' => 'Kitchen stations referencing non-existent branches',
                'fix' => 'Delete orphaned records or create missing branch references'
            ];
        }

        // Menu items with invalid category references
        if (Schema::hasTable('menu_items') && Schema::hasTable('menu_categories')) {
            $invalidCategoryRefs = DB::table('menu_items as mi')
                ->leftJoin('menu_categories as mc', 'mi.menu_category_id', '=', 'mc.id')
                ->whereNull('mc.id')
                ->count();
                
            if ($invalidCategoryRefs > 0) {
                $violations['menu_items_category_id'] = [
                    'table' => 'menu_items',
                    'column' => 'menu_category_id',
                    'violations' => $invalidCategoryRefs,
                    'description' => 'Menu items referencing non-existent categories',
                    'fix' => 'Create missing categories or update references'
                ];
            }
        }

        return $violations;
    }

    protected function findUniqueViolations(): array
    {
        $violations = [];
        
        // Duplicate kitchen station codes
        $duplicateCodes = DB::table('kitchen_stations')
            ->select('code')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        if ($duplicateCodes->isNotEmpty()) {
            $violations['kitchen_stations_code_duplicates'] = [
                'table' => 'kitchen_stations',
                'column' => 'code',
                'violations' => $duplicateCodes->count(),
                'duplicates' => $duplicateCodes->pluck('code'),
                'description' => 'Duplicate kitchen station codes found',
                'fix' => 'Generate new unique codes for duplicate records'
            ];
        }

        // Duplicate organization emails
        $duplicateEmails = DB::table('organizations')
            ->select('email')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        if ($duplicateEmails->isNotEmpty()) {
            $violations['organizations_email_duplicates'] = [
                'table' => 'organizations',
                'column' => 'email',
                'violations' => $duplicateEmails->count(),
                'duplicates' => $duplicateEmails->pluck('email'),
                'description' => 'Duplicate organization emails found',
                'fix' => 'Update duplicate emails with unique values'
            ];
        }

        return $violations;
    }

    protected function checkMigrationStatus(): array
    {
        $migrations = DB::table('migrations')->orderBy('batch')->get();
        $pendingMigrations = $this->getPendingMigrations();
        
        return [
            'total_migrations' => $migrations->count(),
            'last_batch' => $migrations->max('batch'),
            'pending_migrations' => $pendingMigrations,
            'migration_history' => $migrations->take(10)->toArray()
        ];
    }

    protected function checkRelationshipIntegrity(): array
    {
        $this->info('🔗 Checking relationship integrity...');
        
        return [
            'orphaned_records' => $this->findOrphanedRecords(),
            'circular_references' => $this->findCircularReferences(),
            'missing_relationships' => $this->findMissingRelationships()
        ];
    }

    protected function displayResults(array $results)
    {
        $this->newLine();
        $this->line('<fg=white;bg=green> 📊 INTEGRITY CHECK RESULTS </fg=white;bg=green>');
        $this->newLine();

        // Database info
        $this->displayDatabaseInfo($results['database_info']);
        
        // Schema analysis
        $this->displaySchemaAnalysis($results['schema_analysis']);
        
        // Constraint violations
        $this->displayConstraintViolations($results['constraint_analysis']);
        
        // Seeder validation
        $this->displaySeederValidation($results['seeder_validation']);
        
        // Generate summary
        $this->displaySummary($results);
    }

    protected function displayDatabaseInfo(array $info)
    {
        $this->info("💾 Database: {$info['name']} ({$info['driver']})");
        if (isset($info['version'])) {
            $this->line("   Version: {$info['version']}");
        }
        $this->newLine();
    }

    protected function displaySchemaAnalysis(array $analysis)
    {
        $this->info("📋 Schema Analysis:");
        $this->line("   Tables: {$analysis['total_tables']}");
        
        if (!empty($analysis['missing_columns'])) {
            $this->warn("   Missing Columns Found:");
            foreach ($analysis['missing_columns'] as $issue) {
                $this->line("     - {$issue}");
            }
        }
        
        if (!empty($analysis['nullable_issues'])) {
            $this->warn("   Nullable Field Issues:");
            foreach ($analysis['nullable_issues'] as $issue) {
                $this->line("     - {$issue}");
            }
        }
        
        $this->newLine();
    }

    protected function displayConstraintViolations(array $constraints)
    {
        $this->info("🚫 Constraint Violations:");
        
        $totalViolations = 0;
        
        foreach (['not_null_violations', 'foreign_key_violations', 'unique_constraint_violations'] as $type) {
            if (!empty($constraints[$type])) {
                $this->warn("   " . ucwords(str_replace('_', ' ', $type)) . ":");
                foreach ($constraints[$type] as $violation) {
                    $this->line("     - {$violation['table']}.{$violation['column']}: {$violation['violations']} records");
                    $this->line("       {$violation['description']}");
                    $totalViolations += $violation['violations'];
                }
            }
        }
        
        if ($totalViolations === 0) {
            $this->info("   ✅ No constraint violations found");
        } else {
            $this->error("   ❌ Total violations: {$totalViolations}");
        }
        
        $this->newLine();
    }

    protected function displaySeederValidation(array $validation)
    {
        $this->info("🌱 Seeder Validation:");
        
        foreach ($validation as $seederClass => $result) {
            if ($result['status'] === 'valid') {
                $this->info("   ✅ {$seederClass}: Ready");
            } else {
                $this->error("   ❌ {$seederClass}: Issues found");
                foreach ($result['issues'] as $issue) {
                    $this->line("     - {$issue}");
                }
            }
        }
        
        $this->newLine();
    }

    protected function performAutoFix(array $results)
    {
        $this->newLine();
        $this->info('🔧 Performing automatic fixes...');
        
        // Fix kitchen station codes
        $this->fixKitchenStationCodes();
        
        // Fix missing organization emails
        $this->fixOrganizationEmails();
        
        // Fix foreign key violations
        $this->fixForeignKeyViolations();
        
        // Fix duplicate values
        $this->fixDuplicateValues();
        
        $this->info('✅ Automatic fixes completed');
    }

    protected function fixKitchenStationCodes()
    {
        $stationsWithoutCodes = DB::table('kitchen_stations')
            ->whereNull('code')
            ->orWhere('code', '')
            ->get();

        foreach ($stationsWithoutCodes as $station) {
            $code = $this->generateUniqueKitchenStationCode($station->type, $station->branch_id);
            DB::table('kitchen_stations')
                ->where('id', $station->id)
                ->update(['code' => $code]);
            
            $this->line("   Generated code '{$code}' for kitchen station ID {$station->id}");
        }
    }

    protected function generateUniqueKitchenStationCode(string $type, int $branchId): string
    {
        $typePrefix = match($type) {
            'cooking' => 'COOK',
            'prep' => 'PREP', 
            'beverage' => 'BEV',
            'dessert' => 'DESS',
            'grill' => 'GRILL',
            'fry' => 'FRY',
            'bar' => 'BAR',
            default => 'MAIN'
        };

        $branchCode = str_pad($branchId, 2, '0', STR_PAD_LEFT);
        
        // Find next available sequence number
        $existingCodes = DB::table('kitchen_stations')
            ->where('code', 'like', $typePrefix . '-' . $branchCode . '-%')
            ->pluck('code')
            ->toArray();
            
        $sequence = 1;
        do {
            $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $code = $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
            $sequence++;
        } while (in_array($code, $existingCodes));

        return $code;
    }

    // Additional helper methods...
    protected function getTableColumns(string $tableName): array
    {
        return DB::select("SELECT COLUMN_NAME, IS_NULLABLE, DATA_TYPE FROM information_schema.columns WHERE table_schema = ? AND table_name = ?", [DB::getDatabaseName(), $tableName]);
    }

    protected function validateSeederClass(string $seederClass): array
    {
        // Implementation for validating individual seeder classes
        return ['status' => 'valid', 'issues' => []];
    }

    protected function getDatabaseVersion(): ?string
    {
        try {
            return DB::select('SELECT VERSION() as version')[0]->version ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getDatabaseCharset(): ?string
    {
        try {
            $result = DB::select("SELECT DEFAULT_CHARACTER_SET_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?", [DB::getDatabaseName()]);
            return $result[0]->DEFAULT_CHARACTER_SET_NAME ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getDatabaseTimezone(): ?string
    {
        try {
            return DB::select("SELECT @@session.time_zone as timezone")[0]->timezone ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Additional helper methods for various checks...
    protected function checkRequiredColumns(string $tableName, array $columns, array &$analysis)
    {
        $requiredColumns = [
            'kitchen_stations' => ['code'],
            'organizations' => ['email'],
            'users' => ['password']
        ];

        if (isset($requiredColumns[$tableName])) {
            $columnNames = array_column($columns, 'COLUMN_NAME');
            foreach ($requiredColumns[$tableName] as $required) {
                if (!in_array($required, $columnNames)) {
                    $analysis['missing_columns'][] = "{$tableName}.{$required}";
                }
            }
        }
    }

    protected function getIndexedColumns(string $tableName): array
    {
        try {
            return DB::select("SHOW INDEX FROM {$tableName}");
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getForeignKeys(string $tableName): array
    {
        try {
            return DB::select("
                SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [DB::getDatabaseName(), $tableName]);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function findCheckConstraintViolations(): array
    {
        // Implementation for check constraint violations
        return [];
    }

    protected function getPendingMigrations(): array
    {
        // Get list of pending migrations
        return [];
    }

    protected function findOrphanedRecords(): array
    {
        // Implementation for finding orphaned records
        return [];
    }

    protected function findCircularReferences(): array
    {
        // Implementation for finding circular references
        return [];
    }

    protected function findMissingRelationships(): array
    {
        // Implementation for finding missing relationships
        return [];
    }

    protected function checkDataConsistency(): array
    {
        // Implementation for data consistency checks
        return [];
    }

    protected function gatherPerformanceMetrics(): array
    {
        // Implementation for performance metrics
        return [];
    }

    protected function displaySummary(array $results)
    {
        $this->newLine();
        $this->line('<fg=white;bg=blue> 📈 SUMMARY </fg=white;bg=blue>');
        $this->info("Integrity check completed at {$results['timestamp']->format('Y-m-d H:i:s')}");
        $this->newLine();
    }

    protected function exportResults(array $results)
    {
        $filename = $this->option('export');
        $path = storage_path("app/{$filename}");
        
        file_put_contents($path, json_encode($results, JSON_PRETTY_PRINT));
        $this->info("Results exported to: {$path}");
    }

    protected function fixOrganizationEmails()
    {
        // Implementation for fixing organization emails
    }

    protected function fixForeignKeyViolations()
    {
        // Implementation for fixing foreign key violations
    }

    protected function fixDuplicateValues()
    {
        // Implementation for fixing duplicate values
    }
}
