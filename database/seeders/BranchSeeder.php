<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            [
                'id' => 1,
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
                'id' => 2,
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
            [
                'id' => 3,
                'organization_id' => 1,
                'name' => 'Galle Branch',
                'address' => 'No. 7, Lighthouse Street, Galle Fort, Sri Lanka',
                'phone' => '+94 91 223 7890',
                'email' => 'galle@restaurant.lk',
                'opening_time' => '10:00:00',
                'closing_time' => '23:00:00',
                'total_capacity' => 60,
                'reservation_fee' => 10.00,
                'cancellation_fee' => 5.00,
                'is_active' => true,
            ],
            [
                'id' => 4,
                'organization_id' => 1,
                'name' => 'Negombo Branch',
                'address' => 'No. 56, Lewis Place, Negombo, Sri Lanka',
                'phone' => '+94 31 222 3344',
                'email' => 'negombo@restaurant.lk',
                'opening_time' => '08:30:00',
                'closing_time' => '21:30:00',
                'total_capacity' => 70,
                'reservation_fee' => 10.00,
                'cancellation_fee' => 5.00,
                'is_active' => true,
            ],
            [
                'id' => 5,
                'organization_id' => 1,
                'name' => 'Airport Express',
                'address' => 'Bandaranaike International Airport, Katunayake, Sri Lanka',
                'phone' => '+94 11 225 5555',
                'email' => 'airport@restaurant.lk',
                'opening_time' => '05:00:00',
                'closing_time' => '23:59:00',
                'total_capacity' => 40,
                'reservation_fee' => 10.00,
                'cancellation_fee' => 5.00,
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['id' => $branch['id']],
                $branch
            );
        }

        $this->command->info('Branches seeded successfully!');
    }
}
