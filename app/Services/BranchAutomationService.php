<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Admin;
use App\Models\Role;
use App\Models\KitchenStation;
use App\Models\InventoryItem;
use App\Models\ItemMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BranchAutomationService
{
    /**
     * Complete branch setup with automation
     */
    public function setupNewBranch(Branch $branch): void
    {
        DB::transaction(function () use ($branch) {
            // 1. Create branch admin
            $branchAdmin = $this->createBranchAdmin($branch);

            // 2. Create customized kitchen stations
            $this->createCustomizedKitchenStations($branch);

            // 3. Setup starter inventory
            $this->setupStarterInventory($branch);

            // 4. Assign branch-specific permissions
            $this->assignBranchPermissions($branchAdmin, $branch);

            // 5. Log branch creation
            try {
                activity()
                    ->causedBy($branchAdmin)
                    ->performedOn($branch)
                    ->log('Branch created with automation');
            } catch (\Exception $e) {
                // Activity logging not available - skip silently
            }
        });
    }

    /**
     * Create branch administrator
     */
    protected function createBranchAdmin(Branch $branch): Admin
    {
        $password = Str::random(12);
        $adminEmail = 'admin.' . Str::slug($branch->name) . '@' .
                     str_replace(['http://', 'https://'], '', parse_url($branch->organization->email, PHP_URL_HOST) ?? 'restaurant.com');

        $adminData = [
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'name' => 'Branch Manager - ' . $branch->name,
            'email' => $adminEmail,
            'password' => Hash::make($password),
            'phone' => $branch->contact_person_phone ?? $branch->phone,
            'job_title' => 'Branch Manager',
            'is_active' => true,
        ];

        $admin = Admin::create($adminData);

        \Log::info('[BranchAutomationService@createBranchAdmin] Created branch admin', [
            'admin_id' => $admin->id,
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id
        ]);

        // Assign branch admin role (Spatie)
        $branchAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(
            [
                'name' => 'Branch Administrator',
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'guard_name' => 'admin'
            ],
            [
                'scope' => 'branch',
                'description' => 'Full administrative access to branch operations'
            ]
        );
        $admin->syncRoles([$branchAdminRole]);

        \Log::info('[BranchAutomationService@createBranchAdmin] Assigned Branch Administrator role', [
            'admin_id' => $admin->id,
            'role_id' => $branchAdminRole->id
        ]);

        // Assign permissions to the role based on subscription plan
        if (class_exists('App\\Services\\PermissionSystemService')) {
            $permissionService = app(\App\Services\PermissionSystemService::class);
            $permissionDefinitions = $permissionService->getPermissionDefinitions();
            $modulesConfig = config('modules');
            $availablePermissions = $permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
            \Log::info('[BranchAutomationService@createBranchAdmin] Assigning permissions to Branch Administrator role', [
                'role_id' => $branchAdminRole->id,
                'permissions' => array_keys($availablePermissions)
            ]);
            $branchAdminRole->syncPermissions(array_keys($availablePermissions));
        }

        return $admin;
    }

    /**
     * Create customized kitchen stations based on branch type
     */
    protected function createCustomizedKitchenStations(Branch $branch): void
    {
        Log::info("Creating kitchen stations for branch: {$branch->name} (ID: {$branch->id}, Type: {$branch->type})");

        $stationTemplates = $this->getStationTemplatesByBranchType($branch->type);
        Log::info("Found " . count($stationTemplates) . " station templates for type: {$branch->type}");

        foreach ($stationTemplates as $index => $template) {
            $stationData = [
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'name' => $template['name'],
                'code' => $this->generateStationCode($template['code_prefix'], $branch->id, $index + 1),
                'type' => $template['type'],
                'station_type' => 'standard',
                'description' => $template['description'],
                'order_priority' => $template['priority'],
                'priority_level' => $template['priority'],
                'max_capacity' => $template['capacity'],
                'max_concurrent_orders' => 5,
                'current_orders' => 0,
                'is_active' => true,
                'ui_metadata' => json_encode([
                    'icon' => $template['icon'],
                    'color_scheme' => $template['color'],
                    'dashboard_priority' => $template['priority'],
                    'card_category' => $template['category']
                ]),
                'printer_config' => json_encode([
                    'paper_size' => '80mm',
                    'auto_print' => false,
                    'print_logo' => true,
                    'print_timestamp' => true
                ])
            ];

            Log::info("Creating kitchen station with data: " . json_encode($stationData));

            try {
                $station = KitchenStation::create($stationData);
                Log::info("Successfully created kitchen station: {$station->name} (ID: {$station->id})");
            } catch (\Exception $e) {
                Log::error("Failed to create kitchen station: " . $e->getMessage());
                Log::error("Station data: " . json_encode($stationData));
            }
        }

        $stationCount = KitchenStation::where('branch_id', $branch->id)->count();
        Log::info("Total kitchen stations for branch {$branch->id}: {$stationCount}");
    }

    /**
     * Get station templates by branch type
     */
    protected function getStationTemplatesByBranchType(string $branchType): array
    {
        $templates = [
            'restaurant' => [
                ['name' => 'Hot Kitchen', 'code_prefix' => 'HOT', 'type' => 'cooking', 'priority' => 1, 'capacity' => 50.00, 'icon' => 'fas fa-fire', 'color' => 'bg-red-100 text-red-800', 'category' => 'primary', 'description' => 'Main cooking station'],
                ['name' => 'Cold Station', 'code_prefix' => 'COLD', 'type' => 'prep', 'priority' => 2, 'capacity' => 30.00, 'icon' => 'fas fa-snowflake', 'color' => 'bg-blue-100 text-blue-800', 'category' => 'secondary', 'description' => 'Cold food preparation'],
                ['name' => 'Grill Station', 'code_prefix' => 'GRILL', 'type' => 'grill', 'priority' => 3, 'capacity' => 40.00, 'icon' => 'fas fa-hamburger', 'color' => 'bg-yellow-100 text-yellow-800', 'category' => 'primary', 'description' => 'Grilling and barbecue'],
                ['name' => 'Dessert Station', 'code_prefix' => 'DESS', 'type' => 'dessert', 'priority' => 4, 'capacity' => 20.00, 'icon' => 'fas fa-birthday-cake', 'color' => 'bg-pink-100 text-pink-800', 'category' => 'tertiary', 'description' => 'Dessert preparation'],
                ['name' => 'Bar Station', 'code_prefix' => 'BAR', 'type' => 'bar', 'priority' => 5, 'capacity' => 35.00, 'icon' => 'fas fa-cocktail', 'color' => 'bg-purple-100 text-purple-800', 'category' => 'secondary', 'description' => 'Beverage and cocktails']
            ],
            'cafe' => [
                ['name' => 'Coffee Station', 'code_prefix' => 'COFFEE', 'type' => 'beverage', 'priority' => 1, 'capacity' => 40.00, 'icon' => 'fas fa-coffee', 'color' => 'bg-amber-100 text-amber-800', 'category' => 'primary', 'description' => 'Coffee and hot beverages'],
                ['name' => 'Pastry Station', 'code_prefix' => 'PASTRY', 'type' => 'pastry', 'priority' => 2, 'capacity' => 25.00, 'icon' => 'fas fa-bread-slice', 'color' => 'bg-orange-100 text-orange-800', 'category' => 'secondary', 'description' => 'Fresh pastries and baked goods'],
                ['name' => 'Cold Prep', 'code_prefix' => 'COLD', 'type' => 'prep', 'priority' => 3, 'capacity' => 20.00, 'icon' => 'fas fa-leaf', 'color' => 'bg-green-100 text-green-800', 'category' => 'tertiary', 'description' => 'Cold food preparation']
            ],
            'fast_food' => [
                ['name' => 'Fry Station', 'code_prefix' => 'FRY', 'type' => 'frying', 'priority' => 1, 'capacity' => 60.00, 'icon' => 'fas fa-fire', 'color' => 'bg-red-100 text-red-800', 'category' => 'primary', 'description' => 'Deep frying station'],
                ['name' => 'Grill Line', 'code_prefix' => 'GRILL', 'type' => 'grill', 'priority' => 2, 'capacity' => 50.00, 'icon' => 'fas fa-hamburger', 'color' => 'bg-yellow-100 text-yellow-800', 'category' => 'primary', 'description' => 'Fast grill operations'],
                ['name' => 'Assembly', 'code_prefix' => 'ASSEM', 'type' => 'assembly', 'priority' => 3, 'capacity' => 40.00, 'icon' => 'fas fa-layer-group', 'color' => 'bg-blue-100 text-blue-800', 'category' => 'secondary', 'description' => 'Order assembly line']
            ]
        ];

        return $templates[$branchType] ?? $templates['restaurant'];
    }

    /**
     * Setup starter inventory for new branch
     */
    protected function setupStarterInventory(Branch $branch): void
    {
        // Get common inventory items from organization's head office
        $headOfficeItems = ItemMaster::where('organization_id', $branch->organization_id)
            ->where('is_active', true)
            ->where('is_inventory_item', true)
            ->limit(20)
            ->get();

        foreach ($headOfficeItems as $item) {
            InventoryItem::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'item_master_id' => $item->id,
                'current_stock' => 0,
                'minimum_stock' => $item->minimum_stock ?? 10,
                'maximum_stock' => $item->maximum_stock ?? 100,
                'reorder_level' => $item->reorder_level ?? 20,
                'unit_cost' => $item->purchase_price ?? 0,
                'last_updated' => now(),
            ]);
        }
    }

    /**
     * Assign branch-specific permissions
     */
    protected function assignBranchPermissions(Admin $admin, Branch $branch): void
    {
        // Use PermissionSystemService to filter permissions by subscription
        if (class_exists('App\\Services\\PermissionSystemService')) {
            $permissionService = app(\App\Services\PermissionSystemService::class);
            $permissionDefinitions = $permissionService->getPermissionDefinitions();
            $modulesConfig = config('modules');
            $availablePermissions = $permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
            $admin->syncPermissions(array_keys($availablePermissions));
        }
    }

    /**
     * Generate unique station code
     */
    protected function generateStationCode(string $typePrefix, int $branchId, int $sequence): string
    {
        $branchCode = str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $sequenceCode = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
    }


    public function setupBranchResources(Branch $branch): void
    {
        // Create inventory items from head office master items
        $this->createBranchInventory($branch);

        // Create default kitchen stations if not head office
        if (!$branch->is_head_office) {
            $this->createDefaultKitchenStations($branch);
        }

        // Setup branch-specific settings
        $this->setupBranchSettings($branch);

        Log::info('Branch resources setup completed', [
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id
        ]);
    }

    /**
     * Create inventory items for the branch from head office master items
     */
    protected function createBranchInventory(Branch $branch): void
    {
        $headOfficeItems = ItemMaster::where('organization_id', $branch->organization_id)
            ->where('is_active', true)
            ->where('is_inventory_item', true)
            ->get();

        foreach ($headOfficeItems as $item) {
            InventoryItem::firstOrCreate([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'item_master_id' => $item->id,
            ], [
                'current_stock' => 0,
                'minimum_stock' => $item->minimum_stock ?? 10,
                'maximum_stock' => $item->maximum_stock ?? 100,
                'reorder_level' => $item->reorder_level ?? 20,
                'unit_cost' => $item->purchase_price ?? 0,
                'last_updated' => now(),
            ]);
        }
    }

    /**
     * Setup branch-specific settings and configurations
     */
    protected function setupBranchSettings(Branch $branch): void
    {
        $defaultSettings = [
            'pos_enabled' => true,
            'kitchen_display_enabled' => true,
            'reservation_enabled' => true,
            'delivery_enabled' => false,
            'takeaway_enabled' => true,
            'tax_rate' => 10.0,
            'service_charge' => 5.0,
            'currency' => 'USD',
            'timezone' => 'UTC',
            'language' => 'en'
        ];

        $branch->update([
            'settings' => array_merge($branch->settings ?? [], $defaultSettings)
        ]);
    }

    /**
     * Create default kitchen stations for new branch
     */
    protected function createDefaultKitchenStations(Branch $branch): void
    {
        $defaultStations = $branch->getDefaultKitchenStations();

        foreach ($defaultStations as $stationData) {
            KitchenStation::create(array_merge($stationData, [
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id
            ]));
        }
    }
}
