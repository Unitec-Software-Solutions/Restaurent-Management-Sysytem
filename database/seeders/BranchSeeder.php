<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branches = [
            [
                'name' => 'Main Branch',
                'address' => '123 Main St, Cityville, State 12345',
                'phone' => '(555) 123-4567',
                'email' => 'main@restaurant.com',
                'is_active' => true,
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
            ],
            [
                'name' => 'Downtown Location',
                'address' => '456 Center Ave, Downtown, State 12345',
                'phone' => '(555) 987-6543',
                'email' => 'downtown@restaurant.com',
                'is_active' => true,
                'opening_time' => '09:00:00',
                'closing_time' => '23:00:00',
            ],
            [
                'name' => 'Uptown Branch',
                'address' => '789 Highland Blvd, Uptown, State 12345',
                'phone' => '(555) 456-7890',
                'email' => 'uptown@restaurant.com',
                'is_active' => true,
                'opening_time' => '08:30:00',
                'closing_time' => '21:30:00',
            ],
            [
                'name' => 'Beachside Location',
                'address' => '321 Ocean Dr, Beachville, State 12345',
                'phone' => '(555) 234-5678',
                'email' => 'beach@restaurant.com',
                'is_active' => true,
                'opening_time' => '10:00:00',
                'closing_time' => '22:30:00',
            ],
            [
                'name' => 'Airport Branch',
                'address' => 'Terminal 2, International Airport, State 12345',
                'phone' => '(555) 765-4321',
                'email' => 'airport@restaurant.com',
                'is_active' => true,
                'opening_time' => '06:00:00',
                'closing_time' => '23:59:00',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches seeded successfully!');
    }
} 