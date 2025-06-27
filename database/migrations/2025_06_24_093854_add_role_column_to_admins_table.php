<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('admins');
            
            // Add role column if it doesn't exist (for backward compatibility)
            if (!in_array('role', $existingColumns)) {
                $table->string('role')->nullable()->after('organization_id')->comment('Deprecated: Use Spatie roles instead');
            }
            
            // Add proper role_id foreign key for Spatie integration
            if (!in_array('current_role_id', $existingColumns)) {
                $table->unsignedBigInteger('current_role_id')->nullable()->after('role')->comment('Current active role from Spatie roles');
            }
            
            // Add department/division for better organization
            if (!in_array('department', $existingColumns)) {
                $table->string('department')->nullable()->after('current_role_id');
            }
            
            // Add job title for UI display
            if (!in_array('job_title', $existingColumns)) {
                $table->string('job_title')->nullable()->after('department');
            }
            
            // Add status tracking for better admin management
            if (!in_array('status', $existingColumns)) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active')->after('is_active');
            }
            
            // Add foreign key constraint for current_role_id
            if (Schema::hasTable('roles')) {
                $table->foreign('current_role_id')->references('id')->on('roles')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['current_role_id']);
            
            // Drop columns
            $table->dropColumn([
                'role',
                'current_role_id', 
                'department',
                'job_title',
                'status'
            ]);
        });
    }
};
