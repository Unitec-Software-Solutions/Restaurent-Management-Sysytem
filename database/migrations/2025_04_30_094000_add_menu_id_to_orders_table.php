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
        // Ensure both orders and menus tables exist
        if (!Schema::hasTable('orders')) {
            throw new Exception('Orders table must exist before adding menu_id foreign key');
        }
        
        if (!Schema::hasTable('menus')) {
            throw new Exception('Menus table must exist before adding menu_id foreign key');
        }

        Schema::table('orders', function (Blueprint $table) {
            // Add menu_id column if it doesn't exist
            if (!Schema::hasColumn('orders', 'menu_id')) {
                $table->foreignId('menu_id')->nullable()->constrained()->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'menu_id')) {
                $table->dropForeign(['menu_id']);
                $table->dropColumn('menu_id');
            }
        });
    }
};
