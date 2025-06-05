<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Branch;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branch = Branch::first(); // Ensure at least one branch exists

        if (!$branch) {
            $this->command->error('No branches found. Please seed branches first.');
            return;
        }

        Reservation::factory()->count(10)->create([
            'branch_id' => $branch->id,
            'date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'start_time' => Carbon::now()->addDays(1)->format('H:i:s'),
            'end_time' => Carbon::now()->addDays(1)->addHours(2)->format('H:i:s'),
            'status' => 'confirmed',
        ]);

        $this->command->info('Reservations seeded successfully.');
    }
}
