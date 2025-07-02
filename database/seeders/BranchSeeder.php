<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main branch for first organization
        Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN001',
            'organization_id' => 1,
            'address' => '123 Main Street, City, State 12345',
            'phone' => '+1234567890',
            'email' => 'main@testrestaurant.com',
            'manager_name' => 'John Manager',
            'is_active' => true,
            'opening_hours' => json_encode([
                'monday' => ['open' => '09:00', 'close' => '22:00'],
                'tuesday' => ['open' => '09:00', 'close' => '22:00'],
                'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                'thursday' => ['open' => '09:00', 'close' => '22:00'],
                'friday' => ['open' => '09:00', 'close' => '23:00'],
                'saturday' => ['open' => '09:00', 'close' => '23:00'],
                'sunday' => ['open' => '10:00', 'close' => '21:00'],
            ]),
        ]);

        // Second branch for first organization
        Branch::create([
            'name' => 'Downtown Branch',
            'code' => 'DOWN001',
            'organization_id' => 1,
            'address' => '789 Downtown Boulevard, City, State 12347',
            'phone' => '+1234567892',
            'email' => 'downtown@testrestaurant.com',
            'manager_name' => 'Sarah Manager',
            'is_active' => true,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '21:00'],
                'tuesday' => ['open' => '08:00', 'close' => '21:00'],
                'wednesday' => ['open' => '08:00', 'close' => '21:00'],
                'thursday' => ['open' => '08:00', 'close' => '21:00'],
                'friday' => ['open' => '08:00', 'close' => '22:00'],
                'saturday' => ['open' => '08:00', 'close' => '22:00'],
                'sunday' => ['open' => '09:00', 'close' => '20:00'],
            ]),
        ]);

        // Branch for second organization
        Branch::create([
            'name' => 'Demo Main Branch',
            'code' => 'DEMO001',
            'organization_id' => 2,
            'address' => '456 Second Avenue, City, State 12346',
            'phone' => '+1234567893',
            'email' => 'main@demorestaurant.com',
            'manager_name' => 'Mike Demo',
            'is_active' => true,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '21:00'],
                'saturday' => ['open' => '08:00', 'close' => '21:00'],
                'sunday' => ['closed' => true],
            ]),
        ]);
    }
}
