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
            
            // Add tax column if it doesn't exist
            if (!in_array('tax', $existingColumns)) {
                $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            }
            
            // Add discount column if it doesn't exist
            if (!in_array('discount', $existingColumns)) {
                $table->decimal('discount', 10, 2)->default(0)->after('tax');
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
            
            if (in_array('discount', $existingColumns)) {
                $table->dropColumn('discount');
            }
            
            if (in_array('tax', $existingColumns)) {
                $table->dropColumn('tax');
            }
        });
    }
};
