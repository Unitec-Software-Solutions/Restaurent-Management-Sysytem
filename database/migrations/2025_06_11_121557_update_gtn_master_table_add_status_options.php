<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gtn_master', function (Blueprint $table) {
            // Update status column to include new options
            $table->string('status', 50)->default('Pending')->change();
        });

        // Add comment to clarify status options using PostgreSQL-compatible syntax
        if (config('database.default') === 'pgsql') {
            DB::statement("COMMENT ON COLUMN gtn_master.status IS 'Pending, Confirmed, Approved, Verified, Completed, Cancelled'");
        } else {
            // MySQL syntax
            DB::statement("ALTER TABLE gtn_master MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending' COMMENT 'Pending, Confirmed, Approved, Verified, Completed, Cancelled'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gtn_master', function (Blueprint $table) {
            // Revert to original status comment
            if (config('database.default') === 'pgsql') {
                DB::statement("COMMENT ON COLUMN gtn_master.status IS 'Pending, In Transit, Completed, Cancelled'");
            } else {
                // MySQL syntax
                DB::statement("ALTER TABLE gtn_master MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending' COMMENT 'Pending, In Transit, Completed, Cancelled'");
            }
        });
    }
};
