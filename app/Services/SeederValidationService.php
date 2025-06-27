<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class SeederValidationService
{
    protected array $validationErrors = [];
    protected array $dataChecks = [];

    /**
     * Validate data before seeding with comprehensive checks
     */
    public function validateBeforeSeeding(string $seederClass, array $data): array
    {
        $this->validationErrors = [];
        $this->dataChecks = [];
        
        Log::info("🔍 Pre-seeding validation for {$seederClass}");
        
        // Get validation rules for the specific seeder
        $rules = $this->getValidationRules($seederClass);
        
        foreach ($data as $index => $record) {
            $this->validateRecord($seederClass, $record, $rules, $index);
        }
        
        return [
            'valid' => empty($this->validationErrors),
            'errors' => $this->validationErrors,
            'checks_performed' => $this->dataChecks,
            'summary' => $this->generateValidationSummary()
        ];
    }

    /**
     * Get validation rules for specific seeders
     */
    protected function getValidationRules(string $seederClass): array
    {
        $rules = [
            'KitchenStationSeeder' => [
                'required_fields' => ['branch_id', 'name', 'code', 'type'],
                'unique_fields' => ['code'],
                'foreign_keys' => ['branch_id' => 'branches.id'],
                'json_fields' => ['printer_config', 'settings'],
                'enum_fields' => ['type' => ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar']],
                'data_types' => [
                    'branch_id' => 'integer',
                    'name' => 'string',
                    'code' => 'string',
                    'type' => 'string',
                    'is_active' => 'boolean',
                    'max_capacity' => 'numeric',
                    'order_priority' => 'integer'
                ]
            ],
            'OrganizationSeeder' => [
                'required_fields' => ['name', 'email'],
                'unique_fields' => ['email'],
                'foreign_keys' => [],
                'json_fields' => ['plan_snapshot'],
                'enum_fields' => [],
                'data_types' => [
                    'name' => 'string',
                    'email' => 'string',
                    'contact_person' => 'string',
                    'phone_number' => 'string'
                ]
            ],
            'BranchSeeder' => [
                'required_fields' => ['organization_id', 'name'],
                'unique_fields' => [],
                'foreign_keys' => ['organization_id' => 'organizations.id'],
                'json_fields' => [],
                'enum_fields' => [],
                'data_types' => [
                    'organization_id' => 'integer',
                    'name' => 'string',
                    'address' => 'string',
                    'contact_person' => 'string'
                ]
            ],
            'MenuCategorySeeder' => [
                'required_fields' => ['branch_id', 'name'],
                'unique_fields' => [],
                'foreign_keys' => ['branch_id' => 'branches.id'],
                'json_fields' => [],
                'enum_fields' => [],
                'data_types' => [
                    'branch_id' => 'integer',
                    'name' => 'string',
                    'description' => 'string'
                ]
            ]
        ];
        
        return $rules[$seederClass] ?? [];
    }

    /**
     * Validate individual record
     */
    protected function validateRecord(string $seederClass, array $record, array $rules, int $index): void
    {
        $recordId = "Record #{$index}";
        
        // Check required fields
        $this->validateRequiredFields($record, $rules['required_fields'] ?? [], $seederClass, $recordId);
        
        // Check data types
        $this->validateDataTypes($record, $rules['data_types'] ?? [], $seederClass, $recordId);
        
        // Check ENUM values
        $this->validateEnumValues($record, $rules['enum_fields'] ?? [], $seederClass, $recordId);
        
        // Check JSON structure
        $this->validateJsonFields($record, $rules['json_fields'] ?? [], $seederClass, $recordId);
        
        // Check foreign key references
        $this->validateForeignKeys($record, $rules['foreign_keys'] ?? [], $seederClass, $recordId);
        
        // Check unique constraints (when data is provided)
        $this->validateUniqueFields($record, $rules['unique_fields'] ?? [], $seederClass, $recordId);
    }

    /**
     * Validate required fields are present and not null
     */
    protected function validateRequiredFields(array $record, array $requiredFields, string $seeder, string $recordId): void
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $record)) {
                $this->validationErrors[] = "{$seeder} - {$recordId}: Missing required field '{$field}'";
            } elseif (is_null($record[$field]) || $record[$field] === '') {
                $this->validationErrors[] = "{$seeder} - {$recordId}: Required field '{$field}' is null or empty";
            }
        }
        
        $this->dataChecks[] = "Required fields validation for {$recordId}";
    }

    /**
     * Validate data types
     */
    protected function validateDataTypes(array $record, array $dataTypes, string $seeder, string $recordId): void
    {
        foreach ($dataTypes as $field => $expectedType) {
            if (!array_key_exists($field, $record) || is_null($record[$field])) {
                continue; // Skip if field doesn't exist or is null
            }
            
            $value = $record[$field];
            $isValid = match($expectedType) {
                'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
                'string' => is_string($value),
                'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false']),
                'numeric' => is_numeric($value),
                'array' => is_array($value),
                default => true
            };
            
            if (!$isValid) {
                $actualType = gettype($value);
                $this->validationErrors[] = "{$seeder} - {$recordId}: Field '{$field}' expected {$expectedType}, got {$actualType}";
            }
        }
        
        $this->dataChecks[] = "Data type validation for {$recordId}";
    }

    /**
     * Validate ENUM values
     */
    protected function validateEnumValues(array $record, array $enumFields, string $seeder, string $recordId): void
    {
        foreach ($enumFields as $field => $validValues) {
            if (!array_key_exists($field, $record) || is_null($record[$field])) {
                continue;
            }
            
            if (!in_array($record[$field], $validValues)) {
                $validValuesStr = implode(', ', $validValues);
                $this->validationErrors[] = "{$seeder} - {$recordId}: Field '{$field}' has invalid value '{$record[$field]}'. Valid values: {$validValuesStr}";
            }
        }
        
        $this->dataChecks[] = "ENUM validation for {$recordId}";
    }

    /**
     * Validate JSON fields structure
     */
    protected function validateJsonFields(array $record, array $jsonFields, string $seeder, string $recordId): void
    {
        foreach ($jsonFields as $field) {
            if (!array_key_exists($field, $record) || is_null($record[$field])) {
                continue;
            }
            
            $value = $record[$field];
            
            // If it's already an array, it's valid
            if (is_array($value)) {
                continue;
            }
            
            // If it's a string, try to decode it
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->validationErrors[] = "{$seeder} - {$recordId}: Field '{$field}' contains invalid JSON: " . json_last_error_msg();
                }
            } else {
                $this->validationErrors[] = "{$seeder} - {$recordId}: Field '{$field}' should be JSON (array or valid JSON string)";
            }
        }
        
        $this->dataChecks[] = "JSON validation for {$recordId}";
    }

    /**
     * Validate foreign key references
     */
    protected function validateForeignKeys(array $record, array $foreignKeys, string $seeder, string $recordId): void
    {
        foreach ($foreignKeys as $field => $reference) {
            if (!array_key_exists($field, $record) || is_null($record[$field])) {
                continue;
            }
            
            [$table, $column] = explode('.', $reference);
            
            try {
                $exists = DB::table($table)->where($column, $record[$field])->exists();
                if (!$exists) {
                    $this->validationErrors[] = "{$seeder} - {$recordId}: Foreign key '{$field}' value '{$record[$field]}' does not exist in {$reference}";
                }
            } catch (Exception $e) {
                $this->validationErrors[] = "{$seeder} - {$recordId}: Could not validate foreign key '{$field}': " . $e->getMessage();
            }
        }
        
        $this->dataChecks[] = "Foreign key validation for {$recordId}";
    }

    /**
     * Validate unique fields (basic check, not comprehensive)
     */
    protected function validateUniqueFields(array $record, array $uniqueFields, string $seeder, string $recordId): void
    {
        foreach ($uniqueFields as $field) {
            if (!array_key_exists($field, $record) || is_null($record[$field])) {
                continue;
            }
            
            // Basic validation - check if value looks reasonable for unique field
            $value = $record[$field];
            if (is_string($value) && strlen(trim($value)) === 0) {
                $this->validationErrors[] = "{$seeder} - {$recordId}: Unique field '{$field}' cannot be empty string";
            }
        }
        
        $this->dataChecks[] = "Unique fields validation for {$recordId}";
    }

    /**
     * Generate validation summary
     */
    protected function generateValidationSummary(): array
    {
        return [
            'total_errors' => count($this->validationErrors),
            'checks_performed' => count($this->dataChecks),
            'status' => empty($this->validationErrors) ? 'VALID' : 'INVALID',
            'can_proceed' => empty($this->validationErrors)
        ];
    }

    /**
     * Safe seeding with transaction and rollback
     */
    public function safeSeed(callable $seederFunction, string $seederName): array
    {
        $startTime = microtime(true);
        $result = ['success' => false, 'message' => '', 'data' => []];
        
        try {
            DB::beginTransaction();
            
            Log::info("🌱 Starting safe seeding for {$seederName}");
            
            // Execute the seeder function
            $seederResult = $seederFunction();
            
            // Verify the seeding was successful
            $this->verifySeedingResult($seederName);
            
            DB::commit();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $result = [
                'success' => true,
                'message' => "✅ {$seederName} completed successfully in {$duration}ms",
                'data' => $seederResult ?? [],
                'duration_ms' => $duration
            ];
            
            Log::info($result['message']);
            
        } catch (QueryException $e) {
            DB::rollBack();
            
            $result = [
                'success' => false,
                'message' => "❌ {$seederName} failed with database error: " . $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_sql' => $e->getSql() ?? 'N/A'
            ];
            
            Log::error("Database error in {$seederName}", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql' => $e->getSql()
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            $result = [
                'success' => false,
                'message' => "❌ {$seederName} failed with error: " . $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ];
            
            Log::error("General error in {$seederName}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $result;
    }

    /**
     * Verify seeding result
     */
    protected function verifySeedingResult(string $seederName): void
    {
        // Add specific verifications based on seeder
        $verifications = [
            'KitchenStationSeeder' => function() {
                $stationsWithoutCodes = DB::table('kitchen_stations')
                    ->whereNull('code')
                    ->orWhere('code', '')
                    ->count();
                
                if ($stationsWithoutCodes > 0) {
                    throw new Exception("Kitchen stations were created without required codes");
                }
            },
            'OrganizationSeeder' => function() {
                $orgsWithoutEmail = DB::table('organizations')
                    ->whereNull('email')
                    ->orWhere('email', '')
                    ->count();
                
                if ($orgsWithoutEmail > 0) {
                    throw new Exception("Organizations were created without required email");
                }
            }
        ];
        
        if (isset($verifications[$seederName])) {
            $verifications[$seederName]();
        }
    }

    /**
     * Log context for failed seeds
     */
    public function logSeederContext(string $seederName, array $data, Exception $exception): void
    {
        Log::error("Detailed seeder failure context", [
            'seeder' => $seederName,
            'exception' => $exception->getMessage(),
            'data_sample' => array_slice($data, 0, 3), // First 3 records
            'data_count' => count($data),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ]);
    }

    /**
     * Validate seeder requirements and dependencies
     */
    public function validateSeederRequirements(string $seederClass): array
    {
        $issues = [];
        
        try {
            // Check seeder-specific requirements
            switch ($seederClass) {
                case 'KitchenStationSeeder':
                    $issues = array_merge($issues, $this->validateKitchenStationRequirements());
                    break;
                    
                case 'OrganizationSeeder':
                    $issues = array_merge($issues, $this->validateOrganizationRequirements());
                    break;
                    
                case 'BranchSeeder':
                    $issues = array_merge($issues, $this->validateBranchRequirements());
                    break;
                    
                case 'UserSeeder':
                    $issues = array_merge($issues, $this->validateUserRequirements());
                    break;
                    
                case 'DatabaseSeeder':
                    // Validate all seeder requirements for main seeder
                    $issues = array_merge($issues, $this->validateAllSeederRequirements());
                    break;
                    
                default:
                    // Generic validation for unknown seeders
                    $issues = array_merge($issues, $this->validateGenericSeederRequirements($seederClass));
                    break;
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to validate seeder requirements: " . $e->getMessage();
        }
        
        return [
            'issues' => $issues,
            'status' => empty($issues) ? 'passed' : 'failed',
            'seeder_class' => $seederClass
        ];
    }
    
    protected function validateKitchenStationRequirements(): array
    {
        $issues = [];
        
        // Check if branches exist (required for kitchen stations)
        if (!DB::table('branches')->exists()) {
            $issues[] = "KitchenStationSeeder requires branches to exist - run BranchSeeder first";
        }
        
        // Check for existing kitchen station codes that might conflict
        $existingCodes = DB::table('kitchen_stations')->pluck('code')->toArray();
        if (!empty($existingCodes)) {
            $issues[] = "Existing kitchen station codes found - may cause unique constraint violations: " . implode(', ', $existingCodes);
        }
        
        return $issues;
    }
    
    protected function validateBranchRequirements(): array
    {
        $issues = [];
        
        // Check if organizations exist (required for branches)
        if (!DB::table('organizations')->exists()) {
            $issues[] = "BranchSeeder requires organizations to exist - run OrganizationSeeder first";
        }
        
        return $issues;
    }
    
    protected function validateUserRequirements(): array
    {
        $issues = [];
        
        // Check if roles exist
        if (!DB::table('roles')->exists()) {
            $issues[] = "UserSeeder requires roles to exist - ensure role seeding is completed first";
        }
        
        // Check if branches exist for user assignment
        if (!DB::table('branches')->exists()) {
            $issues[] = "UserSeeder requires branches for user assignment - run BranchSeeder first";
        }
        
        return $issues;
    }
    
    protected function validateOrganizationRequirements(): array
    {
        $issues = [];
          // Organizations are typically the root entity, so minimal requirements
        // Check if table structure is correct
        try {
            $columns = Schema::getColumnListing('organizations');
            $requiredColumns = ['name', 'email', 'phone'];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $columns)) {
                    $issues[] = "Organizations table missing required column: {$column}";
                }
            }
        } catch (\Exception $e) {
            $issues[] = "Could not validate organization table structure: " . $e->getMessage();
        }
        
        return $issues;
    }
    
    protected function validateAllSeederRequirements(): array
    {
        $issues = [];
        
        // Check dependency chain for main database seeder
        $seederChain = [
            'OrganizationSeeder' => [],
            'BranchSeeder' => ['organizations'],
            'KitchenStationSeeder' => ['organizations', 'branches'],
            'UserSeeder' => ['organizations', 'branches', 'roles']
        ];
        
        foreach ($seederChain as $seeder => $dependencies) {
            foreach ($dependencies as $table) {
                if (!DB::table($table)->exists()) {
                    $issues[] = "{$seeder} dependency missing: {$table} table has no data";
                }
            }
        }
        
        return $issues;
    }
    
    protected function validateGenericSeederRequirements(string $seederClass): array
    {
        $issues = [];
        
        // Generic checks for unknown seeders
        if (!class_exists($seederClass)) {
            $issues[] = "Seeder class '{$seederClass}' does not exist";
        }
        
        return $issues;
    }
}
