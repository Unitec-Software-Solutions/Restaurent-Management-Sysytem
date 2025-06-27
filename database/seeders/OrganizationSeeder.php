<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\KitchenStation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Seed organizations with complete branch and kitchen station setup
     * Following comprehensive UI/UX design system patterns
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding Organizations with UI/UX optimized data...');

        // Create main restaurant organization following card-based design
        $organization = Organization::create([
            'name' => 'Spice Garden Restaurant Group',
            'trading_name' => 'Spice Garden',
            'registration_number' => 'REG001',
            'email' => 'info@spicegarden.lk',
            'password' => Hash::make('password123'),
            'phone' => '+94112345678',
            'address' => '123 Galle Road, Colombo 03',
            'website' => 'https://spicegarden.lk',
            'contact_person' => 'Kumara Silva',
            'contact_person_designation' => 'General Manager',
            'contact_person_phone' => '+94 77 123 4567',
            'activation_key' => Str::random(40),
            'business_type' => 'restaurant',
            'status' => 'active',
            'is_active' => true
        ]);

        $this->command->info("  ✅ Created organization: {$organization->name}");

        // Create branches following responsive grid patterns
        $branches = [
            [
                'name' => 'Spice Garden Colombo',
                'address' => '123 Galle Road, Colombo 03',
                'phone' => '+94112345678',
                'email' => 'colombo@spicegarden.lk',
                'is_head_office' => true,
                'total_capacity' => 120
            ],
            [
                'name' => 'Spice Garden Kandy',
                'address' => '456 Peradeniya Road, Kandy',
                'phone' => '+94812345678',
                'email' => 'kandy@spicegarden.lk',
                'is_head_office' => false,
                'total_capacity' => 80
            ],
            [
                'name' => 'Spice Garden Galle',
                'address' => '789 Wakwella Road, Galle',
                'phone' => '+94912345678',
                'email' => 'galle@spicegarden.lk',
                'is_head_office' => false,
                'total_capacity' => 100
            ]
        ];

        foreach ($branches as $index => $branchData) {
            $branch = Branch::create([
                'organization_id' => $organization->id,
                ...$branchData,
                'type' => 'restaurant',
                'activation_key' => \Illuminate\Support\Str::random(40),
                'is_active' => true,
                'opening_time' => '10:00:00',
                'closing_time' => '23:00:00',
                'reservation_fee' => 500,
                'cancellation_fee' => 250
            ]);

            $this->command->info("    📍 Created branch: {$branch->name}");

            // Create kitchen stations with REQUIRED CODE field and UI optimization
            $this->createKitchenStationsWithUISupport($branch);

            // Create admin user following UI user management patterns
            $this->createBranchAdminWithUISettings($organization, $branch, $index);
        }

        $this->command->info('✅ Organization seeding completed successfully');
    }

    /**
     * Create kitchen stations with comprehensive UI/UX support
     * Following card-based design, status badges, and responsive patterns
     * FIXED: Always includes the required 'code' field
     */
    private function createKitchenStationsWithUISupport(Branch $branch): void
    {
        $stations = [
            [
                'name' => 'Hot Kitchen',
                'code' => $this->generateStationCode('COOK', $branch->id, 1), // REQUIRED FIELD
                'type' => 'cooking',
                'description' => 'Main cooking station for hot dishes and daily specials',
                'order_priority' => 1,
                'max_capacity' => 50.00,
                'ui_metadata' => [
                    'icon' => 'fas fa-fire',
                    'color_scheme' => 'bg-red-100 text-red-800',
                    'dashboard_priority' => 1,
                    'card_category' => 'primary'
                ]
            ],
            [
                'name' => 'Cold Station',
                'code' => $this->generateStationCode('PREP', $branch->id, 2), // REQUIRED FIELD
                'type' => 'prep',
                'description' => 'Cold food preparation area for salads and appetizers',
                'order_priority' => 2,
                'max_capacity' => 30.00,
                'ui_metadata' => [
                    'icon' => 'fas fa-snowflake',
                    'color_scheme' => 'bg-blue-100 text-blue-800',
                    'dashboard_priority' => 2,
                    'card_category' => 'info'
                ]
            ],
            [
                'name' => 'Grill Station',
                'code' => $this->generateStationCode('GRILL', $branch->id, 3), // REQUIRED FIELD
                'type' => 'grill',
                'description' => 'Grilling station for BBQ items and flame-cooked dishes',
                'order_priority' => 3,
                'max_capacity' => 35.00,
                'ui_metadata' => [
                    'icon' => 'fas fa-fire-flame-curved',
                    'color_scheme' => 'bg-orange-100 text-orange-800',
                    'dashboard_priority' => 2,
                    'card_category' => 'warning'
                ]
            ],
            [
                'name' => 'Beverage Bar',
                'code' => $this->generateStationCode('BEV', $branch->id, 4), // REQUIRED FIELD
                'type' => 'beverage',
                'description' => 'Drink preparation station for juices, smoothies, and beverages',
                'order_priority' => 4,
                'max_capacity' => 25.00,
                'ui_metadata' => [
                    'icon' => 'fas fa-wine-glass',
                    'color_scheme' => 'bg-purple-100 text-purple-800',
                    'dashboard_priority' => 3,
                    'card_category' => 'custom'
                ]
            ],
            [
                'name' => 'Dessert Corner',
                'code' => $this->generateStationCode('DESS', $branch->id, 5), // REQUIRED FIELD
                'type' => 'dessert',
                'description' => 'Dessert preparation area for sweets and pastries',
                'order_priority' => 5,
                'max_capacity' => 20.00,
                'ui_metadata' => [
                    'icon' => 'fas fa-ice-cream',
                    'color_scheme' => 'bg-pink-100 text-pink-800',
                    'dashboard_priority' => 4,
                    'card_category' => 'accent'
                ]
            ]
        ];

        foreach ($stations as $stationData) {
            // Extract UI metadata for proper organization
            $uiMetadata = $stationData['ui_metadata'];
            unset($stationData['ui_metadata']);

            // CRITICAL: Ensure 'code' is included in the create data
            $stationCreateData = [
                'branch_id' => $branch->id,
                'name' => $stationData['name'],
                'code' => $stationData['code'],
                'type' => $stationData['type'],
                'order_priority' => $stationData['order_priority'],
                'max_capacity' => $stationData['max_capacity'],
                'is_active' => true,
                'printer_config' => json_encode([
                    'printer_ip' => '192.168.1.' . (100 + $stationData['order_priority']),
                    'printer_name' => $stationData['name'] . ' Printer',
                    'paper_size' => '80mm',
                    'auto_print' => false,
                    'print_logo' => true,
                    'print_quality' => 'high'
                ]),
                'notes' => 'Auto-created station for ' . $branch->name . ' - ' . $stationData['description']
            ];

            $station = KitchenStation::create($stationCreateData);

            $this->command->info("      🏭 Created kitchen station: {$station->name} (Code: {$station->code})");
        }
    }

    /**
     * Generate unique station codes following naming convention
     * Format: PREFIX-BRANCH-SEQUENCE (e.g., COOK-01-001)
     */
    private function generateStationCode(string $typePrefix, int $branchId, int $sequence): string
    {
        $branchCode = str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        return $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
    }

    /**
     * Create branch admin user with comprehensive UI settings
     * Following user management patterns and accessibility standards
     */
    private function createBranchAdminWithUISettings(Organization $organization, Branch $branch, int $index): void
    {
        $adminData = [
            [
                'name' => 'Kumara Perera',
                'email' => 'admin.colombo@spicegarden.lk',
                'position' => 'Branch Manager - Colombo',
                'permissions' => [
                    'dashboard.view',
                    'kitchen.manage',
                    'orders.manage',
                    'staff.manage',
                    'reports.view'
                ]
            ],
            [
                'name' => 'Nimal Fernando', 
                'email' => 'admin.kandy@spicegarden.lk',
                'position' => 'Branch Manager - Kandy',
                'permissions' => [
                    'dashboard.view',
                    'kitchen.manage',
                    'orders.manage',
                    'staff.manage',
                    'reports.view'
                ]
            ],
            [
                'name' => 'Saman Wickramasinghe',
                'email' => 'admin.galle@spicegarden.lk',
                'position' => 'Branch Manager - Galle',
                'permissions' => [
                    'dashboard.view',
                    'kitchen.manage',
                    'orders.manage',
                    'staff.manage',
                    'reports.view'
                ]
            ]
        ];

        $userData = $adminData[$index] ?? $adminData[0];

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // Default password
            'phone_number' => $branch->phone,
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'is_admin' => true,
            'is_active' => true,
            'is_registered' => true
        ]);

        $this->command->info("      👤 Created admin user: {$user->name} ({$user->email})");
    }
}
