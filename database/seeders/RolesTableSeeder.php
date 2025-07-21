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
                'id' => 3,
                'name' => 'Organization Administrator',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 05:15:27',
                'updated_at' => '2025-07-21 05:15:27',
                'deleted_at' => NULL,
                'organization_id' => 1,
                'branch_id' => NULL,
                'scope' => 'organization',
                'is_system_role' => false,
            ),
        ));
        
        
    }
}