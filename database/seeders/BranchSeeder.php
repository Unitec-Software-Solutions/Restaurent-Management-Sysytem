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
            'opening_time' => '09:00:00',
            'closing_time' => '22:00:00',
            'total_capacity' => 80,
            'reservation_fee' => 5.00,
            'cancellation_fee' => 2.50,
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
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'total_capacity' => 60,
            'reservation_fee' => 5.00,
            'cancellation_fee' => 2.50,
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
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'total_capacity' => 40,
            'reservation_fee' => 3.00,
            'cancellation_fee' => 1.50,
        ]);
    }
}
