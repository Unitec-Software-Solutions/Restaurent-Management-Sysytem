<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OrganizationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('organizations')->delete();
        
        \DB::table('organizations')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Delicious Bites Restaurant',
                'description' => NULL,
                'address' => '123 Main Street, Colombo 03, Sri Lanka',
                'email' => 'admin@deliciousbites.com',
                'website' => NULL,
                'logo' => NULL,
                'trading_name' => NULL,
                'registration_number' => NULL,
                'phone' => '+94 11 123 4567',
                'alternative_phone' => NULL,
                'activation_key' => 'm6dwhc1TbEQ5J4eQ9I6uMBuNiQdPPuEX45NVntqH',
                'is_active' => true,
                'activated_at' => '2025-07-18 07:11:05',
                'business_hours' => NULL,
                'created_at' => '2025-07-18 07:11:05',
                'updated_at' => '2025-07-18 07:11:05',
                'deleted_at' => NULL,
                'password' => '$2y$12$QlOHzH.kuqT9av2NWPVp0OToOmrasMl89ZDaQtsDVJayKpMED6JCe',
                'contact_person' => 'John Manager',
                'contact_person_designation' => 'General Manager',
                'contact_person_phone' => '+94 77 123 4567',
                'discount_percentage' => '5.00',
                'subscription_plan_id' => 1,
                'plan_name' => NULL,
                'plan_price' => NULL,
                'plan_currency' => NULL,
                'plan_modules' => NULL,
                'business_type' => 'restaurant',
            ),
        ));
        
        
    }
}