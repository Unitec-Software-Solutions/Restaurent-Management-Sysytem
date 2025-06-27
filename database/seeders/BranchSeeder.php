<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Organization;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏢 Seeding branches for existing organizations...');
        
        // Get all existing organizations
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No organizations found. Run OrganizationSeeder first.');
            return;
        }
        
        foreach ($organizations as $org) {
            $this->createBranchesForOrganization($org);
        }
        
        $this->command->info('✅ Branches seeded successfully');
    }
    
    private function createBranchesForOrganization(Organization $organization): void
    {
        // Check if organization already has branches (created by OrganizationSeeder)
        if ($organization->branches()->count() > 0) {
            $this->command->info("  ⏭️ Organization '{$organization->name}' already has branches, skipping...");
            return;
        }
        
        // Create additional branches for organizations that don't have any
        $branchData = $this->getBranchDataForOrganization($organization);
        
        foreach ($branchData as $data) {
            try {
                Branch::firstOrCreate([
                    'organization_id' => $organization->id,
                    'name' => $data['name'],
                ], $data);
                
                $this->command->info("    ✅ Created branch: {$data['name']} for {$organization->name}");
            } catch (\Exception $e) {
                $this->command->error("    ❌ Failed to create branch: {$data['name']} - " . $e->getMessage());
            }
        }
    }
    
    private function getBranchDataForOrganization(Organization $organization): array
    {
        // Default branch data that can be customized per organization
        return [
            [
                'name' => $organization->name . ' Main Branch',
                'address' => '123 Main St, Colombo',
                'phone' => $organization->phone ?? '+94 11 123 4567',
                'email' => 'main@' . strtolower(str_replace(' ', '', $organization->name)) . '.com',
                'type' => 'restaurant',
                'activation_key' => \Illuminate\Support\Str::random(40),
                'is_head_office' => true,
                'is_active' => true,
                'opening_time' => '08:00:00',
                'closing_time' => '22:00:00',
                'total_capacity' => 100,
                'reservation_fee' => 500,
                'cancellation_fee' => 200,
            ],
            [
                'name' => $organization->name . ' Secondary Branch',
                'address' => '456 Side St, Kandy',
                'phone' => '+94 81 222 3333',
                'email' => 'secondary@' . strtolower(str_replace(' ', '', $organization->name)) . '.com',
                'type' => 'restaurant',
                'activation_key' => \Illuminate\Support\Str::random(40),
                'is_head_office' => false,
                'is_active' => true,
                'opening_time' => '09:00:00',
                'closing_time' => '21:00:00',
                'total_capacity' => 80,
                'reservation_fee' => 400,
                'cancellation_fee' => 150,
            ]
        ];
    }
}
