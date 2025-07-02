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
            // Get existing columns to avoid conflicts
            $existingColumns = Schema::getColumnListing('item_transactions');
            Log::info('Adding essential columns to item_transactions table');

            // Add essential columns for Laravel + PostgreSQL + Tailwind CSS
            Schema::table('item_transactions', function (Blueprint $table) use ($existingColumns) {
                
                // Add cost_price column if it doesn't exist
                if (!in_array('cost_price', $existingColumns)) {
                    $table->decimal('cost_price', 10, 4)->default(0)->after('unit_price');
                    Log::info('Added cost_price column');
                }
                
                // Add total_amount column if it doesn't exist
                if (!in_array('total_amount', $existingColumns)) {
                    $table->decimal('total_amount', 15, 2)->default(0)->after('unit_price');
                    Log::info('Added total_amount column');
                }
                
                // Add other essential columns
                if (!in_array('transaction_status', $existingColumns)) {
                    $table->enum('transaction_status', ['pending', 'completed', 'cancelled', 'failed'])
                          ->default('pending')->after('transaction_type');
                    Log::info('Added transaction_status column');
                }
                
                if (!in_array('approved_at', $existingColumns)) {
                    $table->timestamp('approved_at')->nullable()->after('transaction_status');
                    Log::info('Added approved_at column');
                }
                
                if (!in_array('approved_by_id', $existingColumns)) {
                    $table->unsignedBigInteger('approved_by_id')->nullable()->after('approved_at');
                    Log::info('Added approved_by_id column');
                }
                
                // Add batch tracking for inventory management
                if (!in_array('batch_code', $existingColumns)) {
                    $table->string('batch_code')->nullable()->after('approved_by_id');
                    Log::info('Added batch_code column');
                }
                
                if (!in_array('expiry_date', $existingColumns)) {
                    $table->date('expiry_date')->nullable()->after('batch_code');
                    Log::info('Added expiry_date column');
                }
                
                // Add quality control fields
                if (!in_array('quality_status', $existingColumns)) {
                    $table->enum('quality_status', ['pending', 'passed', 'failed', 'rejected'])
                          ->default('pending')->after('expiry_date');
                    Log::info('Added quality_status column');
                }
                
                if (!in_array('quality_notes', $existingColumns)) {
                    $table->text('quality_notes')->nullable()->after('quality_status');
                    Log::info('Added quality_notes column');
                }
                
                // Add warehouse location tracking
                if (!in_array('from_location', $existingColumns)) {
                    $table->string('from_location')->nullable()->after('quality_notes');
                    Log::info('Added from_location column');
                }
                
                if (!in_array('to_location', $existingColumns)) {
                    $table->string('to_location')->nullable()->after('from_location');
                    Log::info('Added to_location column');
                }
                
                // Add PostgreSQL JSON metadata for Tailwind CSS UI components
                if (!in_array('transaction_metadata', $existingColumns)) {
                    $table->jsonb('transaction_metadata')->nullable()->after('to_location');
                    Log::info('Added transaction_metadata column');
                }
                
                // Add audit tracking
                if (!in_array('created_by_id', $existingColumns)) {
                    $table->unsignedBigInteger('created_by_id')->nullable()->after('transaction_metadata');
                    Log::info('Added created_by_id column');
                }
                
                if (!in_array('updated_by_id', $existingColumns)) {
                    $table->unsignedBigInteger('updated_by_id')->nullable()->after('created_by_id');
                    Log::info('Added updated_by_id column');
                }
            });

            // Now update total_amount for existing records using the correct column name
            $this->updateTotalAmountSafely();

            // Add foreign key constraints for new columns
            $this->addForeignKeyConstraints();

            // Add PostgreSQL indexes for performance
            $this->addPerformanceIndexes();

            Log::info('Successfully added essential columns to item_transactions table');

        } catch (\Exception $e) {
            Log::error('Error adding essential columns to item_transactions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update total_amount using existing columns safely
     */
    private function updateTotalAmountSafely(): void
    {
        try {
            // Check which price column exists
            $hasCostPrice = Schema::hasColumn('item_transactions', 'cost_price');
            $hasUnitPrice = Schema::hasColumn('item_transactions', 'unit_price');
            
            if ($hasCostPrice) {
                // Use cost_price if it exists
                DB::statement("
                    UPDATE item_transactions 
                    SET total_amount = COALESCE(quantity, 0) * COALESCE(cost_price, 0) 
                    WHERE total_amount = 0 OR total_amount IS NULL
                ");
                Log::info('Updated total_amount using cost_price');
            } elseif ($hasUnitPrice) {
                // Use unit_price if cost_price doesn't exist
                DB::statement("
                    UPDATE item_transactions 
                    SET total_amount = COALESCE(quantity, 0) * COALESCE(unit_price, 0) 
                    WHERE total_amount = 0 OR total_amount IS NULL
                ");
                Log::info('Updated total_amount using unit_price');
            } else {
                Log::warning('Neither cost_price nor unit_price column found, skipping total_amount update');
            }
        } catch (\Exception $e) {
            Log::warning('Could not update total_amount: ' . $e->getMessage());
        }
    }

    /**
     * Add foreign key constraints for new columns
     */
    private function addForeignKeyConstraints(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Add foreign keys if tables exist
            if (Schema::hasTable('users') && Schema::hasColumn('item_transactions', 'approved_by_id')) {
                try {
                    $table->foreign('approved_by_id')->references('id')->on('users')->onDelete('set null');
                    Log::info('Added foreign key for approved_by_id');
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for approved_by_id: ' . $e->getMessage());
                }
            }
            
            if (Schema::hasTable('users') && Schema::hasColumn('item_transactions', 'created_by_id')) {
                try {
                    $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
                    Log::info('Added foreign key for created_by_id');
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for created_by_id: ' . $e->getMessage());
                }
            }
            
            if (Schema::hasTable('users') && Schema::hasColumn('item_transactions', 'updated_by_id')) {
                try {
                    $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('set null');
                    Log::info('Added foreign key for updated_by_id');
                } catch (\Exception $e) {
                    Log::warning('Could not add foreign key for updated_by_id: ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * Add PostgreSQL performance indexes for Tailwind CSS UI queries
     */
    private function addPerformanceIndexes(): void
    {
        $indexesToAdd = [
            'transaction_status' => 'idx_item_transactions_status',
            'quality_status' => 'idx_item_transactions_quality',
            'batch_code' => 'idx_item_transactions_batch',
            'expiry_date' => 'idx_item_transactions_expiry',
            'approved_at' => 'idx_item_transactions_approved_at',
            'from_location' => 'idx_item_transactions_from_location',
            'to_location' => 'idx_item_transactions_to_location',
            'created_by_id' => 'idx_item_transactions_created_by',
            'updated_by_id' => 'idx_item_transactions_updated_by'
        ];

        foreach ($indexesToAdd as $column => $indexName) {
            if (Schema::hasColumn('item_transactions', $column)) {
                try {
                    // Check if index already exists
                    $indexExists = DB::selectOne("
                        SELECT indexname 
                        FROM pg_indexes 
                        WHERE tablename = 'item_transactions' AND indexname = ?
                    ", [$indexName]);
                    
                    if (!$indexExists) {
                        Schema::table('item_transactions', function (Blueprint $table) use ($column, $indexName) {
                            $table->index($column, $indexName);
                        });
                        Log::info("Created index: {$indexName}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not create index {$indexName}: " . $e->getMessage());
                }
            }
        }

        // Add composite indexes for common queries
        try {
            $compositeIndexes = [
                [
                    'columns' => ['transaction_type', 'transaction_status'],
                    'name' => 'idx_item_transactions_type_status'
                ],
                [
                    'columns' => ['organization_id', 'branch_id', 'transaction_status'],
                    'name' => 'idx_item_transactions_org_branch_status'
                ],
                [
                    'columns' => ['inventory_item_id', 'transaction_type'],
                    'name' => 'idx_item_transactions_item_type'
                ]
            ];

            foreach ($compositeIndexes as $compositeIndex) {
                $columns = $compositeIndex['columns'];
                $indexName = $compositeIndex['name'];
                $allColumnsExist = collect($columns)->every(fn($col) => Schema::hasColumn('item_transactions', $col));
                
                if ($allColumnsExist) {
                    $indexExists = DB::selectOne("
                        SELECT indexname 
                        FROM pg_indexes 
                        WHERE tablename = 'item_transactions' AND indexname = ?
                    ", [$indexName]);
                    
                    if (!$indexExists) {
                        Schema::table('item_transactions', function (Blueprint $table) use ($columns, $indexName) {
                            $table->index($columns, $indexName);
                        });
                        Log::info("Created composite index: {$indexName}");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not create composite indexes: ' . $e->getMessage());
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
                'idx_item_transactions_status',
                'idx_item_transactions_quality',
                'idx_item_transactions_batch',
                'idx_item_transactions_expiry',
                'idx_item_transactions_approved_at',
                'idx_item_transactions_from_location',
                'idx_item_transactions_to_location',
                'idx_item_transactions_created_by',
                'idx_item_transactions_updated_by',
                'idx_item_transactions_type_status',
                'idx_item_transactions_org_branch_status',
                'idx_item_transactions_item_type'
            ];
            
            foreach ($indexesToDrop as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }
            
            // Drop foreign keys
            $foreignKeysToTrop = [
                'approved_by_id',
                'created_by_id',
                'updated_by_id'
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
                'cost_price',
                'total_amount',
                'transaction_status',
                'approved_at',
                'approved_by_id',
                'batch_code',
                'expiry_date',
                'quality_status',
                'quality_notes',
                'from_location',
                'to_location',
                'transaction_metadata',
                'created_by_id',
                'updated_by_id'
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
