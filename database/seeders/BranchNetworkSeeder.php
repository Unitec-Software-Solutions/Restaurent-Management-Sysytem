<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;
use App\Services\BranchAutomationService;
use App\Services\MenuScheduleService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BranchNetworkSeeder extends Seeder
{
    protected $branchAutomationService;
    protected $menuScheduleService;

    public function __construct(BranchAutomationService $branchAutomationService)
    {
        $this->branchAutomationService = $branchAutomationService;
        $this->menuScheduleService = app(MenuScheduleService::class);
    }

    /**
     * Generate branch network with admins and kitchen stations
     */
    public function run(): void
    {
        $this->command->info('🏪 Generating Branch Network...');
        
        $organizations = Organization::with('subscriptionPlan')->get();
        
        foreach ($organizations as $organization) {
            $this->createBranchNetwork($organization);
        }
        
        $this->command->info('✅ Branch Network generated successfully');
    }

    private function createBranchNetwork(Organization $organization): void
    {
        $this->command->info("  🏢 Creating branches for: {$organization->name}");
        
        // Skip if head office already exists (created by SuperAdminOrganizationSeeder)
        $maxBranches = $organization->subscriptionPlan->max_branches ?? 1;
        $existingBranches = $organization->branches()->count();
        
        // Calculate how many additional branches to create (excluding head office)
        $branchesToCreate = min($maxBranches - $existingBranches, $this->getBranchCountForBusinessType($organization->business_type));
        
        if ($branchesToCreate <= 0) {
            $this->command->info("    ℹ️ Organization already has maximum branches allowed");
            return;
        }

        // Create additional branches based on business type
        $this->createBranchesForBusinessType($organization, $branchesToCreate);
    }

    private function getBranchCountForBusinessType(string $businessType): int
    {
        return match($businessType) {
            'restaurant_chain', 'international_chain' => 4,
            'fast_food', 'cafe' => 2,
            'family_restaurant' => 1,
            default => 2
        };
    }

    private function createBranchesForBusinessType(Organization $organization, int $branchCount): void
    {
        $locations = $this->getLocationTemplatesForBusinessType($organization->business_type);
        
        for ($i = 0; $i < $branchCount; $i++) {
            $location = $locations[$i % count($locations)];
            $this->createBranchWithAutomation($organization, $location, $i + 1);
        }
    }

    private function getLocationTemplatesForBusinessType(string $businessType): array
    {
        $locationSets = [
            'restaurant_chain' => [
                ['name' => 'Colombo Fort', 'address' => '123 Fort Road, Colombo 01', 'type' => 'restaurant', 'capacity' => 120],
                ['name' => 'Kandy City', 'address' => '456 Peradeniya Road, Kandy', 'type' => 'restaurant', 'capacity' => 100],
                ['name' => 'Galle Branch', 'address' => '789 Wakwella Road, Galle', 'type' => 'restaurant', 'capacity' => 80],
                ['name' => 'Negombo Outlet', 'address' => '321 Kurana Road, Negombo', 'type' => 'restaurant', 'capacity' => 90]
            ],
            'international_chain' => [
                ['name' => 'Dubai Mall', 'address' => 'Dubai Mall, UAE', 'type' => 'restaurant', 'capacity' => 150],
                ['name' => 'Singapore Marina', 'address' => 'Marina Bay, Singapore', 'type' => 'restaurant', 'capacity' => 140],
                ['name' => 'London Soho', 'address' => 'Soho District, London', 'type' => 'restaurant', 'capacity' => 130],
                ['name' => 'Sydney CBD', 'address' => 'Central Business District, Sydney', 'type' => 'restaurant', 'capacity' => 120]
            ],
            'fast_food' => [
                ['name' => 'Food Court', 'address' => '123 Mall Complex, Colombo 03', 'type' => 'fast_food', 'capacity' => 60],
                ['name' => 'Drive Thru', 'address' => '456 Highway Junction, Kelaniya', 'type' => 'fast_food', 'capacity' => 40]
            ],
            'cafe' => [
                ['name' => 'Coffee Shop', 'address' => '789 Library Road, Dehiwala', 'type' => 'cafe', 'capacity' => 30],
                ['name' => 'Beach Cafe', 'address' => '321 Beach Road, Mount Lavinia', 'type' => 'cafe', 'capacity' => 25]
            ],
            'family_restaurant' => [
                ['name' => 'Family Branch', 'address' => '654 Residential Area, Maharagama', 'type' => 'restaurant', 'capacity' => 50]
            ]
        ];

        return $locationSets[$businessType] ?? $locationSets['restaurant_chain'];
    }

    private function createBranchWithAutomation(Organization $organization, array $location, int $branchNumber): void
    {
        // Create branch
        $branch = Branch::create([
            'organization_id' => $organization->id,
            'name' => $organization->trading_name . ' ' . $location['name'],
            'slug' => Str::slug($organization->trading_name . ' ' . $location['name']),
            'type' => $location['type'],
            'address' => $location['address'],
            'phone' => $this->generatePhoneNumber(),
            'email' => $this->generateBranchEmail($organization, $location['name']),
            'opening_time' => $this->getOpeningTimeForType($location['type']),
            'closing_time' => $this->getClosingTimeForType($location['type']),
            'total_capacity' => $location['capacity'],
            'max_capacity' => intval($location['capacity'] * 0.8), // 80% for reservations
            'reservation_fee' => $this->getReservationFeeForType($location['type']),
            'cancellation_fee' => $this->getCancellationFeeForType($location['type']),
            'is_active' => true,
            'is_head_office' => false,
            'activation_key' => null, // Already activated
            'activated_at' => now()->subDays(rand(1, 15)),
            'opened_at' => now()->subDays(rand(15, 60)),
            'contact_person' => $this->generateManagerName(),
            'contact_person_designation' => 'Branch Manager',
            'contact_person_phone' => $this->generatePhoneNumber(),
            'manager_name' => $this->generateManagerName(),
            'manager_phone' => $this->generatePhoneNumber(),
            'operating_hours' => json_encode($this->getOperatingHours($location['type'])),
            'features' => json_encode($this->getBranchFeatures($location['type']))
        ]);

        $this->command->info("    🏪 Created branch: {$branch->name}");

        try {
            // Use automation service to create branch admins and kitchen stations
            $this->branchAutomationService->setupNewBranch($branch);
            
            // Create 3 additional branch admins for each branch
            $this->createAdditionalBranchAdmins($branch, 2);
            
            // Create default menus with scheduled activation times
            $this->createDefaultMenusWithSchedules($branch);
            
            // Display created resources
            $admins = Admin::where('branch_id', $branch->id)->get();
            $branch->refresh(); // Refresh the branch to get updated relationships
            $kitchenStations = $branch->kitchenStations ?? collect();
            
            $this->command->info("      → {$admins->count()} Admins created");
            $this->command->info("      → {$kitchenStations->count()} Kitchen Stations created");
            
            foreach ($admins as $admin) {
                $this->command->info("        👤 {$admin->name} ({$admin->email})");
            }
            
            foreach ($kitchenStations as $station) {
                $this->command->info("        🍽️ {$station->name} ({$station->code})");
            }
            
        } catch (\Exception $e) {
            $this->command->error("      ❌ Failed to setup automation for {$branch->name}: " . $e->getMessage());
        }
    }

    private function createAdditionalBranchAdmins(Branch $branch, int $count): void
    {
        $roles = ['Assistant Manager', 'Shift Supervisor', 'Floor Manager'];
        
        for ($i = 0; $i < $count; $i++) {
            $role = $roles[$i % count($roles)];
            $name = $this->generateStaffName($role);
            
            $admin = Admin::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'name' => $name,
                'email' => $this->generateStaffEmail($branch, $role, $i + 1),
                'password' => Hash::make('staff123'),
                'phone' => $this->generatePhoneNumber(),
                'job_title' => $role,
                'is_super_admin' => false,
                'is_active' => true,
                'status' => 'active',
                'hired_at' => now()->subDays(rand(5, 30)),
                'email_verified_at' => now()
            ]);

            // Assign appropriate role
            $branchRole = \Spatie\Permission\Models\Role::where('name', 'Branch Administrator')->first();
            if ($branchRole) {
                $admin->assignRole($branchRole);
            }
        }
    }

    private function createDefaultMenusWithSchedules(Branch $branch): void
    {
        try {
            // TODO: Fix MenuScheduleService array to string conversion
            // $defaultMenus = $this->menuScheduleService->createDefaultMenusForBranch(
            //     $branch->id, 
            //     $branch->organization_id
            // );
            
            // $menuCount = is_array($defaultMenus) ? count($defaultMenus) : ($defaultMenus ?? 0);
            // $this->command->info("      → {$menuCount} Default menus created with schedules");
            
            $this->command->info("      → Menu creation temporarily disabled");
            
        } catch (\Exception $e) {
            $this->command->warn("      ⚠️ Could not create default menus: " . $e->getMessage());
        }
    }

    // Helper methods for generating data
    private function generatePhoneNumber(): string
    {
        return '+94 ' . rand(11, 91) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    }

    private function generateBranchEmail(Organization $organization, string $locationName): string
    {
        $domain = str_replace(['http://', 'https://'], '', 
                  parse_url($organization->email, PHP_URL_HOST) ?? 
                  str_replace(' ', '', strtolower($organization->name)) . '.com');
        $location = strtolower(str_replace(' ', '', $locationName));
        return $location . '@' . $domain;
    }

    private function generateManagerName(): string
    {
        $firstNames = ['Saman', 'Priya', 'Kamal', 'Nimali', 'Rajesh', 'Dilani', 'Chamara', 'Sanduni'];
        $lastNames = ['Silva', 'Perera', 'Fernando', 'Jayasinghe', 'Wickramasinghe', 'Rathnayake'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function generateStaffName(string $role): string
    {
        return $this->generateManagerName() . ' (' . $role . ')';
    }

    private function generateStaffEmail(Branch $branch, string $role, int $number): string
    {
        $roleSlug = strtolower(str_replace(' ', '', $role));
        $branchSlug = strtolower(str_replace(' ', '-', $branch->name));
        $domain = 'restaurant.local';
        
        return $roleSlug . $number . '.' . $branchSlug . '@' . $domain;
    }

    private function getOpeningTimeForType(string $type): string
    {
        return match($type) {
            'cafe' => '07:00:00',
            'fast_food' => '08:00:00',
            default => '10:00:00'
        };
    }

    private function getClosingTimeForType(string $type): string
    {
        return match($type) {
            'cafe' => '20:00:00',
            'fast_food' => '23:00:00',
            default => '22:00:00'
        };
    }

    private function getReservationFeeForType(string $type): float
    {
        return match($type) {
            'cafe' => 0.00,
            'fast_food' => 0.00,
            default => 500.00
        };
    }

    private function getCancellationFeeForType(string $type): float
    {
        return match($type) {
            'cafe' => 0.00,
            'fast_food' => 0.00,
            default => 250.00
        };
    }

    private function getOperatingHours(string $type): array
    {
        $baseHours = [
            'monday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'tuesday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'wednesday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'thursday' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            'friday' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
            'saturday' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
            'sunday' => ['open' => '11:00', 'close' => '21:00', 'closed' => false]
        ];

        if ($type === 'cafe') {
            foreach ($baseHours as $day => &$hours) {
                $hours['open'] = '07:00';
                $hours['close'] = '20:00';
            }
        } elseif ($type === 'fast_food') {
            foreach ($baseHours as $day => &$hours) {
                $hours['open'] = '08:00';
                $hours['close'] = '23:00';
            }
        }

        return $baseHours;
    }

    private function getBranchFeatures(string $type): array
    {
        $baseFeatures = ['pos_system', 'kitchen_display', 'inventory_tracking'];
        
        return match($type) {
            'cafe' => array_merge($baseFeatures, ['coffee_machine', 'pastry_display']),
            'fast_food' => array_merge($baseFeatures, ['drive_thru', 'quick_service']),
            default => array_merge($baseFeatures, ['table_reservations', 'fine_dining'])
        };
    }
}
