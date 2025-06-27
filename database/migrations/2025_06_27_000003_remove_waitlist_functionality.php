<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove waitlist-related columns from reservations table
        Schema::table('reservations', function (Blueprint $table) {
            // Remove waitlist status if it exists
            if (Schema::hasColumn('reservations', 'waitlist_status')) {
                $table->dropColumn('waitlist_status');
            }
            
            // Remove waitlist notification preference if it exists
            if (Schema::hasColumn('reservations', 'notify_when_available')) {
                $table->dropColumn('notify_when_available');
            }
        });

        // Remove waitlist-related permissions
        DB::table('permissions')->where('name', 'like', '%waitlist%')->delete();
        
        // Remove waitlist-related role permissions
        DB::table('role_has_permissions')
            ->whereIn('permission_id', function($query) {
                $query->select('id')
                      ->from('permissions')
                      ->where('name', 'like', '%waitlist%');
            })
            ->delete();

        // Update restaurant configs to remove waitlist settings
        Schema::table('restaurant_configs', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_configs', 'max_waitlist_size')) {
                $table->dropColumn('max_waitlist_size');
            }
            if (Schema::hasColumn('restaurant_configs', 'waitlist_enabled')) {
                $table->dropColumn('waitlist_enabled');
            }
        });

        // Remove waitlisted status from reservations status enum (update the enum)
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add back waitlist columns to reservations if needed
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('notify_when_available')->default(false);
            $table->enum('waitlist_status', ['none', 'waiting', 'notified', 'expired'])->default('none');
        });

        // Add back waitlist settings to restaurant configs
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $table->integer('max_waitlist_size')->default(50);
            $table->boolean('waitlist_enabled')->default(false);
        });

        // Add back waitlisted status to reservations
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show', 'waitlisted') DEFAULT 'pending'");
    }
};
