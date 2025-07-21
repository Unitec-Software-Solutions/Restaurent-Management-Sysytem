<?php
// database/migrations/2025_07_21_000001_update_guard_name_for_admin_roles_permissions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update guard_name to 'admin' for all roles and permissions
        DB::table('roles')->update(['guard_name' => 'admin']);
        DB::table('permissions')->update(['guard_name' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: cannot safely revert without knowing previous guard_name
    }
};
