<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->time('opening_time');
            $table->time('closing_time');
            $table->integer('total_capacity');
            $table->decimal('reservation_fee', 10, 2);
            $table->decimal('cancellation_fee', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
}; 