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
        // Check if staff table exists before trying to modify it
        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->foreignId('shift_id')->nullable()->after('branch_id')->constrained()->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if staff table exists before trying to modify it
        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            });
        }
    }
};
