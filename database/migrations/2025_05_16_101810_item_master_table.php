<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('item_master', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unicode_name')->nullable();
            $table->foreignId('item_category_id')->constrained('item_categories');
            $table->string('item_code')->unique();
            $table->string('unit_of_measurement');
            $table->integer('reorder_level')->default(0);
            $table->boolean('is_perishable')->default(false);
            $table->integer('shelf_life_in_days')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->decimal('buying_price', 10, 2)->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);
            $table->boolean('is_menu_item')->default(false);
            $table->text('additional_notes')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_master');
    }
};