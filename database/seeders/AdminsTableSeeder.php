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
        
        \DB::table('admins')->insert(array (
            0 => 
            array (
                'id' => 1,
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
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Organization Admin',
                'email' => 'orgadmin@deliciousbites.com',
                'password' => '$2y$12$dbj2xywMsb2phxmkiN8c8OFDlrszl18spIQxTZU7HwOn6mj7y.Q6i',
                'branch_id' => NULL,
                'organization_id' => NULL,
                'remember_token' => NULL,
                'created_at' => '2025-07-18 10:46:05',
                'updated_at' => '2025-07-18 10:46:05',
                'is_super_admin' => false,
                'is_active' => true,
                'deleted_at' => NULL,
                'email_verified_at' => '2025-07-18 10:46:05',
                'phone' => '+94 77 123 4567',
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
                'department' => 'Management',
                'job_title' => 'Org Admin',
                'status' => 'active',
                'hired_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Branch Admin',
                'email' => 'branchadmin@deliciousbites.com',
                'password' => '$2y$12$rYfF7IltqIiV2EX502EpmOX6URrlT9TPoRFeSjY9E1m0NUcWZ1zvW',
                'branch_id' => NULL,
                'organization_id' => NULL,
                'remember_token' => NULL,
                'created_at' => '2025-07-18 10:46:05',
                'updated_at' => '2025-07-18 10:46:05',
                'is_super_admin' => false,
                'is_active' => true,
                'deleted_at' => NULL,
                'email_verified_at' => '2025-07-18 10:46:05',
                'phone' => '+94 77 234 5678',
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
                'department' => 'Branch Management',
                'job_title' => 'Branch Admin',
                'status' => 'active',
                'hired_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'test',
                'email' => 'test123@gmail.com',
                'password' => '$2y$12$C36sMxRrNA//i0.4KoDxWOFNYTaVcxeZqxFO0ohm.2woUL0g/H3Py',
                'branch_id' => NULL,
                'organization_id' => 1,
                'remember_token' => NULL,
                'created_at' => '2025-07-18 10:48:11',
                'updated_at' => '2025-07-18 10:48:11',
                'is_super_admin' => false,
                'is_active' => true,
                'deleted_at' => NULL,
                'email_verified_at' => NULL,
                'phone' => NULL,
                'profile_image' => NULL,
                'last_login_at' => NULL,
                'preferences' => '{"timezone":"Asia\\/Colombo","date_format":"Y-m-d","time_format":"24h","currency":"LKR"}',
                'ui_settings' => '{"theme":"light","sidebar_collapsed":false,"dashboard_layout":"grid","notifications_enabled":true,"preferred_language":"en","cards_per_row":4,"show_help_tips":true}',
                'failed_login_attempts' => 0,
                'locked_until' => NULL,
                'password_changed_at' => NULL,
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'role' => NULL,
                'current_role_id' => NULL,
                'department' => NULL,
                'job_title' => NULL,
                'status' => 'active',
                'hired_at' => NULL,
            ),
        ));
        
        
    }
}