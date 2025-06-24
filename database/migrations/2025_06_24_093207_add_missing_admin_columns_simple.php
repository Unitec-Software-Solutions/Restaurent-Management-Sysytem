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
            
            // Core soft delete support (most important for the error we're fixing)
            if (!in_array('deleted_at', $existingColumns)) {
                $table->softDeletes();
            }
            
            // Email verification for admin security
            if (!in_array('email_verified_at', $existingColumns)) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            
            // Contact and profile information following UI/UX guidelines
            if (!in_array('phone', $existingColumns)) {
                $table->string('phone')->nullable()->after('email_verified_at');
            }
            
            if (!in_array('profile_image', $existingColumns)) {
                $table->string('profile_image')->nullable()->after('phone');
            }
            
            // Login tracking for UI dashboard
            if (!in_array('last_login_at', $existingColumns)) {
                $table->timestamp('last_login_at')->nullable()->after('profile_image');
            }
            
            // UI preferences and settings (JSON for flexible admin customization)
            if (!in_array('preferences', $existingColumns)) {
                $table->json('preferences')->nullable()->after('last_login_at');
            }
            
            if (!in_array('ui_settings', $existingColumns)) {
                $table->json('ui_settings')->nullable()->after('preferences');
            }
            
            // Security features for admin accounts
            if (!in_array('failed_login_attempts', $existingColumns)) {
                $table->integer('failed_login_attempts')->default(0)->after('ui_settings');
            }
            
            if (!in_array('locked_until', $existingColumns)) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
            
            if (!in_array('password_changed_at', $existingColumns)) {
                $table->timestamp('password_changed_at')->nullable()->after('locked_until');
            }
            
            // Two-factor authentication support
            if (!in_array('two_factor_secret', $existingColumns)) {
                $table->string('two_factor_secret')->nullable()->after('password_changed_at');
            }
            
            if (!in_array('two_factor_recovery_codes', $existingColumns)) {
                $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            
            if (!in_array('two_factor_confirmed_at', $existingColumns)) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Remove columns in reverse order
            $columnsToRemove = [
                'two_factor_confirmed_at',
                'two_factor_recovery_codes',
                'two_factor_secret',
                'password_changed_at',
                'locked_until',
                'failed_login_attempts',
                'ui_settings',
                'preferences',
                'last_login_at',
                'profile_image',
                'phone',
                'email_verified_at',
                'deleted_at'
            ];
            
            $existingColumns = Schema::getColumnListing('admins');
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $existingColumns)) {
                    if ($column === 'deleted_at') {
                        $table->dropSoftDeletes();
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
