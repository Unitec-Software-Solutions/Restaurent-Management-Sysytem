<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to fix item_transactions table structure for Laravel + PostgreSQL + Tailwind CSS
     */
    public function up(): void
    {
        try {
            Log::info('Starting item_transactions table structure fix...');
            
            // Get existing columns and constraints
            $existingColumns = Schema::getColumnListing('item_transactions');
            $existingConstraints = $this->getExistingForeignKeys('item_transactions');
            
            Log::info('Existing columns:', $existingColumns);
            Log::info('Existing constraints:', $existingConstraints);
            
            // Add missing columns first
            Schema::table('item_transactions', function (Blueprint $table) use ($existingColumns) {
                // Add transaction workflow columns
                if (!in_array('transaction_status', $existingColumns)) {
                    $table->enum('transaction_status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])
                          ->default('pending')->after('transaction_type');
                    Log::info('Added transaction_status column');
                }
                
                if (!in_array('transaction_date', $existingColumns)) {
                    $table->datetime('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'))->after('transaction_status');
                    Log::info('Added transaction_date column');
                }
                
                // Add user tracking columns
                if (!in_array('created_by_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('created_by');
                    Log::info('Added created_by_user_id column');
                }
                
                if (!in_array('updated_by_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('updated_by_user_id')->nullable()->after('created_by_user_id');
                    Log::info('Added updated_by_user_id column');
                }
                
                if (!in_array('approved_by_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('updated_by_user_id');
                    Log::info('Added approved_by_user_id column');
                }
                
                if (!in_array('approved_at', $existingColumns)) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
                    Log::info('Added approved_at column');
                }
                
                // Add quantity tracking columns
                if (!in_array('received_quantity', $existingColumns)) {
                    $table->decimal('received_quantity', 10, 2)->nullable()->after('quantity');
                    Log::info('Added received_quantity column');
                }
                
                if (!in_array('damaged_quantity', $existingColumns)) {
                    $table->decimal('damaged_quantity', 10, 2)->default(0)->after('received_quantity');
                    Log::info('Added damaged_quantity column');
                }
                
                if (!in_array('waste_quantity', $existingColumns)) {
                    $table->decimal('waste_quantity', 10, 2)->default(0)->after('damaged_quantity');
                    Log::info('Added waste_quantity column');
                }
                
                if (!in_array('waste_reason', $existingColumns)) {
                    $table->string('waste_reason')->nullable()->after('waste_quantity');
                    Log::info('Added waste_reason column');
                }
                
                // Add inventory impact tracking
                if (!in_array('stock_before', $existingColumns)) {
                    $table->decimal('stock_before', 10, 2)->nullable()->after('waste_reason');
                    Log::info('Added stock_before column');
                }
                
                if (!in_array('stock_after', $existingColumns)) {
                    $table->decimal('stock_after', 10, 2)->nullable()->after('stock_before');
                    Log::info('Added stock_after column');
                }
                
                // Add pricing columns
                if (!in_array('cost_price', $existingColumns)) {
                    $table->decimal('cost_price', 10, 4)->default(0)->after('stock_after');
                    Log::info('Added cost_price column');
                }
                
                if (!in_array('tax_amount', $existingColumns)) {
                    $table->decimal('tax_amount', 10, 2)->default(0)->after('total_amount');
                    Log::info('Added tax_amount column');
                }
                
                // Add location tracking
                if (!in_array('from_location', $existingColumns)) {
                    $table->string('from_location')->nullable()->after('batch_number');
                    Log::info('Added from_location column');
                }
                
                if (!in_array('to_location', $existingColumns)) {
                    $table->string('to_location')->nullable()->after('from_location');
                    Log::info('Added to_location column');
                }
                
                // Add quality tracking
                if (!in_array('quality_status', $existingColumns)) {
                    $table->enum('quality_status', ['pending', 'passed', 'failed', 'rejected'])
                          ->default('pending')->after('to_location');
                    Log::info('Added quality_status column');
                }
                
                if (!in_array('quality_notes', $existingColumns)) {
                    $table->text('quality_notes')->nullable()->after('quality_status');
                    Log::info('Added quality_notes column');
                }
                
                // Add production tracking columns
                if (!in_array('production_session_id', $existingColumns)) {
                    $table->unsignedBigInteger('production_session_id')->nullable()->after('gtn_id');
                    Log::info('Added production_session_id column');
                }
                
                if (!in_array('production_order_id', $existingColumns)) {
                    $table->unsignedBigInteger('production_order_id')->nullable()->after('production_session_id');
                    Log::info('Added production_order_id column');
                }
                
                // Add branch transfer tracking
                if (!in_array('incoming_branch_id', $existingColumns)) {
                    $table->unsignedBigInteger('incoming_branch_id')->nullable()->after('branch_id');
                    Log::info('Added incoming_branch_id column');
                }
                
                if (!in_array('receiver_user_id', $existingColumns)) {
                    $table->unsignedBigInteger('receiver_user_id')->nullable()->after('incoming_branch_id');
                    Log::info('Added receiver_user_id column');
                }
                
                // Add PostgreSQL JSON metadata for Tailwind CSS UI
                if (!in_array('transaction_metadata', $existingColumns)) {
                    $table->jsonb('transaction_metadata')->nullable()->after('notes');
                    Log::info('Added transaction_metadata column');
                }
                
                // Add active status tracking
                if (!in_array('is_active', $existingColumns)) {
                    $table->boolean('is_active')->default(true)->after('transaction_metadata');
                    Log::info('Added is_active column');
                }
            });
            
            // Now add foreign key constraints safely
            $this->addForeignKeyConstraintsSafely();
            
            // Add performance indexes for PostgreSQL + Tailwind CSS
            $this->addPerformanceIndexes();
            
            Log::info('Successfully fixed item_transactions table structure');
            
        } catch (\Exception $e) {
            Log::error('Error fixing item_transactions table structure: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Add foreign key constraints safely - check for existing constraints first
     */
    private function addForeignKeyConstraintsSafely(): void
    {
        $existingConstraints = $this->getExistingForeignKeys('item_transactions');
        
        Schema::table('item_transactions', function (Blueprint $table) use ($existingConstraints) {
            // Add user foreign keys only if they don't exist
            if (Schema::hasTable('users')) {
                if (!in_array('item_transactions_created_by_user_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'created_by_user_id')) {
                    try {
                        $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for created_by_user_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for created_by_user_id: ' . $e->getMessage());
                    }
                } else {
                    Log::info('Foreign key for created_by_user_id already exists, skipping');
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
                
                if (!in_array('item_transactions_approved_by_user_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'approved_by_user_id')) {
                    try {
                        $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for approved_by_user_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for approved_by_user_id: ' . $e->getMessage());
                    }
                }
                
                if (!in_array('item_transactions_receiver_user_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'receiver_user_id')) {
                    try {
                        $table->foreign('receiver_user_id')->references('id')->on('users')->onDelete('set null');
                        Log::info('Added foreign key for receiver_user_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for receiver_user_id: ' . $e->getMessage());
                    }
                }
            }
            
            // Add branch foreign keys
            if (Schema::hasTable('branches')) {
                if (!in_array('item_transactions_incoming_branch_id_foreign', $existingConstraints) && 
                    Schema::hasColumn('item_transactions', 'incoming_branch_id')) {
                    try {
                        $table->foreign('incoming_branch_id')->references('id')->on('branches')->onDelete('set null');
                        Log::info('Added foreign key for incoming_branch_id');
                    } catch (\Exception $e) {
                        Log::warning('Could not add foreign key for incoming_branch_id: ' . $e->getMessage());
                    }
                }
            }
            
            // Add production foreign keys
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
                SELECT tc.constraint_name
                FROM information_schema.table_constraints tc
                WHERE tc.table_name = ? 
                    AND tc.constraint_type = 'FOREIGN KEY'
                    AND tc.table_schema = 'public'
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
            ['columns' => ['transaction_type', 'transaction_status'], 'index' => 'idx_item_transactions_type_status'],
            ['columns' => ['organization_id', 'branch_id'], 'index' => 'idx_item_transactions_org_branch'],
            ['columns' => ['reference_type', 'reference_id'], 'index' => 'idx_item_transactions_reference'],
            ['columns' => ['transaction_date'], 'index' => 'idx_item_transactions_date'],
            ['columns' => ['inventory_item_id', 'transaction_type'], 'index' => 'idx_item_transactions_item_type'],
            ['columns' => ['batch_number'], 'index' => 'idx_item_transactions_batch'],
            ['columns' => ['expiry_date'], 'index' => 'idx_item_transactions_expiry'],
            ['columns' => ['transaction_status'], 'index' => 'idx_item_transactions_status'],
            ['columns' => ['quality_status'], 'index' => 'idx_item_transactions_quality'],
            ['columns' => ['created_by_user_id'], 'index' => 'idx_item_transactions_created_by'],
            ['columns' => ['approved_by_user_id'], 'index' => 'idx_item_transactions_approved_by'],
            ['columns' => ['is_active'], 'index' => 'idx_item_transactions_active'],
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
                'idx_item_transactions_type_status',
                'idx_item_transactions_org_branch',
                'idx_item_transactions_reference',
                'idx_item_transactions_date',
                'idx_item_transactions_item_type',
                'idx_item_transactions_batch',
                'idx_item_transactions_expiry',
                'idx_item_transactions_status',
                'idx_item_transactions_quality',
                'idx_item_transactions_created_by',
                'idx_item_transactions_approved_by',
                'idx_item_transactions_active'
            ];
            
            foreach ($indexesToDrop as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }
            
            // Drop foreign keys
            $foreignKeysToTrop = [
                'created_by_user_id',
                'updated_by_user_id',
                'approved_by_user_id',
                'receiver_user_id',
                'incoming_branch_id',
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
                'transaction_status',
                'transaction_date',
                'created_by_user_id',
                'updated_by_user_id',
                'approved_by_user_id',
                'approved_at',
                'received_quantity',
                'damaged_quantity',
                'waste_quantity',
                'waste_reason',
                'stock_before',
                'stock_after',
                'cost_price',
                'tax_amount',
                'from_location',
                'to_location',
                'quality_status',
                'quality_notes',
                'production_session_id',
                'production_order_id',
                'incoming_branch_id',
                'receiver_user_id',
                'transaction_metadata',
                'is_active'
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
