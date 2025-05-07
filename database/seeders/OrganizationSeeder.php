<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organizations;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organizations::create([
            'name' => 'Main Restaurant Group',
            'trading_name' => 'Cityville Eats',
            'registration_number' => 'REG123456789',
            'description' => 'A premium restaurant group specializing in fine dining',
            'email' => 'admin@restaurantgroup.com',
            'phone' => '(555) 888-9999',
            'alternative_phone' => '(555) 888-0000',
            'address' => '123 Corporate Blvd, Cityville',
            'website' => 'https://restaurantgroup.com',
            'logo' => 'logos/main-group.png',
            'business_hours' => json_encode([
                'monday' => ['09:00-18:00'],
                'tuesday' => ['09:00-18:00'],
                'wednesday' => ['09:00-18:00'],
                'thursday' => ['09:00-18:00'],
                'friday' => ['09:00-18:00'],
                'saturday' => ['10:00-16:00'],
                'sunday' => ['Closed'],
            ]),
            'business_type' => 'restaurant',
            'status' => 'active',
            'is_active' => true,
        ]);
    }
}