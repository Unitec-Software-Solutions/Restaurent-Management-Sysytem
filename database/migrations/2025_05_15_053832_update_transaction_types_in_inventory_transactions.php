<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Rename old column to avoid conflicts
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->renameColumn('transaction_type', 'transaction_type_old');
        });

        // Step 2: Add new transaction_type column with VARCHAR type
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->after('branch_id');
        });

        // Step 3: Copy data from old column to new column
        DB::statement('UPDATE inventory_transactions SET transaction_type = transaction_type_old');

        // Step 4: Drop old column
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type_old');
        });

        // Step 5: Make transaction_type non-nullable
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('transaction_type')->nullable(false)->change();
        });

        // Optional: Add index for faster querying by transaction_type
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ⚠️ WARNING: Reversing this migration may result in data loss
        // Only proceed if you're sure you want to revert

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex(['transaction_type']);
        });

        // Rename current column to prepare for ENUM
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->renameColumn('transaction_type', 'transaction_type_new');
        });

        // Re-add old ENUM column
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', [
                'purchase', 'transfer_in', 'transfer_out', 'usage',
                'adjustment', 'return', 'wastage'
            ])->after('branch_id');
        });

        // Copy back basic values (only limited types)
        DB::statement("UPDATE inventory_transactions SET transaction_type = transaction_type_new WHERE transaction_type_new IN ('purchase', 'transfer_in', 'transfer_out', 'usage', 'adjustment', 'return', 'wastage')");

        // Drop the new column
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type_new');
        });
    }
};