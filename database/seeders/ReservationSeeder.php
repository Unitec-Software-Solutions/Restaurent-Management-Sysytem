<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = 1; // Adjust as needed
        for ($i = 1; $i <= 10; $i++) {
            Reservation::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'phone' => '07123456' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'date' => Carbon::now()->addDays($i)->format('Y-m-d'),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'number_of_people' => rand(2, 8),
                'comments' => 'Test reservation ' . $i,
                'reservation_fee' => 10.00 + $i,
                'cancellation_fee' => 5.00 + $i,
                'status' => $i % 3 === 0 ? 'confirmed' : ($i % 2 === 0 ? 'cancelled' : 'pending'),
                'branch_id' => 1,
                'created_at' => Carbon::now()->subDays(rand(0, 10)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
            ]);
        }

        Reservation::create([
            'name' => 'Family Smith',
            'email' => 'smithfamily@example.com',
            'phone' => '0711111111',
            'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'start_time' => '12:00',
            'end_time' => '14:00',
            'number_of_people' => 6,
            'comments' => 'Birthday lunch',
            'reservation_fee' => 25.00,
            'cancellation_fee' => 10.00,
            'status' => 'pending',
            'branch_id' => 1,
        ]);

        Reservation::create([
            'name' => 'Corporate Group',
            'email' => 'corp@example.com',
            'phone' => '0722222222',
            'date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '19:00',
            'end_time' => '22:00',
            'number_of_people' => 12,
            'comments' => 'Company dinner',
            'reservation_fee' => 50.00,
            'cancellation_fee' => 20.00,
            'status' => 'confirmed',
            'branch_id' => 1,
        ]);

        Reservation::create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '0733333333',
            'date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'start_time' => '15:00',
            'end_time' => '16:30',
            'number_of_people' => 2,
            'comments' => 'Afternoon tea',
            'reservation_fee' => 12.00,
            'cancellation_fee' => 6.00,
            'status' => 'pending',
            'branch_id' => 1,
        ]);
        $this->command->info("  Total Reservations in the database : " . Reservation::count());
        $this->command->info("  ✅ Reservations seeded successfully!");
    }
}
