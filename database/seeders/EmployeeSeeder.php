<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('employees')->insert([
            [
                'emp_id' => 'EMP001',
                'name' => 'John Doe',
                'email' => 'john.doe@restaurant.com',
                'phone' => '5550101001',
                'role' => 'manager',
                'branch_id' => 1,
                'organization_id' => 1,
                'is_active' => true,
                'joined_date' => $now->subYears(2),
                'address' => '123 Main St, Cityville',
                'emergency_contact' => '5550101002 (Mary Doe)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'emp_id' => 'EMP002',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@restaurant.com',
                'phone' => '5550202002',
                'role' => 'chef',
                'branch_id' => 1,
                'organization_id' => 1,
                'is_active' => true,
                'joined_date' => $now->subYear(),
                'address' => '456 Oak Ave, Townsville',
                'emergency_contact' => '5550202003 (Robert Smith)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'emp_id' => 'EMP003',
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@restaurant.com',
                'phone' => '5550303003',
                'role' => 'steward',
                'branch_id' => 1,
                'organization_id' => 1,
                'is_active' => true,
                'joined_date' => $now->subMonths(6),
                'address' => '789 Pine Rd, Villageton',
                'emergency_contact' => '5550303004 (David Johnson)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'emp_id' => 'EMP004',
                'name' => 'Bob Williams',
                'email' => 'bob.williams@restaurant.com',
                'phone' => '5550404004',
                'role' => 'steward',
                'branch_id' => 1,
                'organization_id' => 1,
                'is_active' => true,
                'joined_date' => $now->subMonths(3),
                'address' => '321 Elm Blvd, Hamletville',
                'emergency_contact' => '5550404005 (Sarah Williams)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'emp_id' => 'EMP005',
                'name' => 'Emma Brown',
                'email' => 'emma.brown@restaurant.com',
                'phone' => '5550505005',
                'role' => 'cashier',
                'branch_id' => 1,
                'organization_id' => 1,
                'is_active' => true,
                'joined_date' => $now->subMonth(),
                'address' => '654 Maple Ln, Boroughburg',
                'emergency_contact' => '5550505006 (Michael Brown)',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }
}