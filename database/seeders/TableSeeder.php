<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Table;

class TableSeeder extends Seeder
{
    public function run()
    {
        // Define number of tables for each branch (customize as needed)
        $tablesPerBranch = [
            1 => 15, // Main Branch
            2 => 10, // Kandy Branch
            3 => 8,  // Galle Branch
        ];

        foreach ($tablesPerBranch as $branchId => $tableCount) {
            for ($i = 1; $i <= $tableCount; $i++) {
                // Check if table already exists
                $existingTable = Table::where('branch_id', $branchId)
                    ->where('number', $i)
                    ->first();

                if (!$existingTable) {
                    Table::create([
                        'branch_id' => $branchId,
                        'number' => $i,
                        'capacity' => rand(2, 8),
                        'status' => 'available',
                        'location' => null,
                        'description' => null,
                    ]);
                }
            }
        }

        Table::updateOrCreate(
            ['branch_id' => 1, 'number' => 1],
            [
                'capacity' => 3,
                'status' => 'available',
                'location' => 'Main Hall',
                'description' => 'Window seat'
            ]
        );

        $this->command->info("  ✅ Tables seeded successfully!");
    }
}
