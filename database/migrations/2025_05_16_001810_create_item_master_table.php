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
            $table->string('name')->nullable();
            $table->string('unicode_name')->nullable();
            $table->foreignId('item_category_id')->constrained('item_categories')->nullable();
            $table->string('item_code')->default('Item-code-not-set')->nullable();
            $table->string('barcode')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->integer('reorder_level')->default(0)->nullable();
            $table->boolean('is_perishable')->default(false)->nullable();
            $table->integer('shelf_life_in_days')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullable();
            $table->foreignId('organization_id')->constrained('organizations')->nullable();
            $table->decimal('buying_price', 10, 2)->default(0.00)->nullable();
            $table->decimal('selling_price', 10, 2)->default(0.00)->nullable();
            $table->boolean('is_menu_item')->default(false)->nullable();
            $table->text('additional_notes')->nullable();
            $table->text('description')->nullable();
            $table->jsonb('attributes')->nullable();
            $table->boolean('is_active')->default(true)->nullable();

            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
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
