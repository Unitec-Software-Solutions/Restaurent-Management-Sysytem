<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            // Organization 1
            [
                'organization_id' => 1,
                'name' => 'Main Branch',
                'address' => 'No. 25, Galle Road, Colombo 03, Sri Lanka',
                'phone' => '+94 11 234 5678',
                'email' => 'main@restaurant.lk',
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
                'total_capacity' => 100,
                'reservation_fee' => 10.00,
                'cancellation_fee' => 5.00,
                'is_active' => true,
            ],
            [
                'organization_id' => 1,
                'name' => 'Kandy Branch',
                'address' => 'No. 12, Peradeniya Road, Kandy, Sri Lanka',
                'phone' => '+94 81 223 4567',
                'email' => 'kandy@restaurant.lk',
                'opening_time' => '09:00:00',
                'closing_time' => '22:30:00',
                'total_capacity' => 80,
                'reservation_fee' => 10.00,
                'cancellation_fee' => 5.00,
                'is_active' => true,
            ],

            // Organization 2
            [
                'organization_id' => 2,
                'name' => 'Ocean Front',
                'address' => '123 Marine Drive, Coastal City',
                'phone' => '+94 77 123 4567',
                'email' => 'oceanfront@seafood.lk',
                'opening_time' => '11:00:00',
                'closing_time' => '23:00:00',
                'total_capacity' => 90,
                'reservation_fee' => 15.00,
                'cancellation_fee' => 7.50,
                'is_active' => true,
            ],
            [
                'organization_id' => 2,
                'name' => 'Harbor View',
                'address' => 'Pier 4, Harbor District',
                'phone' => '+94 77 234 5678',
                'email' => 'harbor@oceanbreeze.lk',
                'opening_time' => '12:00:00',
                'closing_time' => '22:30:00',
                'total_capacity' => 70,
                'reservation_fee' => 15.00,
                'cancellation_fee' => 7.50,
                'is_active' => true,
            ],

            // Organization 3
            [
                'organization_id' => 3,
                'name' => 'Urban Central',
                'address' => 'Downtown Hub, Metro City',
                'phone' => '+94 76 111 2222',
                'email' => 'central@urbancafe.lk',
                'opening_time' => '07:00:00',
                'closing_time' => '21:00:00',
                'total_capacity' => 60,
                'reservation_fee' => 8.00,
                'cancellation_fee' => 4.00,
                'is_active' => true,
            ],
            [
                'organization_id' => 3,
                'name' => 'Tech Park Cafe',
                'address' => 'Tech Park, Block B',
                'phone' => '+94 76 333 4444',
                'email' => 'tech@urbancafe.lk',
                'opening_time' => '07:30:00',
                'closing_time' => '20:00:00',
                'total_capacity' => 50,
                'reservation_fee' => 8.00,
                'cancellation_fee' => 4.00,
                'is_active' => true,
            ],

            // Organization 4
            [
                'organization_id' => 4,
                'name' => 'Pizza Corner',
                'address' => 'Corner of 5th and Main, Suburbia',
                'phone' => '+94 75 111 3333',
                'email' => 'corner@pizzapalace.lk',
                'opening_time' => '11:00:00',
                'closing_time' => '23:00:00',
                'total_capacity' => 90,
                'reservation_fee' => 12.00,
                'cancellation_fee' => 6.00,
                'is_active' => true,
            ],
            [
                'organization_id' => 4,
                'name' => 'Family Slice',
                'address' => 'Suburban Mall, Food Court',
                'phone' => '+94 75 222 4444',
                'email' => 'family@pizzapalace.lk',
                'opening_time' => '10:00:00',
                'closing_time' => '22:00:00',
                'total_capacity' => 100,
                'reservation_fee' => 12.00,
                'cancellation_fee' => 6.00,
                'is_active' => true,
            ],

            // Organization 5
            [
                'organization_id' => 5,
                'name' => 'Bamboo City',
                'address' => '88 East Street, Chinatown',
                'phone' => '+94 74 123 5678',
                'email' => 'city@bamboogarden.lk',
                'opening_time' => '11:30:00',
                'closing_time' => '22:00:00',
                'total_capacity' => 75,
                'reservation_fee' => 13.00,
                'cancellation_fee' => 6.50,
                'is_active' => true,
            ],
            [
                'organization_id' => 5,
                'name' => 'Fusion Express',
                'address' => 'Mall Kiosk 12, Lotus Centre',
                'phone' => '+94 74 234 6789',
                'email' => 'express@bamboogarden.lk',
                'opening_time' => '12:00:00',
                'closing_time' => '21:30:00',
                'total_capacity' => 45,
                'reservation_fee' => 13.00,
                'cancellation_fee' => 6.50,
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info("  Total Branches in the database : " . Branch::count());
        $this->command->info("  ✅ Branches seeded successfully!");
    }
}
