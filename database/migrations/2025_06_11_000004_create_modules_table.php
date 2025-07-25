<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            

            $table->index(['is_active']);
            $table->index(['slug', 'is_active']);
        });
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
