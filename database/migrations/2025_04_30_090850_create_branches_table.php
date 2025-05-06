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
            $table->text('address');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->boolean('is_head_office')->default(false);
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['organization_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
}; 