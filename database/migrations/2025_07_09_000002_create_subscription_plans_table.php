<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('modules')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_trial')->default(false)->nullable();
            $table->integer('trial_period_days')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('duration')->nullable();
            $table->string('duration_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
};
