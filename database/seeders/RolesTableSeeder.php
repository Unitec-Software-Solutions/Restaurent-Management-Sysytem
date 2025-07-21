<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Organization Administrator',
                'guard_name' => 'admin',
                'created_at' => '2025-07-18 10:47:32',
                'updated_at' => '2025-07-21 04:04:55',
                'deleted_at' => '2025-07-21 04:04:55',
                'organization_id' => 1,
                'branch_id' => NULL,
                'scope' => 'organization',
                'is_system_role' => false,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Organization Administrator',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:05:42',
                'updated_at' => '2025-07-21 04:05:42',
                'deleted_at' => NULL,
                'organization_id' => 1,
                'branch_id' => NULL,
                'scope' => 'organization',
                'is_system_role' => false,
            ),
        ));
        
        
    }
}