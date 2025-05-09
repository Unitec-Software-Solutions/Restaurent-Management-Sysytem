<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organizations;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
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
            ],
            [
                'name' => 'Ocean Breeze Seafood',
                'trading_name' => 'Ocean Breeze',
                'registration_number' => 'REG987654321',
                'description' => 'Fresh seafood restaurant with waterfront views',
                'email' => 'info@oceanbreeze.com',
                'phone' => '(555) 777-8888',
                'alternative_phone' => '(555) 777-9999',
                'address' => '45 Harbor Drive, Coastal City',
                'website' => 'https://oceanbreeze.com',
                'logo' => 'logos/ocean-breeze.png',
                'business_hours' => json_encode([
                    'monday' => ['11:00-22:00'],
                    'tuesday' => ['11:00-22:00'],
                    'wednesday' => ['11:00-22:00'],
                    'thursday' => ['11:00-23:00'],
                    'friday' => ['11:00-23:00'],
                    'saturday' => ['10:00-23:00'],
                    'sunday' => ['10:00-21:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Urban Cafe Collective',
                'trading_name' => 'Urban Cafe',
                'registration_number' => 'REG456789123',
                'description' => 'Trendy cafe serving artisanal coffee and light meals',
                'email' => 'hello@urbancafe.com',
                'phone' => '(555) 666-7777',
                'alternative_phone' => '(555) 666-8888',
                'address' => '89 Downtown Ave, Metro City',
                'website' => 'https://urbancafe.com',
                'logo' => 'logos/urban-cafe.png',
                'business_hours' => json_encode([
                    'monday' => ['07:00-19:00'],
                    'tuesday' => ['07:00-19:00'],
                    'wednesday' => ['07:00-19:00'],
                    'thursday' => ['07:00-21:00'],
                    'friday' => ['07:00-21:00'],
                    'saturday' => ['08:00-18:00'],
                    'sunday' => ['08:00-16:00'],
                ]),
                'business_type' => 'cafe',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Pizza Palace Inc.',
                'trading_name' => 'Pizza Palace',
                'registration_number' => 'REG321654987',
                'description' => 'Family-friendly pizza restaurant with gourmet options',
                'email' => 'orders@pizzapalace.com',
                'phone' => '(555) 444-5555',
                'alternative_phone' => '(555) 444-6666',
                'address' => '22 Main Street, Suburbia',
                'website' => 'https://pizzapalace.com',
                'logo' => 'logos/pizza-palace.png',
                'business_hours' => json_encode([
                    'monday' => ['11:00-22:00'],
                    'tuesday' => ['11:00-22:00'],
                    'wednesday' => ['11:00-22:00'],
                    'thursday' => ['11:00-23:00'],
                    'friday' => ['11:00-24:00'],
                    'saturday' => ['11:00-24:00'],
                    'sunday' => ['11:00-22:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Asian Fusion Group',
                'trading_name' => 'Bamboo Garden',
                'registration_number' => 'REG789123456',
                'description' => 'Modern Asian cuisine blending flavors from across the continent',
                'email' => 'reservations@bamboogarden.com',
                'phone' => '(555) 333-4444',
                'alternative_phone' => '(555) 333-5555',
                'address' => '37 Eastside Road, Chinatown',
                'website' => 'https://bamboogarden.com',
                'logo' => 'logos/bamboo-garden.png',
                'business_hours' => json_encode([
                    'monday' => ['11:30-22:00'],
                    'tuesday' => ['11:30-22:00'],
                    'wednesday' => ['11:30-22:00'],
                    'thursday' => ['11:30-23:00'],
                    'friday' => ['11:30-23:00'],
                    'saturday' => ['12:00-23:00'],
                    'sunday' => ['12:00-21:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ]
        ];

        foreach ($organizations as $org) {
            Organizations::firstOrCreate(
                ['name' => $org['name']],
                $org
            );
        }
    }
}