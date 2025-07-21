<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\KitchenStation;
use Exception;

class DatabaseIntegrityService
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $validationResults = [];

    /**
     * Comprehensive database integrity check
     */
    public function validateDatabaseIntegrity(): array
    {
        $this->errors = [];
        $this->warnings = [];

        Log::info('🔍 Starting comprehensive database integrity check...');

        $checks = [
            'checkTableConstraints',
            'validateRequiredColumns',
            'checkForeignKeyIntegrity',
            'validateJsonColumns',
            'checkUniqueConstraints',
            'validateEnumValues',
            'checkIndexIntegrity',
            'validateSeederData'
        ];

        foreach ($checks as $check) {
            try {
                $this->$check();
            } catch (Exception $e) {
                $this->errors[] = "Error in {$check}: " . $e->getMessage();
                Log::error("Database integrity check failed in {$check}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'status' => empty($this->errors) ? 'passed' : 'failed',
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'summary' => $this->generateSummary()
        ];
    }

    /**
     * Check table constraints and NOT NULL violations
     */
    protected function checkTableConstraints(): void
    {
        Log::info('🔎 Checking table constraints...');

        $criticalTables = [
            'organizations' => ['name', 'email'],
            'branches' => ['organization_id', 'name'],
            'kitchen_stations' => ['branch_id', 'name', 'code'],
            'menu_categories' => ['branch_id', 'name'],
            'menu_items' => ['branch_id', 'name', 'price'],
            'orders' => ['branch_id', 'status'],
            'users' => ['name', 'email']
        ];

        foreach ($criticalTables as $table => $requiredColumns) {
            if (!Schema::hasTable($table)) {
                $this->warnings[] = "Table '{$table}' does not exist";
                continue;
            }

            $existingColumns = Schema::getColumnListing($table);

            foreach ($requiredColumns as $column) {
                if (!in_array($column, $existingColumns)) {
                    $this->errors[] = "Required column '{$column}' missing in table '{$table}'";
                    continue;
                }

                // Check for NULL values in required columns
                try {
                    $nullCount = DB::table($table)->whereNull($column)->count();
                    if ($nullCount > 0) {
                        $this->errors[] = "Table '{$table}' has {$nullCount} NULL values in required column '{$column}'";
                    }
                } catch (Exception $e) {
                    $this->warnings[] = "Could not check NULL values in {$table}.{$column}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Validate required columns exist and have proper types
     */
    protected function validateRequiredColumns(): void
    {
        Log::info('🔎 Validating required columns...');

        $columnValidations = [
            'kitchen_stations' => [
                'code' => ['type' => 'string', 'nullable' => false, 'unique' => true],
                'printer_config' => ['type' => 'json', 'nullable' => true],
                'settings' => ['type' => 'json', 'nullable' => true],
                'max_capacity' => ['type' => 'decimal', 'nullable' => true]
            ],
            'organizations' => [
                'subscription_plan_id' => ['type' => 'integer', 'nullable' => true]
            ]
        ];

        foreach ($columnValidations as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column => $rules) {
                if (!Schema::hasColumn($table, $column)) {
                    $this->errors[] = "Required column '{$column}' missing in table '{$table}'";
                }
            }
        }
    }

    /**
     * Check foreign key integrity
     */
    protected function checkForeignKeyIntegrity(): void
    {
        Log::info('🔎 Checking foreign key integrity...');

        $foreignKeys = [
            'branches.organization_id' => 'organizations.id',
            'kitchen_stations.branch_id' => 'branches.id',
            'menu_categories.branch_id' => 'branches.id',
            'menu_items.branch_id' => 'branches.id',
            'orders.branch_id' => 'branches.id'
        ];

        foreach ($foreignKeys as $childColumn => $parentColumn) {
            [$childTable, $childCol] = explode('.', $childColumn);
            [$parentTable, $parentCol] = explode('.', $parentColumn);

            if (!Schema::hasTable($childTable) || !Schema::hasTable($parentTable)) {
                continue;
            }

            try {
                $orphanedRecords = DB::table($childTable)
                    ->leftJoin($parentTable, "{$childTable}.{$childCol}", '=', "{$parentTable}.{$parentCol}")
                    ->whereNull("{$parentTable}.{$parentCol}")
                    ->whereNotNull("{$childTable}.{$childCol}")
                    ->count();

                if ($orphanedRecords > 0) {
                    $this->errors[] = "Found {$orphanedRecords} orphaned records in {$childTable}.{$childCol}";
                }
            } catch (Exception $e) {
                $this->warnings[] = "Could not check foreign key {$childColumn}: " . $e->getMessage();
            }
        }
    }

    /**
     * Validate JSON columns structure
     */
    protected function validateJsonColumns(): void
    {
        Log::info('🔎 Validating JSON columns...');

        $jsonColumns = [
            'kitchen_stations' => ['printer_config', 'settings'],
        ];

        foreach ($jsonColumns as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                try {
                    $invalidJson = DB::table($table)
                        ->whereRaw("JSON_VALID({$column}) = 0")
                        ->whereNotNull($column)
                        ->count();

                    if ($invalidJson > 0) {
                        $this->errors[] = "Found {$invalidJson} invalid JSON values in {$table}.{$column}";
                    }
                } catch (Exception $e) {
                    // MySQL-specific function, might not work on all databases
                    $this->warnings[] = "Could not validate JSON in {$table}.{$column}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Check unique constraints
     */
    protected function checkUniqueConstraints(): void
    {
        Log::info('🔎 Checking unique constraints...');

        $uniqueConstraints = [
            'kitchen_stations' => ['code'],
            'organizations' => ['email'],
            'users' => ['email']
        ];

        foreach ($uniqueConstraints as $table => $columns) {
            if (!Schema::hasTable($table)) continue;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) continue;

                try {
                    $duplicates = DB::table($table)
                        ->select($column, DB::raw('COUNT(*) as count'))
                        ->whereNotNull($column)
                        ->groupBy($column)
                        ->having('count', '>', 1)
                        ->count();

                    if ($duplicates > 0) {
                        $this->errors[] = "Found {$duplicates} duplicate values in unique column {$table}.{$column}";
                    }
                } catch (Exception $e) {
                    $this->warnings[] = "Could not check uniqueness in {$table}.{$column}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Validate ENUM values
     */
    protected function validateEnumValues(): void
    {
        Log::info('🔎 Validating ENUM values...');

        $enumValidations = [
            'kitchen_stations.type' => ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'],
            'orders.status' => ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled']
        ];

        foreach ($enumValidations as $column => $validValues) {
            [$table, $col] = explode('.', $column);

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col)) continue;

            try {
                $invalidValues = DB::table($table)
                    ->whereNotIn($col, $validValues)
                    ->whereNotNull($col)
                    ->count();

                if ($invalidValues > 0) {
                    $this->errors[] = "Found {$invalidValues} invalid ENUM values in {$table}.{$col}";
                }
            } catch (Exception $e) {
                $this->warnings[] = "Could not validate ENUM values in {$table}.{$col}: " . $e->getMessage();
            }
        }
    }

    /**
     * Check index integrity
     */
    protected function checkIndexIntegrity(): void
    {
        Log::info('🔎 Checking index integrity...');

        $requiredIndexes = [
            'kitchen_stations' => ['kitchen_stations_code_unique', 'kitchen_stations_branch_id_index'],
            'organizations' => ['organizations_email_unique'],
            'branches' => ['branches_organization_id_index']
        ];

        foreach ($requiredIndexes as $table => $indexes) {
            if (!Schema::hasTable($table)) continue;

            try {
                $existingIndexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                    ->pluck('Key_name')
                    ->unique()
                    ->toArray();

                foreach ($indexes as $index) {
                    if (!in_array($index, $existingIndexes)) {
                        $this->warnings[] = "Missing index '{$index}' on table '{$table}'";
                    }
                }
            } catch (Exception $e) {
                $this->warnings[] = "Could not check indexes for table {$table}: " . $e->getMessage();
            }
        }
    }

    /**
     * Validate seeder data integrity
     */
    protected function validateSeederData(): void
    {
        Log::info('🔎 Validating seeder data...');

        // Check if organizations exist
        $orgCount = Organization::count();
        if ($orgCount === 0) {
            $this->warnings[] = "No organizations found in database";
        }

        // Check if branches exist for organizations
        $orgsWithoutBranches = Organization::doesntHave('branches')->count();
        if ($orgsWithoutBranches > 0) {
            $this->warnings[] = "{$orgsWithoutBranches} organizations have no branches";
        }

        // Check kitchen stations
        $branchesWithoutStations = Branch::doesntHave('kitchenStations')->count();
        if ($branchesWithoutStations > 0) {
            $this->warnings[] = "{$branchesWithoutStations} branches have no kitchen stations";
        }

        // Check for kitchen stations without codes
        $stationsWithoutCodes = KitchenStation::whereNull('code')->orWhere('code', '')->count();
        if ($stationsWithoutCodes > 0) {
            $this->errors[] = "{$stationsWithoutCodes} kitchen stations are missing required codes";
        }
    }

    /**
     * Generate summary of checks
     */
    protected function generateSummary(): array
    {
        return [
            'total_errors' => count($this->errors),
            'total_warnings' => count($this->warnings),
            'status' => empty($this->errors) ? 'PASSED' : 'FAILED',
            'recommendations' => $this->generateRecommendations()
        ];
    }

    /**
     * Generate recommendations based on found issues
     */
    protected function generateRecommendations(): array
    {
        $recommendations = [];

        if (!empty($this->errors)) {
            $recommendations[] = "🚨 Critical errors found - database seeding may fail";
            $recommendations[] = "Run 'php artisan db:fix-integrity' to auto-fix common issues";
        }

        if (!empty($this->warnings)) {
            $recommendations[] = "⚠️ Warnings found - review for optimal performance";
        }

        if (empty($this->errors) && empty($this->warnings)) {
            $recommendations[] = "✅ Database integrity is excellent!";
        }

        return $recommendations;
    }

    /**
     * Fix common database integrity issues
     */
    public function fixIntegrityIssues(): array
    {
        $fixes = [];

        try {
            DB::beginTransaction();

            // Fix kitchen stations without codes
            $this->fixKitchenStationCodes($fixes);

            // Fix invalid JSON columns
            $this->fixInvalidJsonColumns($fixes);

            // Fix orphaned foreign keys
            $this->fixOrphanedRecords($fixes);

            DB::commit();

            Log::info('✅ Database integrity fixes completed successfully', $fixes);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Database integrity fix failed', [
                'error' => $e->getMessage(),
                'fixes_attempted' => $fixes
            ]);

            throw $e;
        }

        return $fixes;
    }

    /**
     * Fix kitchen stations without codes
     */
    protected function fixKitchenStationCodes(array &$fixes): void
    {
        $stationsWithoutCodes = KitchenStation::whereNull('code')->orWhere('code', '')->get();

        foreach ($stationsWithoutCodes as $station) {
            $newCode = $this->generateUniqueStationCode($station);
            $station->update(['code' => $newCode]);

            $fixes[] = "Generated code '{$newCode}' for kitchen station ID {$station->id}";
        }
    }

    /**
     * Generate unique kitchen station code
     */
    protected function generateUniqueStationCode(KitchenStation $station): string
    {
        $typePrefix = match($station->type) {
            'cooking' => 'COOK',
            'prep' => 'PREP',
            'beverage' => 'BEV',
            'dessert' => 'DESS',
            'grill' => 'GRILL',
            'fry' => 'FRY',
            'bar' => 'BAR',
            default => 'MAIN'
        };

        $branchCode = str_pad($station->branch_id, 2, '0', STR_PAD_LEFT);

        // Find next available sequence number
        $sequence = 1;
        do {
            $code = $typePrefix . '-' . $branchCode . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $exists = KitchenStation::where('code', $code)->where('id', '!=', $station->id)->exists();
            $sequence++;
        } while ($exists && $sequence < 1000);

        return $code;
    }

    /**
     * Fix invalid JSON columns
     */
    protected function fixInvalidJsonColumns(array &$fixes): void
    {
        // Fix kitchen stations with invalid printer_config
        $stations = KitchenStation::whereNotNull('printer_config')->get();

        foreach ($stations as $station) {
            if (!is_array($station->printer_config)) {
                $defaultConfig = [
                    'printer_ip' => '192.168.1.100',
                    'printer_name' => $station->name . ' Printer',
                    'paper_size' => '80mm',
                    'auto_print' => false,
                    'print_logo' => true,
                    'print_quality' => 'standard'
                ];

                $station->update(['printer_config' => $defaultConfig]);
                $fixes[] = "Fixed printer_config for kitchen station ID {$station->id}";
            }
        }
    }

    /**
     * Fix orphaned records
     */
    protected function fixOrphanedRecords(array &$fixes): void
    {
        // This would be too dangerous to auto-fix, just log them
        $orphanedBranches = Branch::whereDoesntHave('organization')->count();
        if ($orphanedBranches > 0) {
            $fixes[] = "Found {$orphanedBranches} orphaned branches - manual review required";
        }

        $orphanedStations = KitchenStation::whereDoesntHave('branch')->count();
        if ($orphanedStations > 0) {
            $fixes[] = "Found {$orphanedStations} orphaned kitchen stations - manual review required";
        }
    }
}
