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
        // First ensure all data has been migrated and new foreign keys are in place
        Schema::dropIfExists('inventory_items');
    }

    public function down(): void
    {
        // 
    }
};
