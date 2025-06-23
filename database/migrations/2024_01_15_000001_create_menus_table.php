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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->boolean('is_active')->default(false);
            $table->enum('menu_type', ['regular', 'special', 'seasonal', 'promotional'])->default('regular');
            $table->json('days_of_week')->nullable(); // Array of day numbers (0-6)
            $table->time('activation_time')->nullable();
            $table->time('deactivation_time')->nullable();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->integer('priority')->default(0); // Higher priority = preferred
            $table->boolean('auto_activate')->default(true);
            $table->string('special_occasion')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['branch_id', 'is_active']);
            $table->index(['date_from', 'date_to']);
            $table->index(['menu_type', 'is_active']);
            $table->index(['auto_activate', 'date_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
