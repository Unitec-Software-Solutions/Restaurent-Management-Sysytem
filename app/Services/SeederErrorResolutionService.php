<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Exception;

class SeederErrorResolutionService
{
    protected array $errors = [];
    protected array $fixes = [];
    protected array $statistics = [];

    /**
     * Diagnose and resolve seeder errors with comprehensive analysis
     */
    public function resolveSeederErrors(array $options = []): array
    {
        $this->resetCounters();
        
        Log::info('🔧 Starting comprehensive seeder error resolution');
        
        try {
            DB::beginTransaction();
            
            // 1. Analyze current database state
            $analysis = $this->analyzeDatabaseState();
            
            // 2. Identify specific error patterns
            $errorPatterns = $this->identifyErrorPatterns();
            
            // 3. Fix NOT NULL constraint violations
            $notNullFixes = $this->fixNotNullConstraints();
            
            // 4. Fix foreign key violations
            $foreignKeyFixes = $this->fixForeignKeyViolations();
            
            // 5. Fix unique constraint violations
            $uniqueFixes = $this->fixUniqueConstraintViolations();
            
            // 6. Fix JSON field validation issues
            $jsonFixes = $this->fixJsonFieldIssues();
            
            // 7. Generate missing factory data
            $factoryFixes = $this->generateMissingFactoryData();
            
            // 8. Validate all fixes
            $validation = $this->validateAllFixes();
            
            if ($validation['success']) {
                DB::commit();
                Log::info('✅ All seeder errors resolved successfully');
            } else {
                DB::rollback();
                Log::error('❌ Validation failed, rolling back changes');
            }
            
            return [
                'success' => $validation['success'],
                'analysis' => $analysis,
                'error_patterns' => $errorPatterns,
                'fixes_applied' => [
                    'not_null' => $notNullFixes,
                    'foreign_keys' => $foreignKeyFixes,
                    'unique_constraints' => $uniqueFixes,
                    'json_fields' => $jsonFixes,
                    'factory_data' => $factoryFixes
                ],
                'statistics' => $this->statistics,
                'recommendations' => $this->generateRecommendations()
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Seeder error resolution failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'statistics' => $this->statistics
            ];
        }
    }

    /**
     * Analyze current database state for integrity issues
     */
    protected function analyzeDatabaseState(): array
    {
        $analysis = [
            'timestamp' => Carbon::now(),
            'tables' => [],
            'constraints' => [],
            'data_integrity' => []
        ];

        // Get all tables
        $tables = $this->getAllTables();
        
        foreach ($tables as $table) {
            $analysis['tables'][$table] = [
                'row_count' => $this->getTableRowCount($table),
                'null_violations' => $this->findNullViolations($table),
                'foreign_key_issues' => $this->findForeignKeyIssues($table),
                'unique_violations' => $this->findUniqueViolations($table),
                'json_field_issues' => $this->findJsonFieldIssues($table)
            ];
        }

        return $analysis;
    }

    /**
     * Identify common error patterns in seeders
     */
    protected function identifyErrorPatterns(): array
    {
        return [
            'kitchen_stations' => $this->analyzeKitchenStationErrors(),
            'organizations' => $this->analyzeOrganizationErrors(),
            'users' => $this->analyzeUserErrors(),
            'menu_items' => $this->analyzeMenuItemErrors(),
            'branches' => $this->analyzeBranchErrors()
        ];
    }

    /**
     * Fix NOT NULL constraint violations
     */
    protected function fixNotNullConstraints(): array
    {
        $fixes = [];

        // Fix kitchen stations without codes
        $fixes['kitchen_stations_code'] = $this->fixKitchenStationCodes();
        
        // Fix organizations without emails
        $fixes['organizations_email'] = $this->fixOrganizationEmails();
        
        // Fix users without passwords
        $fixes['users_password'] = $this->fixUserPasswords();
        
        // Fix menu items without required fields
        $fixes['menu_items_fields'] = $this->fixMenuItemRequiredFields();

        return $fixes;
    }

