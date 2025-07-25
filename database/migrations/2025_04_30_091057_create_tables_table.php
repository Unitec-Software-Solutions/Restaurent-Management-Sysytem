<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('number');
            $table->integer('capacity');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->string('location')->nullable();
            $table->timestamps();
            $table->text('description')->nullable();
            $table->integer('x_position')->nullable(); 
            $table->integer('y_position')->nullable(); 
            $table->unique(['branch_id', 'number']);
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); 
        });

        Schema::create('reservation_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['reservation_id', 'table_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservation_tables');
        Schema::dropIfExists('tables');
    }
}; 