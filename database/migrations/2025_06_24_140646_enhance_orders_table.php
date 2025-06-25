<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('orders');
            
            // Add missing columns only if they don't exist
            if (!in_array('order_number', $existingColumns)) {
                $table->string('order_number')->unique()->after('id');
            }
            
            if (!in_array('subtotal', $existingColumns)) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total');
            }
            
            if (!in_array('tax', $existingColumns)) {
                $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            }
            
            if (!in_array('discount', $existingColumns)) {
                $table->decimal('discount', 10, 2)->default(0)->after('tax');
            }
            
            if (!in_array('service_charge', $existingColumns)) {
                $table->decimal('service_charge', 10, 2)->default(0)->after('discount');
            }
            
            if (!in_array('payment_method', $existingColumns)) {
                $table->enum('payment_method', ['cash', 'card', 'digital', 'split'])->nullable()->after('status');
            }
            
            if (!in_array('estimated_delivery_time', $existingColumns)) {
                $table->timestamp('estimated_delivery_time')->nullable()->after('payment_method');
            }
        });

        // Add indexes safely - check if they don't exist first
        $this->addIndexIfNotExists('orders', ['status', 'created_at'], 'orders_status_created_at_index');
        $this->addIndexIfNotExists('orders', ['branch_id', 'status'], 'orders_branch_id_status_index');
        
        // Handle order_number index separately as it might already exist
        if (in_array('order_number', Schema::getColumnListing('orders'))) {
            $this->addIndexIfNotExists('orders', ['order_number'], 'orders_order_number_index');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('orders');
            
            // Remove added columns
            $columnsToRemove = [
                'order_number',
                'subtotal', 
                'tax',
                'discount',
                'service_charge',
                'payment_method',
                'estimated_delivery_time'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Drop indexes
        $this->dropIndexIfExists('orders', 'orders_status_created_at_index');
        $this->dropIndexIfExists('orders', 'orders_branch_id_status_index');
        $this->dropIndexIfExists('orders', 'orders_order_number_index');
    }

    /**
     * Add index if it doesn't already exist
     */
    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        try {
            $indexes = collect(DB::select("
                SELECT indexname 
                FROM pg_indexes 
                WHERE tablename = ? AND indexname = ?
            ", [$table, $indexName]));

            if ($indexes->isEmpty()) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
                    $tableBlueprint->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Index creation failed - likely already exists with different name
            Log::info("Index creation skipped for {$indexName}: " . $e->getMessage());
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            $indexes = collect(DB::select("
                SELECT indexname 
                FROM pg_indexes 
                WHERE tablename = ? AND indexname = ?
            ", [$table, $indexName]));

            if ($indexes->isNotEmpty()) {
                DB::statement("DROP INDEX IF EXISTS {$indexName}");
            }
        } catch (\Exception $e) {
            // Index drop failed - likely doesn't exist
            Log::info("Index drop skipped for {$indexName}: " . $e->getMessage());
        }
    }
};
