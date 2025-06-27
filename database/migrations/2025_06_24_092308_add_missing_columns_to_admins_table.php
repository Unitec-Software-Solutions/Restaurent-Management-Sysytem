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
            
            // Add missing columns following UI/UX admin system requirements
            if (!in_array('deleted_at', $existingColumns)) {
                $table->softDeletes();
            }
            
            if (!in_array('email_verified_at', $existingColumns)) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            
            if (!in_array('phone', $existingColumns)) {
                $table->string('phone')->nullable()->after('email');
            }
            
            if (!in_array('profile_image', $existingColumns)) {
                $table->string('profile_image')->nullable()->after('phone');
            }
            
            if (!in_array('last_login_at', $existingColumns)) {
                $table->timestamp('last_login_at')->nullable()->after('profile_image');
            }
            
            if (!in_array('preferences', $existingColumns)) {
                $table->json('preferences')->nullable()->after('last_login_at');
            }
            
            if (!in_array('ui_settings', $existingColumns)) {
                $table->json('ui_settings')->nullable()->after('preferences');
            }
            
            if (!in_array('failed_login_attempts', $existingColumns)) {
                $table->integer('failed_login_attempts')->default(0)->after('ui_settings');
            }
            
            if (!in_array('locked_until', $existingColumns)) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'deleted_at',
                'email_verified_at', 
                'phone',
                'profile_image',
                'last_login_at',
                'preferences',
                'ui_settings',
                'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};
