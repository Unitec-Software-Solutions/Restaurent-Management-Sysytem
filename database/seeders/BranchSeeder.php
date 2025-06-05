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
                ['Colombo HQ', 'No. 25, Galle Road, Colombo 03, Sri Lanka', '+94 11 234 5678', 'colombo@mainrest.lk', 'MAIN-CMB-HQ'],
                ['Kandy Outlet', 'No. 12, Peradeniya Road, Kandy, Sri Lanka', '+94 81 223 4567', 'kandy@mainrest.lk', 'MAIN-KDY-01'],
                ['Galle Branch', '88 Lighthouse Street, Galle Fort', '+94 91 223 1122', 'galle@mainrest.lk', 'MAIN-GLE-01'],
                ['Negombo Branch', '45 Beachside Rd, Negombo', '+94 31 567 1234', 'negombo@mainrest.lk', 'MAIN-NGB-01'],
                ['Kurunegala Branch', 'Mall Complex, Kurunegala', '+94 37 224 9988', 'kurunegala@mainrest.lk', 'MAIN-KUR-01'],
            ],
            2 => [
                ['Oceanfront Colombo', 'Marine Drive, Colombo 04', '+94 77 456 7890', 'colombo@oceanbreeze.lk', 'OCB-CMB-01'],
                ['Galle Sea View', 'Beach Road, Galle', '+94 77 567 4321', 'galle@oceanbreeze.lk', 'OCB-GLE-01'],
                ['Trinco Bay Branch', 'Nilaveli Beach Rd, Trincomalee', '+94 77 987 6543', 'trinco@oceanbreeze.lk', 'OCB-TRN-01'],
                ['Negombo Pier', 'Pier 3, Negombo Harbour', '+94 77 222 3333', 'pier@oceanbreeze.lk', 'OCB-NGB-02'],
                ['Batticaloa Breeze', 'Passikudah Beach Rd', '+94 77 333 4444', 'batti@oceanbreeze.lk', 'OCB-BTC-01'],
            ],
            3 => [
                ['Urban Cafe HQ', 'Downtown Hub, Colombo 01', '+94 76 123 4567', 'hq@urbancafe.lk', 'URB-CMB-HQ'],
                ['Colombo Tech Cafe', 'Trace Expert City, Maradana', '+94 76 234 5678', 'tech@urbancafe.lk', 'URB-CMB-TEC'],
                ['Nugegoda Cafe', 'High-Level Rd, Nugegoda', '+94 76 345 6789', 'nugegoda@urbancafe.lk', 'URB-NGD-01'],
                ['Kandy Lakeview', 'Near Lake Round, Kandy', '+94 76 456 7890', 'kandy@urbancafe.lk', 'URB-KDY-02'],
                ['Gampaha Cafe', 'Main Street, Gampaha', '+94 76 567 8901', 'gampaha@urbancafe.lk', 'URB-GMP-01'],
            ],
            4 => [
                ['Pizza Palace HQ', 'Main Street, Dehiwala', '+94 75 111 2222', 'hq@pizzapalace.lk', 'PP-DHW-HQ'],
                ['Mt. Lavinia Branch', 'Beach Rd, Mt. Lavinia', '+94 75 222 3333', 'mtl@pizzapalace.lk', 'PP-MTL-01'],
                ['Colombo South Branch', 'Havelock Town, Colombo 05', '+94 75 333 4444', 'south@pizzapalace.lk', 'PP-CMB-05'],
                ['Wattala Branch', 'Negombo Rd, Wattala', '+94 75 444 5555', 'wattala@pizzapalace.lk', 'PP-WTL-01'],
                ['Maharagama Branch', 'High-Level Rd, Maharagama', '+94 75 555 6666', 'maharagama@pizzapalace.lk', 'PP-MHR-01'],
            ],
            5 => [
                ['Bamboo HQ', 'Lotus Rd, Colombo 01', '+94 74 111 2233', 'hq@bamboogarden.lk', 'BMB-CMB-HQ'],
                ['Colombo East', 'Rajagiriya Junction', '+94 74 222 3344', 'east@bamboogarden.lk', 'BMB-CMB-EA'],
                ['Kandy Central', 'Temple Rd, Kandy', '+94 74 333 4455', 'kandy@bamboogarden.lk', 'BMB-KDY-01'],
                ['Jaffna Branch', 'Hospital Rd, Jaffna', '+94 74 444 5566', 'jaffna@bamboogarden.lk', 'BMB-JFN-01'],
                ['Matara Branch', 'Beach Road, Matara', '+94 74 555 6677', 'matara@bamboogarden.lk', 'BMB-MTR-01'],
            ],
        ];

        foreach ($branchData as $orgId => $branches) {
            foreach ($branches as [$name, $address, $phone, $email, $code]) {
                Branch::firstOrCreate(
                    [
                        'organization_id' => $orgId,
                        'name' => $name,
                    ],
                    [
                        'code' => $code,
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

        $this->command->info('  ✅ 5 branches per organization (IDs 1–5) seeded successfully with branch codes!');
        $this->command->info('  🏢 Total Branches: ' . Branch::count());
    }
}