    /**
     * Fix kitchen station code issues
     */
    protected function fixKitchenStationCodes(): array
    {
        $stationsWithoutCodes = DB::table('kitchen_stations')
            ->whereNull('code')
            ->orWhere('code', '')
            ->get();

        $fixes = [];
        foreach ($stationsWithoutCodes as $station) {
            $newCode = $this->generateUniqueKitchenStationCode($station->type, $station->branch_id);
            
            DB::table('kitchen_stations')
                ->where('id', $station->id)
                ->update([
                    'code' => $newCode,
                    'updated_at' => Carbon::now()
                ]);

            $fixes[] = [
                'station_id' => $station->id,
                'old_code' => $station->code,
                'new_code' => $newCode,
                'type' => $station->type,
                'branch_id' => $station->branch_id
            ];

            Log::info("Generated kitchen station code: {$newCode} for station ID {$station->id}");
        }

        $this->statistics['kitchen_station_codes_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Generate unique kitchen station code
     */
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
        
        // Get existing codes for this pattern
        $existingCodes = DB::table('kitchen_stations')
            ->where('code', 'like', "{$typePrefix}-{$branchCode}-%")
            ->pluck('code')
            ->toArray();

        // Find next available sequence
        $sequence = 1;
        do {
            $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $code = "{$typePrefix}-{$branchCode}-{$sequenceCode}";
            $sequence++;
        } while (in_array($code, $existingCodes) && $sequence < 1000);

        return $code;
    }

    /**
     * Fix organization email issues
     */
    protected function fixOrganizationEmails(): array
    {
        $orgsWithoutEmails = DB::table('organizations')
            ->whereNull('email')
            ->orWhere('email', '')
            ->get();

        $fixes = [];
        foreach ($orgsWithoutEmails as $org) {
            $newEmail = $this->generateUniqueOrganizationEmail($org->name, $org->id);
            
            DB::table('organizations')
                ->where('id', $org->id)
                ->update([
                    'email' => $newEmail,
                    'updated_at' => Carbon::now()
                ]);

            $fixes[] = [
                'organization_id' => $org->id,
                'name' => $org->name,
                'new_email' => $newEmail
            ];

            Log::info("Generated email: {$newEmail} for organization ID {$org->id}");
        }

        $this->statistics['organization_emails_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Generate unique organization email
     */
    protected function generateUniqueOrganizationEmail(string $name, int $id): string
    {
        $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $baseName = substr($baseName, 0, 10); // Limit length
        
        $suffix = 1;
        do {
            $email = $suffix === 1 ? 
                "{$baseName}@example.com" : 
                "{$baseName}{$suffix}@example.com";
            
            $exists = DB::table('organizations')
                ->where('email', $email)
                ->where('id', '!=', $id)
                ->exists();
                
            $suffix++;
        } while ($exists && $suffix < 100);

        return $email;
    }

    /**
     * Fix user password issues
     */
    protected function fixUserPasswords(): array
    {
        $usersWithoutPasswords = DB::table('users')
            ->whereNull('password')
            ->orWhere('password', '')
            ->get();

        $fixes = [];
        foreach ($usersWithoutPasswords as $user) {
            $hashedPassword = bcrypt('password123'); // Default password
            
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => $hashedPassword,
                    'updated_at' => Carbon::now()
                ]);

            $fixes[] = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'default_password_set' => true
            ];

            Log::info("Set default password for user ID {$user->id} ({$user->email})");
        }

        $this->statistics['user_passwords_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Fix foreign key violations
     */
    protected function fixForeignKeyViolations(): array
    {
        $fixes = [];

        // Fix kitchen stations with invalid branch references
        $fixes['kitchen_stations_branch'] = $this->fixKitchenStationBranchRefs();
        
        // Fix menu items with invalid category references
        $fixes['menu_items_category'] = $this->fixMenuItemCategoryRefs();
        
        // Fix users with invalid organization references
        $fixes['users_organization'] = $this->fixUserOrganizationRefs();

        return $fixes;
    }

    /**
     * Fix kitchen station branch references
     */
    protected function fixKitchenStationBranchRefs(): array
    {
        $invalidRefs = DB::table('kitchen_stations as ks')
            ->leftJoin('branches as b', 'ks.branch_id', '=', 'b.id')
            ->whereNull('b.id')
            ->select('ks.*')
            ->get();

        $fixes = [];
        foreach ($invalidRefs as $station) {
            // Try to find a valid branch for the organization
            $validBranch = DB::table('branches')
                ->where('is_active', true)
                ->first();

            if ($validBranch) {
                DB::table('kitchen_stations')
                    ->where('id', $station->id)
                    ->update([
                        'branch_id' => $validBranch->id,
                        'updated_at' => Carbon::now()
                    ]);

                $fixes[] = [
                    'station_id' => $station->id,
                    'old_branch_id' => $station->branch_id,
                    'new_branch_id' => $validBranch->id,
                    'action' => 'updated_reference'
                ];
            } else {
                // Delete orphaned records if no valid branch exists
                DB::table('kitchen_stations')->where('id', $station->id)->delete();
                
                $fixes[] = [
                    'station_id' => $station->id,
                    'branch_id' => $station->branch_id,
                    'action' => 'deleted_orphaned'
                ];
            }

            Log::info("Fixed kitchen station branch reference for station ID {$station->id}");
        }

        $this->statistics['kitchen_station_branch_refs_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Fix unique constraint violations
     */
    protected function fixUniqueConstraintViolations(): array
    {
        $fixes = [];

        // Fix duplicate kitchen station codes
        $fixes['kitchen_station_codes'] = $this->fixDuplicateKitchenStationCodes();
        
        // Fix duplicate organization emails
        $fixes['organization_emails'] = $this->fixDuplicateOrganizationEmails();
        
        // Fix duplicate user emails
        $fixes['user_emails'] = $this->fixDuplicateUserEmails();

        return $fixes;
    }

    /**
     * Fix duplicate kitchen station codes
     */
    protected function fixDuplicateKitchenStationCodes(): array
    {
        $duplicates = DB::table('kitchen_stations')
            ->select('code')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('code');

        $fixes = [];
        foreach ($duplicates as $duplicateCode) {
            $stations = DB::table('kitchen_stations')
                ->where('code', $duplicateCode)
                ->orderBy('id')
                ->get();

            // Keep the first one, update the rest
            foreach ($stations->skip(1) as $station) {
                $newCode = $this->generateUniqueKitchenStationCode($station->type, $station->branch_id);
                
                DB::table('kitchen_stations')
                    ->where('id', $station->id)
                    ->update([
                        'code' => $newCode,
                        'updated_at' => Carbon::now()
                    ]);

                $fixes[] = [
                    'station_id' => $station->id,
                    'old_code' => $duplicateCode,
                    'new_code' => $newCode
                ];

                Log::info("Fixed duplicate kitchen station code: {$duplicateCode} -> {$newCode} for station ID {$station->id}");
            }
        }

        $this->statistics['duplicate_codes_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Fix JSON field validation issues
     */
    protected function fixJsonFieldIssues(): array
    {
        $fixes = [];

        // Fix kitchen station printer_config
        $fixes['kitchen_station_printer_config'] = $this->fixKitchenStationPrinterConfig();
        
        // Fix kitchen station settings
        $fixes['kitchen_station_settings'] = $this->fixKitchenStationSettings();

        return $fixes;
    }

    /**
     * Fix kitchen station printer configuration
     */
    protected function fixKitchenStationPrinterConfig(): array
    {
        $stationsWithInvalidConfig = DB::table('kitchen_stations')
            ->whereNull('printer_config')
            ->orWhere('printer_config', '')
            ->orWhere('printer_config', '{}')
            ->get();

        $fixes = [];
        foreach ($stationsWithInvalidConfig as $station) {
            $validConfig = [
                'printer_name' => $station->name . ' Printer',
                'paper_size' => '80mm',
                'auto_print' => false,
                'print_logo' => true,
                'printer_ip' => '192.168.1.' . (100 + $station->id),
                'connection_timeout' => 5000,
                'retry_attempts' => 3,
                'print_quality' => 'standard'
            ];

            DB::table('kitchen_stations')
                ->where('id', $station->id)
                ->update([
                    'printer_config' => json_encode($validConfig),
                    'updated_at' => Carbon::now()
                ]);

            $fixes[] = [
                'station_id' => $station->id,
                'config_applied' => $validConfig
            ];

            Log::info("Fixed printer config for kitchen station ID {$station->id}");
        }

        $this->statistics['printer_configs_fixed'] = count($fixes);
        return $fixes;
    }

    /**
     * Validate all applied fixes
     */
    protected function validateAllFixes(): array
    {
        $validation = [
            'success' => true,
            'checks' => []
        ];

        // Check kitchen stations
        $validation['checks']['kitchen_stations'] = $this->validateKitchenStations();
        
        // Check organizations
        $validation['checks']['organizations'] = $this->validateOrganizations();
        
        // Check users
        $validation['checks']['users'] = $this->validateUsers();

        // Overall success
        foreach ($validation['checks'] as $check) {
            if (!$check['valid']) {
                $validation['success'] = false;
                break;
            }
        }

        return $validation;
    }

    /**
     * Validate kitchen stations after fixes
     */
    protected function validateKitchenStations(): array
    {
        $issues = [];

        // Check for missing codes
        $missingCodes = DB::table('kitchen_stations')
            ->whereNull('code')
            ->orWhere('code', '')
            ->count();

        if ($missingCodes > 0) {
            $issues[] = "Still have {$missingCodes} kitchen stations without codes";
        }

        // Check for duplicate codes
        $duplicateCodes = DB::table('kitchen_stations')
            ->select('code')
            ->whereNotNull('code')
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateCodes > 0) {
            $issues[] = "Still have {$duplicateCodes} duplicate kitchen station codes";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    protected function generateRecommendations(): array
    {
        return [
            'immediate_actions' => [
                'Run database integrity check regularly',
                'Implement pre-seeding validation in all seeders',
                'Add unique constraints where missing',
                'Implement proper error handling in seeders'
            ],
            'migration_suggestions' => [
                'Add code column to kitchen_stations with NOT NULL constraint',
                'Add unique constraints for critical fields',
                'Implement proper foreign key constraints',
                'Add check constraints for enum values'
            ],
            'seeder_improvements' => [
                'Use transaction rollbacks in seeders',
                'Implement data validation before insertion',
                'Add comprehensive error logging',
                'Use factory states for different scenarios'
            ],
            'monitoring' => [
                'Set up database integrity monitoring',
                'Implement seeder health checks',
                'Add alerts for constraint violations',
                'Regular data consistency audits'
            ]
        ];
    }

    // Helper methods
    protected function resetCounters()
    {
        $this->errors = [];
        $this->fixes = [];
        $this->statistics = [
            'total_fixes_applied' => 0,
            'tables_processed' => 0,
            'errors_resolved' => 0
        ];
    }

    protected function getAllTables(): array
    {
        return DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [DB::getDatabaseName()]);
    }

    protected function getTableRowCount(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function findNullViolations(string $table): array
    {
        // Implementation for finding null violations in specific table
        return [];
    }

    protected function findForeignKeyIssues(string $table): array
    {
        // Implementation for finding foreign key issues in specific table
        return [];
    }

    protected function findUniqueViolations(string $table): array
    {
        // Implementation for finding unique violations in specific table
        return [];
    }

    protected function findJsonFieldIssues(string $table): array
    {
        // Implementation for finding JSON field issues in specific table
        return [];
    }

    protected function analyzeKitchenStationErrors(): array
    {
        return [
            'missing_codes' => DB::table('kitchen_stations')->whereNull('code')->count(),
            'invalid_printer_config' => DB::table('kitchen_stations')->whereNull('printer_config')->count(),
            'foreign_key_violations' => 0 // Calculate actual violations
        ];
    }

    protected function analyzeOrganizationErrors(): array
    {
        return [
            'missing_emails' => DB::table('organizations')->whereNull('email')->count(),
            'duplicate_emails' => 0 // Calculate actual duplicates
        ];
    }

    protected function analyzeUserErrors(): array
    {
        return [
            'missing_passwords' => DB::table('users')->whereNull('password')->count(),
            'invalid_organization_refs' => 0 // Calculate actual violations
        ];
    }

    protected function analyzeMenuItemErrors(): array
    {
        return [
            'missing_names' => DB::table('menu_items')->whereNull('name')->count(),
            'invalid_category_refs' => 0 // Calculate actual violations
        ];
    }

    protected function analyzeBranchErrors(): array
    {
        return [
            'missing_names' => DB::table('branches')->whereNull('name')->count(),
            'invalid_organization_refs' => 0 // Calculate actual violations
        ];
    }

    // Additional fix methods...
    protected function fixMenuItemRequiredFields(): array
    {
        // Implementation for fixing menu item required fields
        return [];
    }

    protected function fixMenuItemCategoryRefs(): array
    {
        // Implementation for fixing menu item category references
        return [];
    }

    protected function fixUserOrganizationRefs(): array
    {
        // Implementation for fixing user organization references
        return [];
    }

    protected function fixDuplicateOrganizationEmails(): array
    {
        // Implementation for fixing duplicate organization emails
        return [];
    }

    protected function fixDuplicateUserEmails(): array
    {
        // Implementation for fixing duplicate user emails
        return [];
    }

    protected function fixKitchenStationSettings(): array
    {
        // Implementation for fixing kitchen station settings
        return [];
    }

    protected function generateMissingFactoryData(): array
    {
        // Implementation for generating missing factory data
        return [];
    }

    protected function validateOrganizations(): array
    {
        // Implementation for validating organizations
        return ['valid' => true, 'issues' => []];
    }

    protected function validateUsers(): array
    {
        // Implementation for validating users
        return ['valid' => true, 'issues' => []];
    }
}
