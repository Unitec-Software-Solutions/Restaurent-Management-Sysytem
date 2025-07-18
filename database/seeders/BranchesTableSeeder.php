<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BranchesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('branches')->delete();
        
        \DB::table('branches')->insert(array (
            0 => 
            array (
                'id' => 1,
                'organization_id' => 1,
                'name' => 'Main Branch - Colombo',
                'code' => 'DB-COL-001',
                'address' => '123 Main Street, Colombo 03, Sri Lanka',
                'phone' => '+94 11 123 4567',
                'email' => 'main@deliciousbites.com',
                'opening_time' => '08:00:00',
                'closing_time' => '23:00:00',
                'total_capacity' => 80,
                'reservation_fee' => '500.00',
                'cancellation_fee' => '250.00',
                'type' => 'restaurant',
                'activation_key' => 'iahNJ7OdESw5HVsmmmhwwZFa4ZZ63xi1jm5DRnWP',
                'is_active' => false,
                'created_at' => '2025-07-18 07:11:06',
                'updated_at' => '2025-07-18 07:11:06',
                'deleted_at' => NULL,
                'activated_at' => '2025-07-18 07:11:06',
                'contact_person' => 'Sarah Branch Manager',
                'contact_person_designation' => 'Branch Manager',
                'contact_person_phone' => '+94 77 234 5678',
                'is_head_office' => true,
                'slug' => 'main-branch-colombo',
                'max_capacity' => 80,
                'status' => 'active',
                'features' => NULL,
                'settings' => NULL,
                'opened_at' => NULL,
                'manager_name' => 'Sarah Branch Manager',
                'manager_phone' => '+94 77 234 5678',
                'operating_hours' => NULL,
            ),
        ));
        
        
    }
}