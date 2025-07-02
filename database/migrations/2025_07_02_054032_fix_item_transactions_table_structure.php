<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, check what columns currently exist
        $existingColumns = Schema::getColumnListing('item_transactions');
        
        Schema::table('item_transactions', function (Blueprint $table) use ($existingColumns) {
            // Add missing essential columns for PostgreSQL
            if (!in_array('created_by_user_id', $existingColumns)) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('source_type');
            }
            
            if (!in_array('updated_by_user_id', $existingColumns)) {
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->after('created_by_user_id');
            }
            
            if (!in_array('verified_by', $existingColumns)) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('updated_by_user_id');
            }
            
            if (!in_array('approved_by', $existingColumns)) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('verified_by');
            }
            
            // Add transaction reference fields
            if (!in_array('reference_type', $existingColumns)) {
                $table->string('reference_type')->nullable()->after('source_type');
            }
            
            if (!in_array('reference_id', $existingColumns)) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
            
            if (!in_array('reference_number', $existingColumns)) {
                $table->string('reference_number')->nullable()->after('reference_id');
            }
            
            // Add batch and expiry tracking
            if (!in_array('batch_number', $existingColumns)) {
                $table->string('batch_number')->nullable()->after('reference_number');
            }
            
            if (!in_array('expiry_date', $existingColumns)) {
                $table->date('expiry_date')->nullable()->after('batch_number');
            }
            
            // Add transaction status and workflow
            if (!in_array('status', $existingColumns)) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])
                      ->default('pending')->after('transaction_type');
            }
            
            if (!in_array('transaction_date', $existingColumns)) {
                $table->datetime('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'))->after('status');
            }
            
            // Add financial tracking
            if (!in_array('total_value', $existingColumns)) {
                $table->decimal('total_value', 15, 2)->default(0)->after('unit_price');
            }
            
            if (!in_array('tax_amount', $existingColumns)) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('total_value');
            }
            
            // Add inventory impact tracking
            if (!in_array('stock_before', $existingColumns)) {
                $table->decimal('stock_before', 10, 2)->nullable()->after('damaged_quantity');
            }
            
            if (!in_array('stock_after', $existingColumns)) {
                $table->decimal('stock_after', 10, 2)->nullable()->after('stock_before');
            }
            
            // Add production-related fields
            if (!in_array('production_session_id', $existingColumns)) {
                $table->unsignedBigInteger('production_session_id')->nullable()->after('gtn_id');
            }
            
            if (!in_array('production_order_id', $existingColumns)) {
                $table->unsignedBigInteger('production_order_id')->nullable()->after('production_session_id');
            }
            
            if (!in_array('waste_quantity', $existingColumns)) {
                $table->decimal('waste_quantity', 10, 2)->default(0)->after('damaged_quantity');
            }
            
            if (!in_array('waste_reason', $existingColumns)) {
                $table->string('waste_reason')->nullable()->after('waste_quantity');
            }
            
            // Add PostgreSQL JSON for Tailwind CSS UI metadata
            if (!in_array('metadata', $existingColumns)) {
                $table->json('metadata')->nullable()->after('notes');
            }
            
            // Add soft deletes if not exists
            if (!in_array('deleted_at', $existingColumns)) {
                $table->softDeletes();
            }
        });
        
        // Add foreign key constraints safely
        Schema::table('item_transactions', function (Blueprint $table) {
            // Check if foreign keys exist before adding
            $this->addForeignKeyIfNotExists($table, 'created_by_user_id', 'users', 'id');
            $this->addForeignKeyIfNotExists($table, 'updated_by_user_id', 'users', 'id');
            $this->addForeignKeyIfNotExists($table, 'verified_by', 'users', 'id');
            $this->addForeignKeyIfNotExists($table, 'approved_by', 'users', 'id');
            
            // Fix the main item reference - check which table name exists
            if (Schema::hasTable('item_master')) {
                $this->addForeignKeyIfNotExists($table, 'inventory_item_id', 'item_master', 'id');
            } elseif (Schema::hasTable('item_masters')) {
                $this->addForeignKeyIfNotExists($table, 'inventory_item_id', 'item_masters', 'id');
            }
            
            // Add production foreign keys if tables exist
            if (Schema::hasTable('production_sessions')) {
                $this->addForeignKeyIfNotExists($table, 'production_session_id', 'production_sessions', 'id');
            }
            
            if (Schema::hasTable('production_orders')) {
                $this->addForeignKeyIfNotExists($table, 'production_order_id', 'production_orders', 'id');
            }
        });
        
        // Add PostgreSQL indexes for performance with Tailwind CSS UI queries
        $this->addIndexIfNotExists('item_transactions', ['created_by_user_id'], 'item_transactions_created_by_idx');
        $this->addIndexIfNotExists('item_transactions', ['transaction_type', 'status'], 'item_transactions_type_status_idx');
        $this->addIndexIfNotExists('item_transactions', ['organization_id', 'branch_id'], 'item_transactions_org_branch_idx');
        $this->addIndexIfNotExists('item_transactions', ['reference_type', 'reference_id'], 'item_transactions_reference_idx');
        $this->addIndexIfNotExists('item_transactions', ['transaction_date'], 'item_transactions_date_idx');
        $this->addIndexIfNotExists('item_transactions', ['inventory_item_id', 'transaction_type'], 'item_transactions_item_type_idx');
        $this->addIndexIfNotExists('item_transactions', ['batch_number'], 'item_transactions_batch_idx');
        $this->addIndexIfNotExists('item_transactions', ['expiry_date'], 'item_transactions_expiry_idx');
    }

    /**
     * Add foreign key constraint if it doesn't exist
     */
    private function addForeignKeyIfNotExists(Blueprint $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        try {
            if (Schema::hasTable($referencedTable) && Schema::hasColumn('item_transactions', $column)) {
                $table->foreign($column)->references($referencedColumn)->on($referencedTable)->onDelete('set null');
            }
        } catch (\Exception $e) {
            // Foreign key might already exist, continue
        }
    }
    
    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $tableName, array $columns, string $indexName): void
    {
        try {
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
            }
        } catch (\Exception $e) {
            // Index creation failed, might already exist
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Drop indexes
            $indexesToDrop = [
                'item_transactions_created_by_idx',
                'item_transactions_type_status_idx',
                'item_transactions_org_branch_idx',
                'item_transactions_reference_idx',
                'item_transactions_date_idx',
                'item_transactions_item_type_idx',
                'item_transactions_batch_idx',
                'item_transactions_expiry_idx'
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
                'reference_type',
                'reference_id',
                'reference_number',
                'batch_number',
                'expiry_date',
                'status',
                'transaction_date',
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
