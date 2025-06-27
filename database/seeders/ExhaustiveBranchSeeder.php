<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\KitchenStation;
use App\Models\Table;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Exhaustive Branch Seeder
 * 
 * Creates various branch scenarios:
 * - Head office branches
 * - Regular operational branches
 * - Temporary/seasonal branches
 * - Branches with custom kitchen stations
 * - Multi-location franchises
 * - International branches
 */
class ExhaustiveBranchSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  🏪 Creating branch creation cases...');

        $organizations = Organization::with('subscriptionPlan')->get();

        foreach ($organizations as $org) {
            $this->createBranchesForOrganization($org);
        }

        $this->command->info("  ✅ Created branches for all organization types with realistic scenarios");
    }

    private function createBranchesForOrganization(Organization $org): void
    {
        $maxBranches = $org->subscriptionPlan->max_branches ?? 1;
        $branchType = $this->determineBranchStrategy($org);

        switch ($branchType) {
            case 'single_branch':
                $this->createSingleBranch($org);
                break;
            
            case 'multi_branch_local':
                $this->createMultiBranchLocal($org, min($maxBranches, 5));
                break;
                
            case 'franchise_chain':
                $this->createFranchiseChain($org, min($maxBranches, 20));
                break;
                
            case 'seasonal_branches':
                $this->createSeasonalBranches($org, min($maxBranches, 3));
                break;
                
            case 'food_truck':
                $this->createMobileBranch($org);
                break;
                
            case 'international':
                $this->createInternationalBranches($org, min($maxBranches, 10));
                break;
                
            default:
                $this->createSingleBranch($org);
                break;
        }
    }

    private function determineBranchStrategy(Organization $org): string
    {
        switch ($org->business_type) {
            case 'food_truck':
                return 'food_truck';
            case 'restaurant_chain':
            case 'international_chain':
                return 'franchise_chain';
            case 'resort_restaurant':
                return 'seasonal_branches';
            case 'food_court':
                return 'multi_branch_local';
            default:
                return $org->subscriptionPlan->max_branches > 2 ? 'multi_branch_local' : 'single_branch';
        }
    }

    private function createSingleBranch(Organization $org): void
    {
        $operatingHours = $this->getOperatingHours($org->business_type);
        
        $branch = Branch::create([
            'organization_id' => $org->id,
            'name' => $org->trading_name . ' Main',
            'type' => $this->getBranchType($org->business_type),
            'address' => $org->address,
            'city' => $this->extractCity($org->address),
            'state' => $this->extractState($org->address),
            'zip' => $this->generateZipCode(),
            'phone' => $org->phone,
            'email' => $org->email,
            'opening_time' => $this->getEarliestOpeningTime($operatingHours),
            'closing_time' => $this->getLatestClosingTime($operatingHours),
            'total_capacity' => $this->getCapacityForType($org->business_type),
            'reservation_fee' => $this->getReservationFee($org->business_type),
            'cancellation_fee' => $this->getCancellationFee($org->business_type),
            'activation_key' => $this->generateActivationKey(),
            'is_active' => $org->is_active,
            'is_head_office' => true,
            'opened_at' => $org->activated_at ?? Carbon::now()->subDays(30),
            'manager_name' => $org->contact_person,
            'manager_phone' => $org->contact_person_phone,
            'operating_hours' => json_encode($operatingHours),
            'features' => json_encode($this->getBranchFeatures($org->business_type)),
        ]);

        $this->createKitchenStationsForBranch($branch, 'standard');
        $this->createTablesForBranch($branch);
        
        $this->command->info("      ✓ Created single branch: {$branch->name}");
    }

    private function createMultiBranchLocal(Organization $org, int $branchCount): void
    {
        $locations = $this->getLocalLocations();
        
        for ($i = 0; $i < min($branchCount, count($locations)); $i++) {
            $location = $locations[$i];
            $isHeadOffice = $i === 0;
            $operatingHours = $this->getOperatingHours($org->business_type);
            
            $branch = Branch::create([
                'organization_id' => $org->id,
                'name' => $org->trading_name . ' ' . $location['name'],
                'type' => $this->getBranchType($org->business_type),
                'address' => $location['address'],
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $location['zip'],
                'phone' => $this->generatePhoneNumber(),
                'email' => $isHeadOffice ? $org->email : strtolower(str_replace(' ', '', $location['name'])) . '@' . $this->extractDomain($org->email),
                'opening_time' => $this->getEarliestOpeningTime($operatingHours),
                'closing_time' => $this->getLatestClosingTime($operatingHours),
                'total_capacity' => $this->getCapacityForType($org->business_type),
                'reservation_fee' => $this->getReservationFee($org->business_type),
                'cancellation_fee' => $this->getCancellationFee($org->business_type),
                'activation_key' => $this->generateActivationKey(),
                'is_active' => true,
                'is_head_office' => $isHeadOffice,
                'opened_at' => Carbon::now()->subDays(rand(10, 180)),
                'manager_name' => $this->generateManagerName(),
                'manager_phone' => $this->generatePhoneNumber(),
                'operating_hours' => json_encode($operatingHours),
                'features' => json_encode($this->getBranchFeatures($org->business_type)),
            ]);

            $this->createKitchenStationsForBranch($branch, $isHeadOffice ? 'premium' : 'standard');
            $this->createTablesForBranch($branch);
            
            $this->command->info("      ✓ Created branch: {$branch->name}");
        }
    }

    private function createFranchiseChain(Organization $org, int $branchCount): void
    {
        $franchiseLocations = $this->getFranchiseLocations();
        
        for ($i = 0; $i < min($branchCount, count($franchiseLocations)); $i++) {
            $location = $franchiseLocations[$i];
            $isHeadOffice = $i === 0;
            $isFlagship = in_array($i, [0, 1, 2]); // First 3 are flagship stores
            $operatingHours = $this->getOperatingHours($org->business_type);
            
            $branch = Branch::create([
                'organization_id' => $org->id,
                'name' => $org->trading_name . ' ' . $location['name'],
                'type' => $isFlagship ? 'flagship' : 'franchise',
                'address' => $location['address'],
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $location['zip'],
                'phone' => $this->generatePhoneNumber(),
                'email' => strtolower(str_replace(' ', '', $location['name'])) . '@' . $this->extractDomain($org->email),
                'opening_time' => $this->getEarliestOpeningTime($operatingHours),
                'closing_time' => $this->getLatestClosingTime($operatingHours),
                'total_capacity' => $isFlagship ? 150 : $this->getCapacityForType($org->business_type),
                'reservation_fee' => $this->getReservationFee($org->business_type),
                'cancellation_fee' => $this->getCancellationFee($org->business_type),
                'activation_key' => $this->generateActivationKey(),
                'is_active' => true,
                'is_head_office' => $isHeadOffice,
                'opened_at' => Carbon::now()->subDays(rand(30, 365)),
                'manager_name' => $this->generateManagerName(),
                'manager_phone' => $this->generatePhoneNumber(),
                'operating_hours' => json_encode($operatingHours),
                'features' => $isFlagship ? 
                    json_encode(array_merge($this->getBranchFeatures($org->business_type), ['vip_section', 'private_dining', 'event_hall'])) :
                    json_encode($this->getBranchFeatures($org->business_type)),
                'franchise_id' => $isHeadOffice ? null : 'FR' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ]);

            $this->createKitchenStationsForBranch($branch, $isFlagship ? 'premium' : 'standard');
            $this->createTablesForBranch($branch);
            
            $this->command->info("      ✓ Created franchise branch: {$branch->name}");
        }
    }

    private function createSeasonalBranches(Organization $org, int $branchCount): void
    {
        $seasonalLocations = $this->getSeasonalLocations();
        
        for ($i = 0; $i < min($branchCount, count($seasonalLocations)); $i++) {
            $location = $seasonalLocations[$i];
            $isMainLocation = $i === 0;
            $operatingHours = $location['operating_hours'];
            
            $branch = Branch::create([
                'organization_id' => $org->id,
                'name' => $org->trading_name . ' ' . $location['name'],
                'type' => 'seasonal',
                'address' => $location['address'],
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $location['zip'],
                'phone' => $this->generatePhoneNumber(),
                'email' => strtolower(str_replace(' ', '', $location['name'])) . '@' . $this->extractDomain($org->email),
                'opening_time' => $this->getEarliestOpeningTime($operatingHours),
                'closing_time' => $this->getLatestClosingTime($operatingHours),
                'total_capacity' => $location['capacity'],
                'reservation_fee' => $this->getReservationFee($org->business_type),
                'cancellation_fee' => $this->getCancellationFee($org->business_type),
                'activation_key' => $this->generateActivationKey(),
                'is_active' => $location['active_season'],
                'is_head_office' => $isMainLocation,
                'opened_at' => Carbon::now()->subDays(rand(60, 120)),
                'closed_at' => $location['active_season'] ? null : Carbon::now()->addDays(rand(30, 90)),
                'seasonal_start' => $location['season_start'],
                'seasonal_end' => $location['season_end'],
                'manager_name' => $this->generateManagerName(),
                'manager_phone' => $this->generatePhoneNumber(),
                'operating_hours' => json_encode($operatingHours),
                'features' => json_encode($location['features']),
            ]);

            $this->createKitchenStationsForBranch($branch, 'compact');
            $this->createTablesForBranch($branch);
            
            $this->command->info("      ✓ Created seasonal branch: {$branch->name} (Active: " . ($location['active_season'] ? 'Yes' : 'No') . ")");
        }
    }

    private function createMobileBranch(Organization $org): void
    {
        $operatingHours = [
            'monday' => ['10:00', '22:00'],
            'tuesday' => ['10:00', '22:00'],
            'wednesday' => ['10:00', '22:00'],
            'thursday' => ['10:00', '22:00'],
            'friday' => ['10:00', '23:00'],
            'saturday' => ['09:00', '23:00'],
            'sunday' => ['11:00', '21:00'],
        ];
        
        $branch = Branch::create([
            'organization_id' => $org->id,
            'name' => $org->trading_name . ' Mobile Unit',
            'type' => 'mobile',
            'address' => 'Mobile Operations - Various Locations',
            'city' => 'Colombo',
            'state' => 'Western Province',
            'zip' => '00000',
            'phone' => $org->phone,
            'email' => $org->email,
            'opening_time' => $this->getEarliestOpeningTime($operatingHours),
            'closing_time' => $this->getLatestClosingTime($operatingHours),
            'total_capacity' => 20,
            'reservation_fee' => $this->getReservationFee($org->business_type),
            'cancellation_fee' => $this->getCancellationFee($org->business_type),
            'activation_key' => $this->generateActivationKey(),
            'is_active' => true,
            'is_head_office' => true,
            'opened_at' => $org->activated_at ?? Carbon::now()->subDays(20),
            'manager_name' => $org->contact_person,
            'manager_phone' => $org->contact_person_phone,
            'operating_hours' => json_encode($operatingHours),
            'features' => json_encode(['mobile_pos', 'gps_tracking', 'route_planning', 'quick_service']),
        ]);

        $this->createKitchenStationsForBranch($branch, 'mobile');
        // No tables for mobile units
        
        $this->command->info("      ✓ Created mobile branch: {$branch->name}");
    }

    private function createInternationalBranches(Organization $org, int $branchCount): void
    {
        $internationalLocations = $this->getInternationalLocations();
        
        for ($i = 0; $i < min($branchCount, count($internationalLocations)); $i++) {
            $location = $internationalLocations[$i];
            $isRegionalHQ = $i === 0;
            $operatingHours = $location['operating_hours'];
            
            $branch = Branch::create([
                'organization_id' => $org->id,
                'name' => $org->trading_name . ' ' . $location['name'],
                'type' => $isRegionalHQ ? 'regional_hq' : 'international',
                'address' => $location['address'],
                'city' => $location['city'],
                'state' => $location['state'],
                'zip' => $location['zip'],
                'country' => $location['country'],
                'phone' => $location['phone'],
                'email' => strtolower(str_replace(' ', '', $location['name'])) . '@' . $this->extractDomain($org->email),
                'opening_time' => $this->getEarliestOpeningTime($operatingHours),
                'closing_time' => $this->getLatestClosingTime($operatingHours),
                'total_capacity' => $location['capacity'],
                'reservation_fee' => $this->getReservationFee($org->business_type),
                'cancellation_fee' => $this->getCancellationFee($org->business_type),
                'activation_key' => $this->generateActivationKey(),
                'is_active' => true,
                'is_head_office' => $isRegionalHQ,
                'opened_at' => Carbon::now()->subDays(rand(100, 500)),
                'manager_name' => $location['manager'],
                'manager_phone' => $location['phone'],
                'operating_hours' => json_encode($operatingHours),
                'features' => json_encode($location['features']),
                'timezone' => $location['timezone'],
                'currency' => $location['currency'],
                'language' => $location['language'],
            ]);

            $this->createKitchenStationsForBranch($branch, 'international');
            $this->createTablesForBranch($branch);
            
            $this->command->info("      ✓ Created international branch: {$branch->name} ({$location['country']})");
        }
    }

    private function createKitchenStationsForBranch(Branch $branch, string $stationType): void
    {
        $stationConfigs = $this->getKitchenStationConfigs($stationType);
        
        foreach ($stationConfigs as $config) {
            KitchenStation::create([
                'branch_id' => $branch->id,
                'name' => $config['name'],
                'code' => $this->generateStationCode($branch->id, $config['type']),
                'type' => $config['type'],
                'order_priority' => $config['priority'] ?? 1,
                'is_active' => true,
                'max_capacity' => $config['capacity'] ?? null,
                'printer_config' => isset($config['printer']) ? json_encode($config['printer']) : null,
                'notes' => isset($config['notes']) ? $config['notes'] : null,
            ]);
        }
    }

    private function createTablesForBranch(Branch $branch): void
    {
        if ($branch->type === 'mobile') {
            return; // No tables for mobile units
        }

        $tableCount = $this->calculateTableCount($branch->total_capacity);
        $tableConfigs = $this->generateTableConfigurations($tableCount);
        
        foreach ($tableConfigs as $config) {
            Table::create([
                'branch_id' => $branch->id,
                'number' => (string) $config['number'],
                'capacity' => $config['capacity'],
                'status' => 'available',
                'location' => $config['location'] ?? null,
                'is_active' => true,
                'description' => isset($config['features']) ? 'Features: ' . implode(', ', $config['features']) : null,
                'x_position' => $config['x_position'] ?? null,
                'y_position' => $config['y_position'] ?? null,
            ]);
        }
    }

    // Helper methods for data generation
    private function getBranchType(string $businessType): string
    {
        $mapping = [
            'restaurant' => 'dine_in',
            'cafe' => 'cafe',
            'food_court' => 'food_court',
            'restaurant_chain' => 'chain_restaurant',
            'resort_restaurant' => 'resort',
            'food_truck' => 'mobile',
            'international_chain' => 'international',
        ];
        
        return $mapping[$businessType] ?? 'dine_in';
    }

    private function getCapacityForType(string $businessType): int
    {
        $capacities = [
            'restaurant' => rand(40, 80),
            'cafe' => rand(20, 40),
            'food_court' => rand(100, 200),
            'restaurant_chain' => rand(60, 120),
            'resort_restaurant' => rand(80, 150),
            'food_truck' => 20,
            'international_chain' => rand(80, 200),
        ];
        
        return $capacities[$businessType] ?? 50;
    }

    private function getOperatingHours(string $businessType): array
    {
        switch ($businessType) {
            case 'cafe':
                return [
                    'monday' => ['07:00', '18:00'],
                    'tuesday' => ['07:00', '18:00'],
                    'wednesday' => ['07:00', '18:00'],
                    'thursday' => ['07:00', '18:00'],
                    'friday' => ['07:00', '19:00'],
                    'saturday' => ['08:00', '19:00'],
                    'sunday' => ['08:00', '17:00'],
                ];
            case 'food_truck':
                return [
                    'monday' => ['11:00', '21:00'],
                    'tuesday' => ['11:00', '21:00'],
                    'wednesday' => ['11:00', '21:00'],
                    'thursday' => ['11:00', '21:00'],
                    'friday' => ['11:00', '23:00'],
                    'saturday' => ['10:00', '23:00'],
                    'sunday' => ['12:00', '20:00'],
                ];
            default:
                return [
                    'monday' => ['11:00', '22:00'],
                    'tuesday' => ['11:00', '22:00'],
                    'wednesday' => ['11:00', '22:00'],
                    'thursday' => ['11:00', '22:00'],
                    'friday' => ['11:00', '23:00'],
                    'saturday' => ['10:00', '23:00'],
                    'sunday' => ['10:00', '22:00'],
                ];
        }
    }

    private function getBranchFeatures(string $businessType): array
    {
        $baseFeatures = ['dine_in', 'takeaway'];
        
        switch ($businessType) {
            case 'cafe':
                return array_merge($baseFeatures, ['wifi', 'outdoor_seating', 'coffee_bar']);
            case 'food_court':
                return array_merge($baseFeatures, ['multiple_cuisines', 'food_court_seating', 'shared_dining']);
            case 'resort_restaurant':
                return array_merge($baseFeatures, ['beachfront', 'outdoor_dining', 'bar', 'live_music']);
            case 'food_truck':
                return ['quick_service', 'mobile_ordering', 'street_food'];
            default:
                return array_merge($baseFeatures, ['delivery', 'reservations']);
        }
    }

    private function getLocalLocations(): array
    {
        return [
            ['name' => 'Colombo Central', 'address' => '123 Main Street', 'city' => 'Colombo', 'state' => 'Western Province', 'zip' => '00100'],
            ['name' => 'Kandy', 'address' => '456 Temple Road', 'city' => 'Kandy', 'state' => 'Central Province', 'zip' => '20000'],
            ['name' => 'Galle', 'address' => '789 Fort Road', 'city' => 'Galle', 'state' => 'Southern Province', 'zip' => '80000'],
            ['name' => 'Negombo', 'address' => '321 Beach Road', 'city' => 'Negombo', 'state' => 'Western Province', 'zip' => '11500'],
            ['name' => 'Jaffna', 'address' => '654 Hospital Road', 'city' => 'Jaffna', 'state' => 'Northern Province', 'zip' => '40000'],
        ];
    }

    private function getFranchiseLocations(): array
    {
        return array_merge($this->getLocalLocations(), [
            ['name' => 'Batticaloa', 'address' => '987 Bazaar Street', 'city' => 'Batticaloa', 'state' => 'Eastern Province', 'zip' => '30000'],
            ['name' => 'Kurunegala', 'address' => '147 Clock Tower Road', 'city' => 'Kurunegala', 'state' => 'North Western Province', 'zip' => '60000'],
            ['name' => 'Anuradhapura', 'address' => '258 Sacred City Road', 'city' => 'Anuradhapura', 'state' => 'North Central Province', 'zip' => '50000'],
        ]);
    }

    private function getSeasonalLocations(): array
    {
        return [
            [
                'name' => 'Mirissa Beach',
                'address' => '123 Coconut Tree Hill Road',
                'city' => 'Mirissa',
                'state' => 'Southern Province',
                'zip' => '81740',
                'active_season' => true,
                'season_start' => 'November',
                'season_end' => 'April',
                'capacity' => 80,
                'operating_hours' => [
                    'monday' => ['10:00', '23:00'],
                    'tuesday' => ['10:00', '23:00'],
                    'wednesday' => ['10:00', '23:00'],
                    'thursday' => ['10:00', '23:00'],
                    'friday' => ['10:00', '00:00'],
                    'saturday' => ['10:00', '00:00'],
                    'sunday' => ['10:00', '23:00'],
                ],
                'features' => ['beachfront', 'sunset_dining', 'seafood_specialty', 'bar'],
            ],
            [
                'name' => 'Ella Hills',
                'address' => '456 Mountain View Road',
                'city' => 'Ella',
                'state' => 'Uva Province',
                'zip' => '90090',
                'active_season' => false,
                'season_start' => 'December',
                'season_end' => 'March',
                'capacity' => 50,
                'operating_hours' => json_encode([
                    'monday' => ['08:00', '21:00'],
                    'tuesday' => ['08:00', '21:00'],
                    'wednesday' => ['08:00', '21:00'],
                    'thursday' => ['08:00', '21:00'],
                    'friday' => ['08:00', '22:00'],
                    'saturday' => ['08:00', '22:00'],
                    'sunday' => ['08:00', '21:00'],
                ]),
                'features' => json_encode(['mountain_view', 'hiking_cafe', 'local_cuisine', 'tea_plantation']),
            ],
        ];
    }

    private function getInternationalLocations(): array
    {
        return [
            [
                'name' => 'New York Manhattan',
                'address' => '123 Broadway, Manhattan',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'USA',
                'phone' => '+1 212 555 0123',
                'manager' => 'John Smith',
                'capacity' => 120,
                'operating_hours' => json_encode([
                    'monday' => ['11:00', '23:00'],
                    'tuesday' => ['11:00', '23:00'],
                    'wednesday' => ['11:00', '23:00'],
                    'thursday' => ['11:00', '23:00'],
                    'friday' => ['11:00', '00:00'],
                    'saturday' => ['10:00', '00:00'],
                    'sunday' => ['10:00', '22:00'],
                ]),
                'features' => json_encode(['fine_dining', 'business_lunch', 'rooftop_bar', 'private_events']),
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'language' => 'en',
            ],
            [
                'name' => 'London Soho',
                'address' => '456 Oxford Street, Soho',
                'city' => 'London',
                'state' => 'England',
                'zip' => 'W1D 1BS',
                'country' => 'UK',
                'phone' => '+44 20 7123 4567',
                'manager' => 'Emma Johnson',
                'capacity' => 90,
                'operating_hours' => json_encode([
                    'monday' => ['12:00', '22:00'],
                    'tuesday' => ['12:00', '22:00'],
                    'wednesday' => ['12:00', '22:00'],
                    'thursday' => ['12:00', '22:00'],
                    'friday' => ['12:00', '23:00'],
                    'saturday' => ['11:00', '23:00'],
                    'sunday' => ['11:00', '21:00'],
                ]),
                'features' => json_encode(['traditional_british', 'craft_beer', 'weekend_brunch', 'theater_district']),
                'timezone' => 'Europe/London',
                'currency' => 'GBP',
                'language' => 'en',
            ],
        ];
    }

    private function getKitchenStationConfigs(string $stationType): array
    {
        switch ($stationType) {
            case 'mobile':
                return [
                    ['name' => 'Mobile Grill', 'type' => 'grill', 'priority' => 1, 'capacity' => 10, 'equipment' => ['portable_grill', 'fryer'], 'specialties' => ['burgers', 'fries']],
                    ['name' => 'Prep Station', 'type' => 'prep', 'priority' => 2, 'capacity' => 5, 'equipment' => ['cutting_board', 'storage'], 'specialties' => ['salads', 'wraps']],
                ];
            
            case 'compact':
                return [
                    ['name' => 'Hot Kitchen', 'type' => 'cooking', 'priority' => 1, 'capacity' => 15, 'equipment' => ['stove', 'oven'], 'specialties' => ['main_courses']],
                    ['name' => 'Cold Prep', 'type' => 'prep', 'priority' => 2, 'capacity' => 10, 'equipment' => ['refrigerator', 'prep_tables'], 'specialties' => ['salads', 'appetizers']],
                    ['name' => 'Beverage Station', 'type' => 'beverage', 'priority' => 3, 'capacity' => 8, 'equipment' => ['coffee_machine', 'blender'], 'specialties' => ['drinks', 'smoothies']],
                ];
            
            case 'premium':
                return [
                    ['name' => 'Executive Chef Station', 'type' => 'cooking', 'priority' => 1, 'capacity' => 25, 'equipment' => ['professional_range', 'convection_oven', 'salamander'], 'specialties' => ['signature_dishes', 'fine_dining']],
                    ['name' => 'Grill Station', 'type' => 'grill', 'priority' => 2, 'capacity' => 20, 'equipment' => ['charcoal_grill', 'gas_grill'], 'specialties' => ['steaks', 'seafood']],
                    ['name' => 'Pastry Kitchen', 'type' => 'dessert', 'priority' => 3, 'capacity' => 15, 'equipment' => ['pastry_oven', 'mixer', 'blast_chiller'], 'specialties' => ['desserts', 'bread']],
                    ['name' => 'Cold Kitchen', 'type' => 'prep', 'priority' => 4, 'capacity' => 18, 'equipment' => ['walk_in_cooler', 'prep_stations'], 'specialties' => ['salads', 'cold_appetizers']],
                    ['name' => 'Beverage Bar', 'type' => 'beverage', 'priority' => 5, 'capacity' => 12, 'equipment' => ['espresso_machine', 'wine_refrigerator'], 'specialties' => ['cocktails', 'wine', 'coffee']],
                ];
            
            case 'international':
                return [
                    ['name' => 'International Kitchen', 'type' => 'cooking', 'priority' => 1, 'capacity' => 20, 'equipment' => ['multi_cuisine_range', 'tandoor', 'wok'], 'specialties' => ['international_cuisine']],
                    ['name' => 'Sushi Bar', 'type' => 'sushi', 'priority' => 2, 'capacity' => 8, 'equipment' => ['sushi_display', 'rice_cooker'], 'specialties' => ['sushi', 'sashimi']],
                    ['name' => 'Grill & Roast', 'type' => 'grill', 'priority' => 3, 'capacity' => 15, 'equipment' => ['rotisserie', 'grill'], 'specialties' => ['roasts', 'grilled_items']],
                    ['name' => 'Prep & Garde Manger', 'type' => 'prep', 'priority' => 4, 'capacity' => 12, 'equipment' => ['refrigerated_prep'], 'specialties' => ['salads', 'appetizers']],
                ];
            
            default: // standard
                return [
                    ['name' => 'Main Kitchen', 'type' => 'cooking', 'priority' => 1, 'capacity' => 20, 'equipment' => ['stove', 'oven', 'fryer'], 'specialties' => ['main_courses']],
                    ['name' => 'Grill Station', 'type' => 'grill', 'priority' => 2, 'capacity' => 15, 'equipment' => ['grill', 'griddle'], 'specialties' => ['grilled_items']],
                    ['name' => 'Cold Prep', 'type' => 'prep', 'priority' => 3, 'capacity' => 12, 'equipment' => ['prep_tables', 'refrigerator'], 'specialties' => ['salads', 'cold_dishes']],
                    ['name' => 'Dessert Station', 'type' => 'dessert', 'priority' => 4, 'capacity' => 8, 'equipment' => ['dessert_display', 'ice_cream_machine'], 'specialties' => ['desserts']],
                    ['name' => 'Beverage Station', 'type' => 'beverage', 'priority' => 5, 'capacity' => 10, 'equipment' => ['coffee_machine', 'juice_dispenser'], 'specialties' => ['beverages']],
                ];
        }
    }

    private function generateStationCode(int $branchId, string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        return $prefix . '-' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
    }

    private function calculateTableCount(int $capacity): int
    {
        return intval(ceil($capacity / 4)); // Average 4 people per table
    }

    private function generateTableConfigurations(int $tableCount): array
    {
        $tables = [];
        $locations = ['main_dining', 'window_side', 'center', 'outdoor', 'private'];
        
        for ($i = 1; $i <= $tableCount; $i++) {
            $capacity = $this->determineTableCapacity($i, $tableCount);
            $tables[] = [
                'number' => $i,
                'capacity' => $capacity,
                'type' => $this->determineTableType($capacity),
                'location' => $locations[array_rand($locations)],
                'features' => $this->generateTableFeatures($capacity),
            ];
        }
        
        return $tables;
    }

    private function determineTableCapacity(int $tableNumber, int $totalTables): int
    {
        // Mix of different table sizes
        if ($tableNumber <= $totalTables * 0.6) {
            return rand(2, 4); // Most tables are 2-4 people
        } elseif ($tableNumber <= $totalTables * 0.85) {
            return rand(4, 6); // Some larger tables
        } else {
            return rand(6, 10); // A few large tables for groups
        }
    }

    private function determineTableType(int $capacity): string
    {
        if ($capacity <= 2) return 'intimate';
        if ($capacity <= 4) return 'standard';
        if ($capacity <= 6) return 'family';
        return 'group';
    }

    private function generateTableFeatures(int $capacity): array
    {
        $features = [];
        
        if ($capacity >= 6) {
            $features[] = 'suitable_for_groups';
        }
        
        if (rand(1, 10) <= 3) {
            $features[] = 'window_view';
        }
        
        if (rand(1, 10) <= 2) {
            $features[] = 'private_seating';
        }
        
        return $features;
    }

    // Utility methods
    private function extractCity(string $address): string
    {
        $cities = ['Colombo', 'Kandy', 'Galle', 'Negombo', 'Jaffna', 'Batticaloa', 'Kurunegala'];
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }
        return 'Colombo';
    }

    private function extractState(string $address): string
    {
        $stateMapping = [
            'Colombo' => 'Western Province',
            'Negombo' => 'Western Province',
            'Kandy' => 'Central Province',
            'Galle' => 'Southern Province',
            'Jaffna' => 'Northern Province',
            'Batticaloa' => 'Eastern Province',
            'Kurunegala' => 'North Western Province',
        ];
        
        $city = $this->extractCity($address);
        return $stateMapping[$city] ?? 'Western Province';
    }

    private function generateZipCode(): string
    {
        return str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function generatePhoneNumber(): string
    {
        return '+94 ' . rand(11, 91) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    }

    private function generateManagerName(): string
    {
        $firstNames = ['Saman', 'Kumari', 'Rohan', 'Priya', 'Nimal', 'Madhuri', 'Lasith', 'Chamari', 'Dinesh', 'Shanti'];
        $lastNames = ['Silva', 'Perera', 'Fernando', 'Jayawardena', 'Gunawardena', 'Rathnayake', 'Wijesinghe', 'Mendis'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function getEarliestOpeningTime(array $operatingHours): string
    {
        $earliestTime = '23:59';
        foreach ($operatingHours as $day => $hours) {
            if (is_array($hours) && count($hours) >= 2) {
                $openTime = $hours[0];
                if ($openTime < $earliestTime) {
                    $earliestTime = $openTime;
                }
            }
        }
        return $earliestTime;
    }

    private function getLatestClosingTime(array $operatingHours): string
    {
        $latestTime = '00:00';
        foreach ($operatingHours as $day => $hours) {
            if (is_array($hours) && count($hours) >= 2) {
                $closeTime = $hours[1];
                if ($closeTime > $latestTime) {
                    $latestTime = $closeTime;
                }
            }
        }
        return $latestTime;
    }

    private function getReservationFee(string $businessType): float
    {
        switch ($businessType) {
            case 'fine_dining':
                return 25.00;
            case 'restaurant':
                return 15.00;
            case 'cafe':
                return 5.00;
            case 'food_truck':
                return 0.00;
            default:
                return 10.00;
        }
    }

    private function getCancellationFee(string $businessType): float
    {
        switch ($businessType) {
            case 'fine_dining':
                return 50.00;
            case 'restaurant':
                return 25.00;
            case 'cafe':
                return 10.00;
            case 'food_truck':
                return 0.00;
            default:
                return 15.00;
        }
    }

    private function generateActivationKey(): string
    {
        return strtoupper(Str::random(12));
    }

    private function extractDomain(string $email): string
    {
        return substr(strrchr($email, "@"), 1);
    }
}
