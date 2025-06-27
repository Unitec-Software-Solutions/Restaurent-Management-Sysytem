<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        if (!Schema::hasTable('menu_categories')) {
            Schema::create('menu_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->integer('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                // Add indexes
                $table->index(['is_active', 'display_order']);
                $table->index('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
