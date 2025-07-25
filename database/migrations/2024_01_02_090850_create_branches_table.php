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
            $table->string('code')->nullable();
            $table->string('address');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->time('opening_time')->nullable()->default('09:00:00');
            $table->time('closing_time')->nullable()->default('22:00:00');
            $table->integer('total_capacity');
            $table->decimal('reservation_fee', 10, 2);
            $table->decimal('cancellation_fee', 10, 2);
            $table->string('type')->default('branch');
            $table->string('activation_key')->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
};
