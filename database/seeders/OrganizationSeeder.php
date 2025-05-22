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
                'name' => 'Ceylon Spice House',
                'trading_name' => 'Spice House',
                'registration_number' => 'REGSL001',
                'description' => 'Authentic Sri Lankan cuisine with a modern twist',
                'email' => 'info@spicehouse.lk',
                'phone' => '+94 11 234 5678',
                'alternative_phone' => '+94 77 123 4567',
                'address' => '123 Galle Road, Colombo 03, Sri Lanka',
                'website' => 'https://spicehouse.lk',
                'logo' => 'logos/spice-house.png',
                'business_hours' => json_encode([
                    'monday' => ['10:00-22:00'],
                    'tuesday' => ['10:00-22:00'],
                    'wednesday' => ['10:00-22:00'],
                    'thursday' => ['10:00-22:30'],
                    'friday' => ['10:00-23:00'],
                    'saturday' => ['10:00-23:00'],
                    'sunday' => ['10:00-21:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Hill Country Kitchens',
                'trading_name' => 'Kandy Kitchen',
                'registration_number' => 'REGSL002',
                'description' => 'Traditional meals from Sri Lanka’s hill country',
                'email' => 'hello@kandykitchen.lk',
                'phone' => '+94 81 222 3344',
                'alternative_phone' => '+94 76 987 6543',
                'address' => '45 Peradeniya Road, Kandy, Sri Lanka',
                'website' => 'https://kandykitchen.lk',
                'logo' => 'logos/kandy-kitchen.png',
                'business_hours' => json_encode([
                    'monday' => ['09:00-21:00'],
                    'tuesday' => ['09:00-21:00'],
                    'wednesday' => ['09:00-21:00'],
                    'thursday' => ['09:00-22:00'],
                    'friday' => ['09:00-22:00'],
                    'saturday' => ['10:00-22:00'],
                    'sunday' => ['10:00-20:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Southern Coast Delights',
                'trading_name' => 'Galle Bites',
                'registration_number' => 'REGSL003',
                'description' => 'Fresh seafood and coastal delicacies',
                'email' => 'contact@gallebites.lk',
                'phone' => '+94 91 223 5566',
                'alternative_phone' => '+94 71 345 6789',
                'address' => '7 Beach Road, Galle, Sri Lanka',
                'website' => 'https://gallebites.lk',
                'logo' => 'logos/galle-bites.png',
                'business_hours' => json_encode([
                    'monday' => ['11:00-22:00'],
                    'tuesday' => ['11:00-22:00'],
                    'wednesday' => ['11:00-22:00'],
                    'thursday' => ['11:00-23:00'],
                    'friday' => ['11:00-23:30'],
                    'saturday' => ['11:00-23:30'],
                    'sunday' => ['11:00-21:30'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Negombo Curry House',
                'trading_name' => 'Negombo Curry',
                'registration_number' => 'REGSL004',
                'description' => 'Best local curries and rice dishes by the sea',
                'email' => 'orders@negombocurry.lk',
                'phone' => '+94 31 225 8899',
                'alternative_phone' => '+94 70 112 2334',
                'address' => '56 Lewis Place, Negombo, Sri Lanka',
                'website' => 'https://negombocurry.lk',
                'logo' => 'logos/negombo-curry.png',
                'business_hours' => json_encode([
                    'monday' => ['10:00-21:30'],
                    'tuesday' => ['10:00-21:30'],
                    'wednesday' => ['10:00-21:30'],
                    'thursday' => ['10:00-22:00'],
                    'friday' => ['10:00-22:30'],
                    'saturday' => ['10:00-22:30'],
                    'sunday' => ['10:00-21:00'],
                ]),
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => 'Airport Express Meals',
                'trading_name' => 'Express Meals',
                'registration_number' => 'REGSL005',
                'description' => 'Quick Sri Lankan meals for travelers',
                'email' => 'support@expressmeals.lk',
                'phone' => '+94 11 225 3344',
                'alternative_phone' => '+94 77 888 9990',
                'address' => 'Bandaranaike International Airport, Katunayake, Sri Lanka',
                'website' => 'https://expressmeals.lk',
                'logo' => 'logos/express-meals.png',
                'business_hours' => json_encode([
                    'monday' => ['05:00-23:59'],
                    'tuesday' => ['05:00-23:59'],
                    'wednesday' => ['05:00-23:59'],
                    'thursday' => ['05:00-23:59'],
                    'friday' => ['05:00-23:59'],
                    'saturday' => ['05:00-23:59'],
                    'sunday' => ['05:00-23:59'],
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

        $this->command->info('  Total Organizations in the database: ' . Organizations::count());
        $this->command->info('  ✅ Organizations seeded successfully.');
    }
}
