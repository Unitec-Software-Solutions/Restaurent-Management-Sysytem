<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        try {
            // Check if kitchen_stations table exists
            if (!Schema::hasTable('kitchen_stations')) {
                Log::warning('kitchen_stations table does not exist');
                return;
            }

            // Get current constraint information using PostgreSQL 12+ compatible query
            $constraints = DB::select("
                SELECT 
                    conname,
                    pg_get_constraintdef(oid) as constraint_definition
                FROM pg_constraint 
                WHERE conrelid = (SELECT oid FROM pg_class WHERE relname = 'kitchen_stations')
                AND contype = 'c'
                AND conname LIKE '%type%'
            ");

            Log::info('Current type constraints found: ' . count($constraints));
            
            foreach ($constraints as $constraint) {
                Log::info("Constraint: {$constraint->conname} - Definition: {$constraint->constraint_definition}");
            }

            // Drop the existing type constraint(s)
            foreach ($constraints as $constraint) {
                if (strpos($constraint->conname, 'type') !== false) {
                    try {
                        DB::statement("ALTER TABLE kitchen_stations DROP CONSTRAINT IF EXISTS {$constraint->conname}");
                        Log::info("Dropped constraint: {$constraint->conname}");
                    } catch (\Exception $e) {
                        Log::warning("Could not drop constraint {$constraint->conname}: " . $e->getMessage());
                    }
                }
            }

            // Also try to drop common constraint names that might exist
            $commonConstraintNames = [
                'kitchen_stations_type_check',
                'kitchen_stations_type_constraint',
                'chk_kitchen_stations_type'
            ];

            foreach ($commonConstraintNames as $constraintName) {
                try {
                    DB::statement("ALTER TABLE kitchen_stations DROP CONSTRAINT IF EXISTS {$constraintName}");
                    Log::info("Attempted to drop constraint: {$constraintName}");
                } catch (\Exception $e) {
                    // Constraint probably doesn't exist, which is fine
                    Log::debug("Constraint {$constraintName} does not exist or could not be dropped");
                }
            }

            // Add new comprehensive type constraint for restaurant operations
            $allowedTypes = [
                'cooking',
                'preparation',  // This was missing and causing the error!
                'prep',
                'beverage',
                'dessert',
                'grill',
                'grilling',
                'fry',
                'bar',
                'pastry',
                'salad',
                'cold_kitchen',
                'hot_kitchen',
                'expo',
                'service'
            ];

            $typeValues = "'" . implode("','", $allowedTypes) . "'";
            
            // Create the new constraint with a unique name
            $constraintName = 'kitchen_stations_type_check_' . time();
            
            DB::statement("
                ALTER TABLE kitchen_stations 
                ADD CONSTRAINT {$constraintName}
                CHECK (type IN ({$typeValues}))
            ");

            Log::info("Added comprehensive type constraint '{$constraintName}' with values: " . $typeValues);

            // Update any existing records with invalid types
            $this->updateInvalidTypes();

            // Verify the constraint was added successfully
            $newConstraints = DB::select("
                SELECT conname, pg_get_constraintdef(oid) as constraint_definition
                FROM pg_constraint 
                WHERE conrelid = (SELECT oid FROM pg_class WHERE relname = 'kitchen_stations')
                AND contype = 'c'
                AND conname = '{$constraintName}'
            ");

            if (count($newConstraints) > 0) {
                Log::info("✅ Successfully added new type constraint: {$constraintName}");
            } else {
                Log::warning("⚠️ New constraint may not have been added properly");
            }

        } catch (\Exception $e) {
            Log::error('Error fixing kitchen stations type constraint: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update any existing records with invalid type values for PostgreSQL
     */
    private function updateInvalidTypes(): void
    {
        try {
            // First, check what types currently exist in the table
            $existingTypes = DB::select("
                SELECT DISTINCT type, COUNT(*) as count
                FROM kitchen_stations 
                GROUP BY type
                ORDER BY count DESC
            ");

            Log::info('Current types in kitchen_stations table:');
            foreach ($existingTypes as $typeInfo) {
                Log::info("  - {$typeInfo->type}: {$typeInfo->count} stations");
            }

            // Map old/invalid types to valid ones
            $typeMapping = [
                'cold_prep' => 'preparation',
                'cold' => 'preparation',
                'hot' => 'cooking',
                'main' => 'cooking',
                'drink' => 'beverage',
                'drinks' => 'beverage',
                'sweet' => 'dessert',
                'sweets' => 'dessert',
                'bbq' => 'grill',
                'barbeque' => 'grill',
                'grilling' => 'grill'  // Ensure this maps correctly
            ];

            foreach ($typeMapping as $oldType => $newType) {
                $updated = DB::table('kitchen_stations')
                    ->where('type', $oldType)
                    ->update(['type' => $newType]);
                
                if ($updated > 0) {
                    Log::info("✅ Updated {$updated} stations from type '{$oldType}' to '{$newType}'");
                }
            }

            // Check for any remaining invalid types using the valid types list
            $validTypes = [
                'cooking', 'preparation', 'prep', 'beverage', 'dessert', 'grill', 'grilling',
                'fry', 'bar', 'pastry', 'salad', 'cold_kitchen', 'hot_kitchen', 'expo', 'service'
            ];

            $validTypesString = "'" . implode("','", $validTypes) . "'";
            
            $invalidTypes = DB::select("
                SELECT DISTINCT type, COUNT(*) as count
                FROM kitchen_stations 
                WHERE type NOT IN ({$validTypesString})
                GROUP BY type
            ");

            foreach ($invalidTypes as $invalid) {
                Log::warning("⚠️ Found {$invalid->count} stations with invalid type: '{$invalid->type}'");
                
                // Set default type for invalid ones
                $updated = DB::table('kitchen_stations')
                    ->where('type', $invalid->type)
                    ->update(['type' => 'cooking']);
                    
                Log::info("✅ Updated {$updated} stations with invalid type '{$invalid->type}' to 'cooking'");
            }

            // Final verification
            $finalCheck = DB::select("
                SELECT DISTINCT type, COUNT(*) as count
                FROM kitchen_stations 
                WHERE type NOT IN ({$validTypesString})
                GROUP BY type
            ");

            if (empty($finalCheck)) {
                Log::info("✅ All kitchen station types are now valid");
            } else {
                Log::error("❌ Still have invalid types after cleanup:");
                foreach ($finalCheck as $invalid) {
                    Log::error("  - {$invalid->type}: {$invalid->count} stations");
                }
            }

        } catch (\Exception $e) {
            Log::warning('Could not update invalid types: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        try {
            // Drop any type constraints
            $constraints = DB::select("
                SELECT conname
                FROM pg_constraint 
                WHERE conrelid = (SELECT oid FROM pg_class WHERE relname = 'kitchen_stations')
                AND contype = 'c'
                AND conname LIKE '%type%'
            ");

            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE kitchen_stations DROP CONSTRAINT IF EXISTS {$constraint->conname}");
                Log::info("Dropped constraint during rollback: {$constraint->conname}");
            }
            
            // Restore basic constraint with original types
            $originalTypes = ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'];
            $typeValues = "'" . implode("','", $originalTypes) . "'";
            
            DB::statement("
                ALTER TABLE kitchen_stations 
                ADD CONSTRAINT kitchen_stations_type_check 
                CHECK (type IN ({$typeValues}))
            ");

            Log::info("Restored original type constraint during rollback");

        } catch (\Exception $e) {
            Log::warning('Could not revert type constraint: ' . $e->getMessage());
        }
    }
};