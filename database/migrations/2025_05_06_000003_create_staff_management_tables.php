<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_training_mode')->default(false);
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('status')->default('present')->comment('present, absent, late, half-day');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('trainer_id')->nullable()->constrained('staff_profiles')->onDelete('set null');
            $table->string('training_type');
            $table->text('description');
            $table->date('training_date');
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_training_records');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff_shifts');
        Schema::dropIfExists('shifts');
    }
}; 