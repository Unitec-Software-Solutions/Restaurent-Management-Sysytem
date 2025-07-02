<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Add only the essential columns that are causing the error
            if (!Schema::hasColumn('item_transactions', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0);
            }
            
            if (!Schema::hasColumn('item_transactions', 'balance_after_transaction')) {
                $table->decimal('balance_after_transaction', 12, 2)->default(0);
            }
            
            if (!Schema::hasColumn('item_transactions', 'unit_of_measurement')) {
                $table->string('unit_of_measurement')->nullable();
            }
            
            if (!Schema::hasColumn('item_transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable();
            }
            
            if (!Schema::hasColumn('item_transactions', 'transaction_time')) {
                $table->time('transaction_time')->nullable();
            }
            
            if (!Schema::hasColumn('item_transactions', 'reference_number')) {
                $table->string('reference_number')->nullable();
            }
            
            if (!Schema::hasColumn('item_transactions', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('item_transactions', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'total_amount',
                'balance_after_transaction', 
                'unit_of_measurement',
                'transaction_date',
                'transaction_time',
                'reference_number',
                'supplier_id',
                'metadata'
            ]);
        });
    }
};