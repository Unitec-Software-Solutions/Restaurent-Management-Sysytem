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
        Schema::table('menus', function (Blueprint $table) {
            // Add missing columns that MenuController expects
            if (!Schema::hasColumn('menus', 'valid_from')) {
                $table->datetime('valid_from')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('menus', 'valid_until')) {
                $table->datetime('valid_until')->nullable()->after('valid_from');
            }
            
            if (!Schema::hasColumn('menus', 'available_days')) {
                $table->json('available_days')->nullable()->after('valid_until');
            }
            
            if (!Schema::hasColumn('menus', 'start_time')) {
                $table->time('start_time')->nullable()->after('available_days');
            }
            
            if (!Schema::hasColumn('menus', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            
            if (!Schema::hasColumn('menus', 'type')) {
                $table->enum('type', ['breakfast', 'lunch', 'dinner', 'all_day', 'special'])->default('all_day')->after('name');
            }
            
            if (!Schema::hasColumn('menus', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained()->after('type');
            }
            
            if (!Schema::hasColumn('menus', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('admins')->after('end_time');
            }

            // Add indexes for performance (check if they don't exist)
            if (!$this->indexExists('menus', 'menus_valid_from_valid_until_index')) {
                $table->index(['valid_from', 'valid_until']);
            }
            if (!$this->indexExists('menus', 'menus_is_active_valid_from_index')) {
                $table->index(['is_active', 'valid_from']);
            }
            if (!$this->indexExists('menus', 'menus_branch_id_is_active_index')) {
                $table->index(['branch_id', 'is_active']);
            }
        });
    }

    private function indexExists($table, $index)
    {
        try {
            // For PostgreSQL, use information_schema
            $connection = Schema::getConnection();
            if ($connection->getDriverName() === 'pgsql') {
                $exists = $connection->selectOne(
                    "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                    [$table, $index]
                );
                return !is_null($exists);
            }
            
            // For MySQL and other databases
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
            return array_key_exists($index, $indexes);
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['valid_from', 'valid_until']);
            $table->dropIndex(['is_active', 'valid_from']);
            $table->dropIndex(['branch_id', 'is_active']);
            
            $table->dropColumn([
                'valid_from',
                'valid_until', 
                'available_days',
                'start_time',
                'end_time',
                'type',
                'branch_id',
                'created_by'
            ]);
        });
    }
};
