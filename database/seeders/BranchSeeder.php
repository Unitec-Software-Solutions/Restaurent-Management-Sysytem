<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branchData = [
            1 => [
                ['Colombo HQ', 'No. 25, Galle Road, Colombo 03, Sri Lanka', '+94 11 234 5678', 'colombo@mainrest.lk'],
                ['Kandy Outlet', 'No. 12, Peradeniya Road, Kandy, Sri Lanka', '+94 81 223 4567', 'kandy@mainrest.lk'],
                ['Galle Branch', '88 Lighthouse Street, Galle Fort', '+94 91 223 1122', 'galle@mainrest.lk'],
                ['Negombo Branch', '45 Beachside Rd, Negombo', '+94 31 567 1234', 'negombo@mainrest.lk'],
                ['Kurunegala Branch', 'Mall Complex, Kurunegala', '+94 37 224 9988', 'kurunegala@mainrest.lk'],
            ],
            2 => [
                ['Oceanfront Colombo', 'Marine Drive, Colombo 04', '+94 77 456 7890', 'colombo@oceanbreeze.lk'],
                ['Galle Sea View', 'Beach Road, Galle', '+94 77 567 4321', 'galle@oceanbreeze.lk'],
                ['Trinco Bay Branch', 'Nilaveli Beach Rd, Trincomalee', '+94 77 987 6543', 'trinco@oceanbreeze.lk'],
                ['Negombo Pier', 'Pier 3, Negombo Harbour', '+94 77 222 3333', 'pier@oceanbreeze.lk'],
                ['Batticaloa Breeze', 'Passikudah Beach Rd', '+94 77 333 4444', 'batti@oceanbreeze.lk'],
            ],
            3 => [
                ['Urban Cafe HQ', 'Downtown Hub, Colombo 01', '+94 76 123 4567', 'hq@urbancafe.lk'],
                ['Colombo Tech Cafe', 'Trace Expert City, Maradana', '+94 76 234 5678', 'tech@urbancafe.lk'],
                ['Nugegoda Cafe', 'High-Level Rd, Nugegoda', '+94 76 345 6789', 'nugegoda@urbancafe.lk'],
                ['Kandy Lakeview', 'Near Lake Round, Kandy', '+94 76 456 7890', 'kandy@urbancafe.lk'],
                ['Gampaha Cafe', 'Main Street, Gampaha', '+94 76 567 8901', 'gampaha@urbancafe.lk'],
            ],
            4 => [
                ['Pizza Palace HQ', 'Main Street, Dehiwala', '+94 75 111 2222', 'hq@pizzapalace.lk'],
                ['Mt. Lavinia Branch', 'Beach Rd, Mt. Lavinia', '+94 75 222 3333', 'mtl@pizzapalace.lk'],
                ['Colombo South Branch', 'Havelock Town, Colombo 05', '+94 75 333 4444', 'south@pizzapalace.lk'],
                ['Wattala Branch', 'Negombo Rd, Wattala', '+94 75 444 5555', 'wattala@pizzapalace.lk'],
                ['Maharagama Branch', 'High-Level Rd, Maharagama', '+94 75 555 6666', 'maharagama@pizzapalace.lk'],
            ],
            5 => [
                ['Bamboo HQ', 'Lotus Rd, Colombo 01', '+94 74 111 2233', 'hq@bamboogarden.lk'],
                ['Colombo East', 'Rajagiriya Junction', '+94 74 222 3344', 'east@bamboogarden.lk'],
                ['Kandy Central', 'Temple Rd, Kandy', '+94 74 333 4455', 'kandy@bamboogarden.lk'],
                ['Jaffna Branch', 'Hospital Rd, Jaffna', '+94 74 444 5566', 'jaffna@bamboogarden.lk'],
                ['Matara Branch', 'Beach Road, Matara', '+94 74 555 6677', 'matara@bamboogarden.lk'],
            ],
        ];

        foreach ($branchData as $orgId => $branches) {
            foreach ($branches as [$name, $address, $phone, $email]) {
                Branch::firstOrCreate(
                    [
                        'organization_id' => $orgId,
                        'name' => $name,
                    ],
                    [
                        'address' => $address,
                        'phone' => $phone,
                        'email' => $email,
                        'opening_time' => '08:00:00',
                        'closing_time' => '22:00:00',
                        'total_capacity' => 80,
                        'reservation_fee' => 10.00,
                        'cancellation_fee' => 5.00,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('  ✅ 5 branches per organization (IDs 1–5) seeded successfully!');
        $this->command->info('  🏢 Total Branches: ' . Branch::count());
    }
}
