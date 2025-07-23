<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('admins')->delete();

        \DB::table('admins')->insert([
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@rms.com',
                'password' => '$2y$12$oZeFWflZnmyAnbme/tcp1.JxQ5J09m1qu8Qk55ILR0rusngg0TZmq',
                'branch_id' => NULL,
                'organization_id' => NULL,
                'remember_token' => NULL,
                'created_at' => '2025-07-18 10:46:04',
                'updated_at' => '2025-07-18 10:46:04',
                'is_super_admin' => true,
                'is_active' => true,
                'deleted_at' => NULL,
                'email_verified_at' => '2025-07-18 10:46:04',
                'phone' => '+94 11 000 0000',
                'profile_image' => NULL,
                'last_login_at' => NULL,
                'preferences' => '"{\\"timezone\\":\\"UTC\\",\\"language\\":\\"en\\",\\"theme\\":\\"light\\",\\"notifications\\":true}"',
                'ui_settings' => '{"theme":"light","sidebar_collapsed":false,"dashboard_layout":"grid","notifications_enabled":true,"preferred_language":"en","cards_per_row":4,"show_help_tips":true}',
                'failed_login_attempts' => 0,
                'locked_until' => NULL,
                'password_changed_at' => NULL,
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'role' => NULL,
                'current_role_id' => NULL,
                'department' => 'System Administration',
                'job_title' => 'System Administrator',
                'status' => 'active',
                'hired_at' => NULL,
            ],
        ]);


    }
}
