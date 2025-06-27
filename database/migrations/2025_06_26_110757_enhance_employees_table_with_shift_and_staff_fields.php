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
        Schema::table('employees', function (Blueprint $table) {
            // Essential Shift Management
            if (!Schema::hasColumn('employees', 'shift_type')) {
                $table->enum('shift_type', ['morning', 'evening', 'night', 'flexible'])->default('flexible')->after('role');
            }
            if (!Schema::hasColumn('employees', 'shift_start_time')) {
                $table->time('shift_start_time')->nullable()->after('shift_type');
            }
            if (!Schema::hasColumn('employees', 'shift_end_time')) {
                $table->time('shift_end_time')->nullable()->after('shift_start_time');
            }

            // Essential Staff Management
            if (!Schema::hasColumn('employees', 'hourly_rate')) {
                $table->decimal('hourly_rate', 8, 2)->nullable()->after('salary');
            }
            if (!Schema::hasColumn('employees', 'department')) {
                $table->string('department')->nullable()->after('position');
            }
            if (!Schema::hasColumn('employees', 'availability_status')) {
                $table->enum('availability_status', ['available', 'busy', 'on_break', 'off_duty'])->default('available')->after('department');
            }
            if (!Schema::hasColumn('employees', 'current_workload')) {
                $table->integer('current_workload')->default(0)->after('availability_status');
            }

            // Basic indexes for performance
            $table->index(['shift_type', 'availability_status'], 'idx_employees_shift_availability');
            $table->index(['branch_id', 'is_active'], 'idx_employees_branch_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_employees_shift_availability');
            $table->dropIndex('idx_employees_branch_active');

            // Drop only the essential columns we added
            $columns = [
                'shift_type', 'shift_start_time', 'shift_end_time',
                'hourly_rate', 'department', 'availability_status', 'current_workload'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
