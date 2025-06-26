<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeUserTypeToStringOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, drop the check constraint if it exists
        DB::statement('ALTER TABLE users ALTER COLUMN user_type DROP DEFAULT;');
        // Remove the check constraint (Postgres syntax)
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check;');
        // Change column to string (varchar)
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert to enum or previous state
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'manager', 'chef', 'waiter', 'cashier', 'customer'])->change();
        });
    }
};
