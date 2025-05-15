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
        Schema::table('reservations', function (Blueprint $table) {
            $table->json('assigned_table_ids')->nullable()->after('comments'); // To store assigned table IDs
            $table->unsignedBigInteger('created_by_admin_id')->nullable()->after('branch_id'); // To track admin who created the reservation
            $table->foreign('created_by_admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['assigned_table_ids', 'created_by_admin_id']);
        });
    }
};
