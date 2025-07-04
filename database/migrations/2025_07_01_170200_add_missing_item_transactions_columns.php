<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS
     */
    public function up(): void
    {
        try {
            // First, check what columns currently exist
            $existingColumns = Schema::getColumnListing('item_transactions');
            Log::info('Existing item_transactions columns:', $existingColumns);

            // Add missing columns first (before creating indexes)
            Schema::table('item_transactions', function (Blueprint $table) use ($existingColumns) {
                // Add audit trail columns for Laravel + PostgreSQL + Tailwind CSS
                if (!in_array('created_by_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable();
                    Log::info('Added created_by_user_id column');
                }
                
                if (!in_array('updated_by_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('updated_by_user_id')->nullable();
                    Log::info('Added updated_by_user_id column');
                }
                
                if (!in_array('verified_by', $existingColumns)) {
                    $table->unsignedBigInteger('verified_by')->nullable();
                    Log::info('Added verified_by column');
                }
                
                if (!in_array('approved_by', $existingColumns)) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                    Log::info('Added approved_by column');
                }
                
                // Add transaction workflow columns
                if (!in_array('status', $existingColumns)) {
                    $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])
                          ->default('pending');
                    Log::info('Added status column');
                }
                
                if (!in_array('transaction_date', $existingColumns)) {
                    $table->datetime('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'));
                    Log::info('Added transaction_date column');
                }
                
                // Add reference tracking
                if (!in_array('reference_type', $existingColumns)) {
                    $table->string('reference_type')->nullable();
                    Log::info('Added reference_type column');
                }
                
                if (!in_array('reference_id', $existingColumns)) {
                    $table->unsignedBigInteger('reference_id')->nullable();
                    Log::info('Added reference_id column');
                }
                
                if (!in_array('reference_number', $existingColumns)) {
                    $table->string('reference_number')->nullable();
                    Log::info('Added reference_number column');
                }
                
                // Add batch and expiry tracking
                if (!in_array('batch_number', $existingColumns)) {
                    $table->string('batch_number')->nullable();
                    Log::info('Added batch_number column');
                }
                
                if (!in_array('expiry_date', $existingColumns)) {
                    $table->date('expiry_date')->nullable();
                    Log::info('Added expiry_date column');
                }
                
                // Add financial tracking
                if (!in_array('total_value', $existingColumns)) {
                    $table->decimal('total_value', 15, 2)->default(0);
                    Log::info('Added total_value column');
                }
                
                if (!in_array('tax_amount', $existingColumns)) {
                    $table->decimal('tax_amount', 10, 2)->default(0);
                    Log::info('Added tax_amount column');
                }
                
                // Add inventory impact tracking
                if (!in_array('stock_before', $existingColumns)) {
                    $table->decimal('stock_before', 10, 2)->nullable();
                    Log::info('Added stock_before column');
                }
                
                if (!in_array('stock_after', $existingColumns)) {
                    $table->decimal('stock_after', 10, 2)->nullable();
                    Log::info('Added stock_after column');
                }
                
                // Add production tracking
                if (!in_array('production_session_id', $existingColumns)) {
                    $table->unsignedBigInteger('production_session_id')->nullable();
                    Log::info('Added production_session_id column');
                }
                
                if (!in_array('production_order_id', $existingColumns)) {
                    $table->unsignedBigInteger('production_order_id')->nullable();
                    Log::info('Added production_order_id column');
                }
                
                // Add waste tracking
                if (!in_array('waste_quantity', $existingColumns)) {
                    $table->decimal('waste_quantity', 10, 2)->default(0);
                    Log::info('Added waste_quantity column');
                }
                
                if (!in_array('waste_reason', $existingColumns)) {
                    $table->string('waste_reason')->nullable();
                    Log::info('Added waste_reason column');
                }
                
                // Add PostgreSQL JSON for Tailwind CSS UI metadata
                if (!in_array('metadata', $existingColumns)) {
                    $table->json('metadata')->nullable();
                    Log::info('Added metadata column');
                }
                
                // Add soft deletes if not exists
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                    Log::info('Added soft deletes');
                }
            });

            // Now add foreign key constraints (check for existing ones first)
            $this->addForeignKeyConstraintsSafely();

            // Finally, add indexes (after columns and foreign keys exist)
            $this->addPerformanceIndexes();

            Log::info('Successfully added missing columns to item_transactions table');

        } catch (\Exception $e) {
            Log::error('Error adding columns to item_transactions table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add foreign key constraints safely - check for existing constraints first
     */
    private function addForeignKeyConstraintsSafely(): void
    {
        // Get all existing foreign key constraints for item_transactions table
        $existingConstraints = $this->getExistingForeignKeys('item_transactions');
        Log::info('Existing foreign key constraints:', $existingConstraints);

        Schema::table('item_transactions', function (Blueprint $table) use ($existingConstraints) {
            // Add foreign keys only if they don't exist and referenced tables exist
            
            // Users table foreign keys
            if (Schema::hasTable('users')) {
                if (!in_array('item_transactions_created_by_user_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'created_by_user_id')) {
                    try {
                        $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for created_by_user_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for created_by_user_id: ' . $e->getMessage());
                    }
                }
                
                if (!in_array('item_transactions_updated_by_user_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'updated_by_user_id')) {
                    try {
                        $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for updated_by_user_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for updated_by_user_id: ' . $e->getMessage());
                    }
                }
                
                if (!in_array('item_transactions_verified_by_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'verified_by')) {
                    try {
                        $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for verified_by');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for verified_by: ' . $e->getMessage());
                    }
                }
                
                if (!in_array('item_transactions_approved_by_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'approved_by')) {
                    try {
                        $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for approved_by');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for approved_by: ' . $e->getMessage());
                    }
                }
            }
            
            // Item master foreign key - only add if it doesn't exist
            if (!in_array('item_transactions_inventory_item_id_foreign', $existingConstraints) && 
                Schema::hasColumn('item_transactions', 'inventory_item_id')) {
                try {
                    if (Schema::hasTable('item_master')) {
                        $table->foreign('inventory_item_id')->references('id')->on('item_master')->onDelete('cascade');
                        Log::info('Added foreign key for inventory_item_id -> item_master');
                    } elseif (Schema::hasTable('item_master')) {
                        $table->foreign('inventory_item_id')->references('id')->on('item_master')->onDelete('cascade');
                        Log::info('Added foreign key for inventory_item_id -> item_master');
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for inventory_item_id: ' . $e->getMessage());
                }
            } else {
                Log::info('Foreign key for inventory_item_id already exists, skipping');
            }
            
            // Production foreign keys
            if (Schema::hasTable('production_sessions') && 
                !in_array('item_transactions_production_session_id_foreign', $existingConstraints) && 
                Schema::hasColumn('item_transactions', 'production_session_id')) {
                try {
                    $table->foreign('production_session_id')->references('id')->on('production_sessions')->onDelete('set null');
                    Log::info('Added foreign key for production_session_id');
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for production_session_id: ' . $e->getMessage());
                }
            }
            
            if (Schema::hasTable('production_orders') && 
                !in_array('item_transactions_production_order_id_foreign', $existingConstraints) && 
                Schema::hasColumn('item_transactions', 'production_order_id')) {
                try {
                    $table->foreign('production_order_id')->references('id')->on('production_orders')->onDelete('set null');
                    Log::info('Added foreign key for production_order_id');
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for production_order_id: ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * Get existing foreign key constraints for a table in PostgreSQL
     */
    private function getExistingForeignKeys(string $tableName): array
    {
        try {
            $constraints = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints 
                WHERE table_name = ? AND constraint_type = 'FOREIGN KEY'
            ", [$tableName]);
            
            return array_column($constraints, 'constraint_name');
        } catch (\Exception $e) {
            Log::warning('Could not fetch existing foreign keys: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add PostgreSQL performance indexes for Tailwind CSS UI queries
     */
    private function addPerformanceIndexes(): void
    {
        $indexesToAdd = [
            ['columns' => ['created_by_user_id'], 'index' => 'item_transactions_created_by_idx'],
            ['columns' => ['transaction_type', 'status'], 'index' => 'item_transactions_type_status_idx'],
            ['columns' => ['organization_id', 'branch_id'], 'index' => 'item_transactions_org_branch_idx'],
            ['columns' => ['reference_type', 'reference_id'], 'index' => 'item_transactions_reference_idx'],
            ['columns' => ['transaction_date'], 'index' => 'item_transactions_date_idx'],
            ['columns' => ['inventory_item_id', 'transaction_type'], 'index' => 'item_transactions_item_type_idx'],
            ['columns' => ['batch_number'], 'index' => 'item_transactions_batch_idx'],
            ['columns' => ['expiry_date'], 'index' => 'item_transactions_expiry_idx'],
            ['columns' => ['status'], 'index' => 'item_transactions_status_idx'],
            ['columns' => ['verified_by'], 'index' => 'item_transactions_verified_by_idx'],
            ['columns' => ['approved_by'], 'index' => 'item_transactions_approved_by_idx']
        ];

        foreach ($indexesToAdd as $indexData) {
            $this->addIndexIfNotExists('item_transactions', $indexData['columns'], $indexData['index']);
        }
    }

    /**
     * Add index if it doesn't exist in PostgreSQL
     */
    private function addIndexIfNotExists(string $tableName, array $columns, string $indexName): void
    {
        try {
            // Check if all columns exist before creating index
            $existingColumns = Schema::getColumnListing($tableName);
            $missingColumns = array_diff($columns, $existingColumns);
            
            if (!empty($missingColumns)) {
                Log::warning("Skipping index {$indexName} - missing columns: " . implode(', ', $missingColumns));
                return;
            }

            // Check if index exists in PostgreSQL
            $indexExists = DB::selectOne("
                SELECT indexname 
                FROM pg_indexes 
                WHERE tablename = ? AND indexname = ?
            ", [$tableName, $indexName]);
            
            if (!$indexExists) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
                Log::info("Created index: {$indexName}");
            } else {
                Log::info("Index already exists: {$indexName}");
            }
        } catch (\Exception $e) {
            Log::warning("Could not create index {$indexName}: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Drop indexes first
            $indexesToDrop = [
                'item_transactions_created_by_idx',
                'item_transactions_type_status_idx',
                'item_transactions_org_branch_idx',
                'item_transactions_reference_idx',
                'item_transactions_date_idx',
                'item_transactions_item_type_idx',
                'item_transactions_batch_idx',
                'item_transactions_expiry_idx',
                'item_transactions_status_idx',
                'item_transactions_verified_by_idx',
                'item_transactions_approved_by_idx'
            ];
            
            foreach ($indexesToDrop as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }
            
            // Drop foreign keys (only the ones we might have added)
            $foreignKeysToTrop = [
                'created_by_user_id',
                'updated_by_user_id',
                'verified_by',
                'approved_by',
                'production_session_id',
                'production_order_id'
            ];
            
            foreach ($foreignKeysToTrop as $fk) {
                try {
                    $table->dropForeign([$fk]);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            }
            
            // Drop columns
            $columnsToDrop = [
                'created_by_user_id',
                'updated_by_user_id',
                'verified_by',
                'approved_by',
                'status',
                'transaction_date',
                'reference_type',
                'reference_id',
                'reference_number',
                'batch_number',
                'expiry_date',
                'total_value',
                'tax_amount',
                'stock_before',
                'stock_after',
                'production_session_id',
                'production_order_id',
                'waste_quantity',
                'waste_reason',
                'metadata',
                'deleted_at'
            ];
            
            $existingColumns = Schema::getColumnListing('item_transactions');
            foreach ($columnsToDrop as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};