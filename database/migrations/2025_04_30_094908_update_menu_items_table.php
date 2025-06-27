<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('category')->default('general');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('image_url')->nullable();
            $table->json('allergens')->nullable();
            $table->json('dietary_info')->nullable(); // vegetarian, vegan, etc.
            $table->integer('preparation_time')->nullable(); // in minutes
            $table->integer('calories')->nullable();
            $table->decimal('portion_size', 8, 2)->nullable();
            $table->string('portion_unit')->nullable(); // grams, ml, pieces
            $table->integer('sort_order')->default(0);
            
            // Foreign keys
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['organization_id', 'is_available']);
            $table->index(['branch_id', 'category']);
            $table->index(['is_featured', 'is_available']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};