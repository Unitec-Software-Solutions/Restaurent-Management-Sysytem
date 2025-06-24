<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('orders');
            
            // Add missing columns that factories expect
            if (!in_array('reservation_id', $existingColumns)) {
                $table->foreignId('reservation_id')->nullable()->after('id')
                      ->constrained('reservations')->onDelete('set null');
            }
            
            if (!in_array('subtotal', $existingColumns)) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            }
            
            if (!in_array('tax', $existingColumns)) {
                $table->decimal('tax', 8, 2)->default(0)->after('subtotal');
            }
            
            if (!in_array('service_charge', $existingColumns)) {
                $table->decimal('service_charge', 8, 2)->default(0)->after('tax');
            }
            
            if (!in_array('discount', $existingColumns)) {
                $table->decimal('discount', 8, 2)->default(0)->after('service_charge');
            }
            
            if (!in_array('total', $existingColumns)) {
                $table->decimal('total', 10, 2)->default(0)->after('discount');
            }
            
            if (!in_array('order_date', $existingColumns)) {
                $table->timestamp('order_date')->default(now())->after('notes');
            }
            
            // Add performance indexes
            $table->index(['reservation_id'], 'orders_reservation_idx');
            $table->index(['status', 'order_date'], 'orders_status_date_idx');
            $table->index(['branch_id', 'status'], 'orders_branch_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropIndex('orders_reservation_idx');
                $table->dropIndex('orders_status_date_idx');
                $table->dropIndex('orders_branch_status_idx');
            } catch (\Exception $e) {
                // Indexes might not exist
            }
            
            $columnsToRemove = [
                'reservation_id', 'subtotal', 'tax', 'service_charge', 
                'discount', 'total', 'order_date'
            ];
            
            $existingColumns = Schema::getColumnListing('orders');
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
