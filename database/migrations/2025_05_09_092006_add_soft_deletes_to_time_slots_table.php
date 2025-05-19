<?php

// database/migrations/xxxx_xx_xx_add_soft_deletes_to_time_slots_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToTimeSlotsTable extends Migration
{
    public function up()
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->softDeletes(); // Adds the `deleted_at` column
        });
    }

    public function down()
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Removes the `deleted_at` column
        });
    }
}