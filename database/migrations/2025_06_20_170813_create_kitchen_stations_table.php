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
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['cooking', 'prep', 'beverage', 'dessert', 'grill', 'fry', 'bar'])->default('cooking');
            $table->boolean('is_active')->default(true);
            $table->integer('order_priority')->default(1);
            $table->json('printer_config')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['branch_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_stations');
    }
};
