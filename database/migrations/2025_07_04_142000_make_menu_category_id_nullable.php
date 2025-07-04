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
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['menu_category_id']);
            
            // Modify the column to be nullable
            $table->foreignId('menu_category_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable
            $table->foreign('menu_category_id')
                  ->references('id')
                  ->on('menu_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['menu_category_id']);
            
            // Make the column not nullable again
            $table->foreignId('menu_category_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('menu_category_id')
                  ->references('id')
                  ->on('menu_categories')
                  ->onDelete('cascade');
        });
    }
};
