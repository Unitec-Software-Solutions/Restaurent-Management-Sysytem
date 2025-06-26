<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Organization;

class OptimizedBranchSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing branches
        Branch::truncate();
        
        $organizations = Organization::all();
        
        foreach ($organizations as $organization) {
            $this->createBranchesForOrganization($organization);
        }

        $this->command->info("  Total Branches: " . Branch::count());
        $this->command->info("  ✅ Branches seeded successfully - 2 per organization.");
    }

    private function createBranchesForOrganization(Organization $organization): void
    {
        $branchData = $this->getBranchDataForOrganization($organization);
        
        foreach ($branchData as $data) {
            $branch = Branch::create(array_merge($data, [
                'organization_id' => $organization->id,
            ]));
            
            $this->command->info("    ✅ Created branch: {$branch->name} for {$organization->name}");
        }
    }

    private function getBranchDataForOrganization(Organization $organization): array
    {
        switch ($organization->name) {
            case 'Spice Garden Restaurant':
                return [
                    [
                        'name' => 'Spice Garden Main',
                        'address' => '123 Galle Road, Colombo 03',
                        'phone' => '+94 11 234 5678',
                        'is_active' => true,
                        'opening_time' => '10:00:00',
                        'closing_time' => '23:00:00',
                        'total_capacity' => 120,
                        'reservation_fee' => 1000,
                        'cancellation_fee' => 500,
                    ],
                    [
                        'name' => 'Spice Garden Dehiwala',
                        'address' => '456 Galle Road, Dehiwala',
                        'phone' => '+94 11 234 5679',
                        'is_active' => true,
                        'opening_time' => '11:00:00',
                        'closing_time' => '22:30:00',
                        'total_capacity' => 80,
                        'reservation_fee' => 750,
                        'cancellation_fee' => 350,
                    ],
                ];

            case 'Ocean View Cafe':
                return [
                    [
                        'name' => 'Ocean View Main',
                        'address' => '456 Marine Drive, Galle',
                        'phone' => '+94 31 567 8901',
                        'is_active' => true,
                        'opening_time' => '07:00:00',
                        'closing_time' => '22:00:00',
                        'total_capacity' => 60,
                        'reservation_fee' => 500,
                        'cancellation_fee' => 250,
                    ],
                    [
                        'name' => 'Ocean View Unawatuna',
                        'address' => '789 Beach Road, Unawatuna',
                        'phone' => '+94 31 567 8902',
                        'is_active' => true,
                        'opening_time' => '08:00:00',
                        'closing_time' => '21:00:00',
                        'total_capacity' => 40,
                        'reservation_fee' => 300,
                        'cancellation_fee' => 150,
                    ],
                ];

            case 'Mountain Peak Restaurant':
                return [
                    [
                        'name' => 'Mountain Peak Kandy',
                        'address' => '789 Hill Street, Kandy',
                        'phone' => '+94 81 345 6789',
                        'is_active' => true,
                        'opening_time' => '09:00:00',
                        'closing_time' => '22:00:00',
                        'total_capacity' => 100,
                        'reservation_fee' => 800,
                        'cancellation_fee' => 400,
                    ],
                    [
                        'name' => 'Mountain Peak Nuwara Eliya',
                        'address' => '321 Lake Road, Nuwara Eliya',
                        'phone' => '+94 52 345 6789',
                        'is_active' => true,
                        'opening_time' => '08:00:00',
                        'closing_time' => '21:30:00',
                        'total_capacity' => 70,
                        'reservation_fee' => 600,
                        'cancellation_fee' => 300,
                    ],
                ];

            default:
                return [
                    [
                        'name' => $organization->name . ' Main Branch',
                        'address' => $organization->address,
                        'phone' => $organization->phone,
                        'is_active' => true,
                        'opening_time' => '09:00:00',
                        'closing_time' => '22:00:00',
                        'total_capacity' => 60,
                        'reservation_fee' => 500,
                        'cancellation_fee' => 250,
                    ],
                    [
                        'name' => $organization->name . ' Branch 2',
                        'address' => 'Secondary Location',
                        'phone' => $organization->phone,
                        'is_active' => true,
                        'opening_time' => '10:00:00',
                        'closing_time' => '21:00:00',
                        'total_capacity' => 40,
                        'reservation_fee' => 400,
                        'cancellation_fee' => 200,
                    ],
                ];
        }
    }
}
