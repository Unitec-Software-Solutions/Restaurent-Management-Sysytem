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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->string('table_number');
            $table->integer('capacity');
            $table->enum('status', ['open', 'reserved', 'occupied', 'dirty'])->default('open');
            $table->integer('x_position')->nullable(); // For table mapping visualization
            $table->integer('y_position')->nullable(); // For table mapping visualization
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
