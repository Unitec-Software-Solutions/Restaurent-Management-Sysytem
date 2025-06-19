<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Organization;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $olu = Organization::where('name', 'Olu cafe and restaurent')->first();
        $urban = Organization::where('name', 'Urban Cafe')->first();

        // Olu: Multiple branches, all active
        Branch::firstOrCreate([
            'organization_id' => $olu->id,
            'name' => 'Main Branch',
        ], [
            'address' => '123 Main St, Colombo',
            'phone' => '+94 11 123 4567',
            'is_active' => true,
            'opening_time' => '08:00:00',
            'closing_time' => '22:00:00',
            'total_capacity' => 100,
            'reservation_fee' => 500,
            'cancellation_fee' => 200, // <-- Add this line
        ]);
        Branch::firstOrCreate([
            'organization_id' => $olu->id,
            'name' => 'Branch 01',
        ], [
            'address' => '456 Side St, Colombo',
            'phone' => '+94 11 222 3333',
            'is_active' => true,
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'total_capacity' => 80,
            'reservation_fee' => 400,
            'cancellation_fee' => 150,
        ]);

        // Urban: Single branch, inactive
        Branch::firstOrCreate([
            'organization_id' => $urban->id,
            'name' => 'Urban Main',
        ], [
            'address' => '456 High St, Kandy',
            'phone' => '+94 11 987 6543',
            'is_active' => false,
            'opening_time' => '10:00:00',
            'closing_time' => '20:00:00',
            'total_capacity' => 60,
            'reservation_fee' => 350,
            'cancellation_fee' => 100,
        ]);
    }
}
