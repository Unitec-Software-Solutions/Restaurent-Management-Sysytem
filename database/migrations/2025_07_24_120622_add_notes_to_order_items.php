<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('order_items');
            
            // Add notes column if it doesn't exist
            if (!in_array('notes', $existingColumns)) {
                $table->text('notes')->nullable()->after('special_instructions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('order_items');
            
            if (in_array('notes', $existingColumns)) {
                $table->dropColumn('notes');
            }
        });
    }
};
