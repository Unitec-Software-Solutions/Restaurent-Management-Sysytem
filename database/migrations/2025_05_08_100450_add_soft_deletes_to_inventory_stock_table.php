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
        Schema::table('inventory_stock', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_stock', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_stock', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_stock', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
