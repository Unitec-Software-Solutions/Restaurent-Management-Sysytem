<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX performance guidelines.
     */
    public function up(): void
    {
        // Add indexes for performance following UI/UX query patterns
        Schema::table('admins', function (Blueprint $table) {
            try {
                // Check if indexes don't exist before creating them
                $indexes = $this->getExistingIndexes('admins');
                
                if (!in_array('admins_organization_active_idx', $indexes)) {
                    $table->index(['organization_id', 'is_active'], 'admins_organization_active_idx');
                }
                
                if (!in_array('admins_branch_active_idx', $indexes)) {
                    $table->index(['branch_id', 'is_active'], 'admins_branch_active_idx');
                }
                
                if (!in_array('admins_email_active_idx', $indexes)) {
                    $table->index(['email', 'is_active'], 'admins_email_active_idx');
                }
                
                if (!in_array('admins_super_admin_idx', $indexes)) {
                    $table->index('is_super_admin', 'admins_super_admin_idx');
                }
                
            } catch (\Exception $e) {
                // Silently continue if indexes can't be created
                // This prevents migration failures on different database systems
            }
        });
    }
    
    /**
     * Get existing indexes for a table (PostgreSQL and MySQL compatible)
     */
    private function getExistingIndexes(string $table): array
    {
        try {
            if (config('database.default') === 'pgsql') {
                // PostgreSQL query to get indexes
                $indexes = DB::select("
                    SELECT indexname 
                    FROM pg_indexes 
                    WHERE tablename = ? AND schemaname = 'public'
                ", [$table]);
                
                return array_column($indexes, 'indexname');
            } else {
                // MySQL query to get indexes
                $indexes = DB::select("SHOW INDEX FROM {$table}");
                return array_unique(array_column($indexes, 'Key_name'));
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            try {
                $table->dropIndex('admins_organization_active_idx');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('admins_branch_active_idx');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('admins_email_active_idx');
            } catch (\Exception $e) {}
            
            try {
                $table->dropIndex('admins_super_admin_idx');
            } catch (\Exception $e) {}
        });
    }
};
