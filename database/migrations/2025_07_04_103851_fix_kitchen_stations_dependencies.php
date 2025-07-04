<?php
// filepath: database/migrations/2025_07_04_103851_fix_kitchen_stations_dependencies.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations - Fix PostgreSQL dependencies with proper transaction handling
     */
    public function up(): void
    {
        // Use database transactions properly for PostgreSQL
        DB::transaction(function () {
            try {
                // Step 1: Handle existing foreign key constraints safely
                $this->dropForeignKeyConstraintsSafely();

                // Step 2: Ensure kitchen_stations table exists with correct structure
                $this->ensureKitchenStationsTableSafely();

                // Step 3: Re-create foreign key constraints
                $this->recreateForeignKeyConstraintsSafely();

                Log::info('✅ Successfully fixed kitchen stations dependencies');

            } catch (\Exception $e) {
                Log::error('❌ Error fixing kitchen stations dependencies: ' . $e->getMessage());
                // Let the transaction rollback automatically
                throw $e;
            }
        });
    }

    /**
     * Drop foreign key constraints safely - PostgreSQL compatible with better error handling
     */
    private function dropForeignKeyConstraintsSafely(): void
    {
        try {
            // Check if kots table exists and has the column
            if ($this->tableAndColumnExists('kots', 'kitchen_station_id')) {
                $this->dropConstraintIfExists('kots', 'kitchen_station_id');
            }

            // Check if menu_items table exists and has the column
            if ($this->tableAndColumnExists('menu_items', 'kitchen_station_id')) {
                $this->dropConstraintIfExists('menu_items', 'kitchen_station_id');
            }

            Log::info('Successfully dropped foreign key constraints');

        } catch (\Exception $e) {
            Log::warning('Error dropping constraints: ' . $e->getMessage());
            // Don't throw, continue with migration
        }
    }

    /**
     * Check if table and column exist safely
     */
    private function tableAndColumnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            Log::debug("Error checking table/column {$table}.{$column}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Drop constraint if it exists
     */
    private function dropConstraintIfExists(string $table, string $column): void
    {
        try {
            // Get constraint name from PostgreSQL system tables
            $constraintName = DB::selectOne("
                SELECT tc.constraint_name
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
                WHERE tc.table_name = ?
                AND kcu.column_name = ?
                AND tc.constraint_type = 'FOREIGN KEY'
                LIMIT 1
            ", [$table, $column]);

            if ($constraintName) {
                // Drop using raw SQL to avoid Laravel's transaction issues
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraintName->constraint_name}");
                Log::info("Dropped constraint {$constraintName->constraint_name} from {$table}");
            }

        } catch (\Exception $e) {
            Log::debug("Could not drop constraint from {$table}.{$column}: " . $e->getMessage());
        }
    }

    /**
     * Ensure kitchen_stations table exists with proper structure - PostgreSQL safe
     */
    private function ensureKitchenStationsTableSafely(): void
    {
        try {
            if (!Schema::hasTable('kitchen_stations')) {
                $this->createKitchenStationsTable();
            } else {
                $this->updateKitchenStationsTable();
            }
        } catch (\Exception $e) {
            Log::error('Error ensuring kitchen_stations table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create kitchen_stations table from scratch
     */
    private function createKitchenStationsTable(): void
    {
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('station_code')->nullable();
            $table->text('description')->nullable();

            // Station Type - PostgreSQL enum
            $table->enum('station_type', [
                'hot_kitchen',
                'cold_kitchen',
                'grill',
                'prep',
                'dessert',
                'serving',
                'other'
            ])->nullable();

            // Foreign Keys
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');

            // Location and Physical Details
            $table->string('location')->nullable();
            $table->text('equipment')->nullable();

            // Capacity and Operations
            $table->integer('capacity')->nullable();
            $table->integer('priority_order')->default(1);
            $table->integer('max_concurrent_orders')->default(5);

            // Status and Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_assign_kots')->default(false);

            // Legacy Support for existing data
            $table->enum('type', ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'])->default('cooking')->nullable();
            $table->integer('order_priority')->default(1)->nullable();
            $table->string('code')->nullable();

            // Configuration - PostgreSQL JSON
            $table->json('printer_config')->nullable();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // PostgreSQL optimized indexes
            $table->index(['branch_id', 'is_active']);
            $table->index(['organization_id', 'is_active']);
            $table->index(['priority_order', 'order_priority']);
            $table->index(['station_type', 'is_active']);

            // Unique constraints
            $table->unique(['branch_id', 'name']);
        });

        Log::info('Created kitchen_stations table from scratch');
    }

    /**
     * Update existing kitchen_stations table
     */
    private function updateKitchenStationsTable(): void
    {
        $existingColumns = Schema::getColumnListing('kitchen_stations');

        Schema::table('kitchen_stations', function (Blueprint $table) use ($existingColumns) {
            // Add missing columns safely
            if (!in_array('station_code', $existingColumns)) {
                $table->string('station_code')->nullable()->after('name');
            }

            if (!in_array('description', $existingColumns)) {
                $table->text('description')->nullable()->after('name');
            }

            if (!in_array('organization_id', $existingColumns)) {
                $table->foreignId('organization_id')->nullable()->after('id');
            }

            if (!in_array('equipment', $existingColumns)) {
                $table->text('equipment')->nullable();
            }

            if (!in_array('auto_assign_kots', $existingColumns)) {
                $table->boolean('auto_assign_kots')->default(false);
            }

            if (!in_array('deleted_at', $existingColumns)) {
                $table->softDeletes();
            }
        });

        // Generate station codes for existing records
        $this->generateMissingStationCodes();

        Log::info('Updated existing kitchen_stations table');
    }

    /**
     * Generate station codes for records that don't have them
     */
    private function generateMissingStationCodes(): void
    {
        try {
            $stationsWithoutCodes = DB::table('kitchen_stations')
                ->whereNull('station_code')
                ->orWhere('station_code', '')
                ->get();

            foreach ($stationsWithoutCodes as $station) {
                $code = $this->generateUniqueStationCode($station);
                DB::table('kitchen_stations')
                    ->where('id', $station->id)
                    ->update(['station_code' => $code]);
            }

            // Make station_code unique if not already
            if (Schema::hasColumn('kitchen_stations', 'station_code')) {
                try {
                    DB::statement('ALTER TABLE kitchen_stations ADD CONSTRAINT kitchen_stations_station_code_unique UNIQUE (station_code)');
                } catch (\Exception $e) {
                    // Constraint might already exist
                    Log::debug('station_code unique constraint might already exist');
                }
            }

        } catch (\Exception $e) {
            Log::warning('Could not generate station codes: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique station code for a station
     */
    private function generateUniqueStationCode($station): string
    {
        $typePrefix = match($station->type ?? 'cooking') {
            'cooking' => 'COOK',
            'prep' => 'PREP',
            'beverage' => 'BEV',
            'dessert' => 'DESS',
            'grill' => 'GRILL',
            'fry' => 'FRY',
            'bar' => 'BAR',
            default => 'MAIN'
        };

        $branchCode = str_pad($station->branch_id ?? 1, 2, '0', STR_PAD_LEFT);
        $baseCode = $typePrefix . $branchCode;

        // Find next available number
        $counter = 1;
        do {
            $code = $baseCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $exists = DB::table('kitchen_stations')
                ->where('station_code', $code)
                ->where('id', '!=', $station->id)
                ->exists();
            $counter++;
        } while ($exists && $counter <= 99);

        return $code;
    }

    /**
     * Re-create foreign key constraints safely
     */
    private function recreateForeignKeyConstraintsSafely(): void
    {
        try {
            // Re-add foreign key to kots table
            if ($this->tableAndColumnExists('kots', 'kitchen_station_id')) {
                DB::statement('
                    ALTER TABLE kots
                    ADD CONSTRAINT kots_kitchen_station_id_foreign
                    FOREIGN KEY (kitchen_station_id)
                    REFERENCES kitchen_stations(id)
                    ON DELETE SET NULL
                ');
                Log::info('Re-created kots foreign key constraint');
            }

            // Re-add foreign key to menu_items table
            if ($this->tableAndColumnExists('menu_items', 'kitchen_station_id')) {
                DB::statement('
                    ALTER TABLE menu_items
                    ADD CONSTRAINT menu_items_kitchen_station_id_foreign
                    FOREIGN KEY (kitchen_station_id)
                    REFERENCES kitchen_stations(id)
                    ON DELETE SET NULL
                ');
                Log::info('Re-created menu_items foreign key constraint');
            }

            // Add organization foreign key if missing
            if ($this->tableAndColumnExists('kitchen_stations', 'organization_id')) {
                try {
                    DB::statement('
                        ALTER TABLE kitchen_stations
                        ADD CONSTRAINT kitchen_stations_organization_id_foreign
                        FOREIGN KEY (organization_id)
                        REFERENCES organizations(id)
                        ON DELETE CASCADE
                    ');
                    Log::info('Added organization foreign key constraint');
                } catch (\Exception $e) {
                    Log::debug('Organization foreign key might already exist');
                }
            }

        } catch (\Exception $e) {
            Log::warning('Error recreating foreign keys: ' . $e->getMessage());
            // Don't throw - foreign keys are not critical for basic functionality
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Log::info('Rolling back kitchen stations dependencies fix');

        try {
            // Drop foreign keys first
            DB::statement('ALTER TABLE kots DROP CONSTRAINT IF EXISTS kots_kitchen_station_id_foreign');
            DB::statement('ALTER TABLE menu_items DROP CONSTRAINT IF EXISTS menu_items_kitchen_station_id_foreign');

            // Note: We don't drop the kitchen_stations table as it might have been created elsewhere

        } catch (\Exception $e) {
            Log::warning('Error during rollback: ' . $e->getMessage());
        }
    }
};
