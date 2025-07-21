<?php
// filepath: database/seeders/MinimalSystemSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\SubscriptionPlan;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
use App\Enums\ReservationType;
use App\Enums\OrderType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MinimalSystemSeeder extends Seeder
{
    /**
     * Seed only essential system components for Laravel + PostgreSQL + Tailwind CSS
     */
    public function run(): void
    {
        $this->command->info('🎛️ Creating minimal system foundation...');

        DB::transaction(function () {
            // Step 1: Clear existing data
            $this->clearExistingData();

            // Step 2: Create system modules
            $this->createSystemModules();

            // Step 3: Create permissions
            $allPermissions = $this->collectAllSystemPermissions();
            foreach ($allPermissions as $perm => $desc) {
                Permission::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => 'admin',
                ]);
            }

            // Step 4: Create subscription plan FIRST
            $subscriptionPlan = SubscriptionPlan::create([
                'name' => 'Premium Plan',
                'price' => 99.99,
                'currency' => 'USD',
                'description' => 'Full-featured restaurant management plan',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 10,
                'max_employees' => 100,
                'modules' => [1, 2, 3, 4, 5, 6, 7, 8],
                'features' => [
                    'unlimited_orders',
                    'advanced_reporting',
                    'inventory_management',
                    'multi_branch_support',
                    'customer_management',
                    'pos_integration'
                ],
                'is_active' => true
            ]);

            // Step 5: Create organization using correct plan ID
            $organization = Organization::create([
                'name' => 'Delicious Bites Restaurant',
                'email' => 'admin@deliciousbites.com',
                'phone' => '+94 11 123 4567',
                'address' => '123 Main Street, Colombo 03, Sri Lanka',
                'contact_person' => 'John Manager',
                'contact_person_designation' => 'General Manager',
                'contact_person_phone' => '+94 77 123 4567',
                'business_type' => 'restaurant',
                'subscription_plan_id' => $subscriptionPlan->getKey(),
                'discount_percentage' => 5.00,
                'is_active' => true,
                'activated_at' => now(),
                'password' => Hash::make('DeliciousBites123!')
            ]);
        });

        $this->command->info('✅ Minimal system foundation created successfully');
    }

    /**
     * Collect all permissions used in system (from PermissionSystemService, policies, middleware, sidebar, blade, etc.)
     */
    private function collectAllSystemPermissions(): array
    {
        $service = new \App\Services\PermissionSystemService();
        $defs = $service->getPermissionDefinitions();
        $allPermissions = [];
        foreach ($defs as $cat) {
            if (isset($cat['permissions'])) {
                foreach ($cat['permissions'] as $perm => $desc) {
                    $allPermissions[$perm] = $desc;
                }
            }
        }
        // Add legacy and sidebar/menu permissions
        $sidebarFiles = [app_path('View/Components/AdminSidebar.php'), app_path('View/Components/Sidebar.php')];
        foreach ($sidebarFiles as $sidebarPath) {
            if (file_exists($sidebarPath)) {
                $code = file_get_contents($sidebarPath);
                preg_match_all('/permission[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
                }
            }
        }
        // Scan blade files for @can/@canany usage
        $bladeFiles = glob(resource_path('views/**/*.blade.php'));
        foreach ($bladeFiles as $file) {
            $code = file_get_contents($file);
            preg_match_all('/@can\([\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
            foreach ($matches[1] as $perm) {
                $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
            }
        }
        return $allPermissions;
    }

    // Removed assignRolePermissions. Role-permission assignments should be done via admin panel UI.

    /**
     * Clear existing data for clean start
     */
    private function clearExistingData(): void
    {
        $this->command->info('  🧹 Clearing existing data...');

        // Clear in dependency order for PostgreSQL - only clear existing tables
        $this->safeTableDelete('order_items');
        $this->safeTableDelete('orders');
        $this->safeTableDelete('reservations');
        $this->safeTableDelete('menu_menu_items');
        $this->safeTableDelete('menu_items');
        $this->safeTableDelete('menu_categories');
        $this->safeTableDelete('menus');
        $this->safeTableDelete('tables');
        $this->safeTableDelete('customers');
        $this->safeTableDelete('item_masters');
        $this->safeTableDelete('item_categories');
        $this->safeTableDelete('branches');
        $this->safeTableDelete('organizations');
        $this->safeTableDelete('subscription_plans');

        $this->safeTableDelete('model_has_roles');
        $this->safeTableDelete('model_has_permissions');
        $this->safeTableDelete('role_has_permissions');

        Permission::truncate();
        Role::truncate();
        Module::truncate();

        // Clear admins but preserve any existing data structure
        Admin::truncate();

        $this->command->info('  ✅ Existing data cleared');
    }

    /**
     * Safely delete from table only if it exists
     */
    private function safeTableDelete(string $tableName): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                DB::table($tableName)->delete();
            }
        } catch (\Exception $e) {
            // Ignore table doesn't exist errors
            $this->command->warn("  ⚠️ Table {$tableName} doesn't exist or couldn't be cleared");
        }
    }

    /**
     * Create essential system modules for restaurant management
     */
    private function createSystemModules(): void
    {
        $this->command->info('  📦 Creating system modules...');

        $modules = [
            [
                'name' => 'Order Management',
                'slug' => 'order',
                'description' => 'Complete order processing and kitchen workflows',
                'is_active' => true
            ],
            [
                'name' => 'Reservation System',
                'slug' => 'reservation',
                'description' => 'Table booking and reservation management',
                'is_active' => true
            ],
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'description' => 'Stock control and supplier management',
                'is_active' => true
            ],
            [
                'name' => 'Menu Management',
                'slug' => 'menu',
                'description' => 'Menu items, categories, and pricing',
                'is_active' => true
            ],
            [
                'name' => 'Customer Management',
                'slug' => 'customer',
                'description' => 'Customer database and loyalty programs',
                'is_active' => true
            ],
            [
                'name' => 'Kitchen Operations',
                'slug' => 'kitchen',
                'description' => 'Kitchen stations, KOT management, and production',
                'is_active' => true
            ],
            [
                'name' => 'Reports & Analytics',
                'slug' => 'report',
                'description' => 'Business intelligence and reporting',
                'is_active' => true
            ],
            [
                'name' => 'System Administration',
                'slug' => 'system',
                'description' => 'System settings and administration',
                'is_active' => true
            ]
        ];

        foreach ($modules as $moduleData) {
            $module = Module::create($moduleData);
            $this->command->info("    ✓ Module: {$module->getAttribute('name')}");
        }
    }

    /**
     * Create essential permissions for restaurant management system
     */
    private function createSystemPermissions(): void
    {
        $this->command->info('  🔐 Creating system permissions...');

        // Collect all permissions from PermissionSystemService, sidebar/menu, blade, and legacy arrays
        $allPermissions = [];
        $service = new \App\Services\PermissionSystemService();
        $defs = $service->getPermissionDefinitions();
        // Flatten all permission definitions
        foreach ($defs as $cat) {
            if (isset($cat['permissions'])) {
                foreach ($cat['permissions'] as $perm => $desc) {
                    $allPermissions[$perm] = $desc;
                }
            }
        }

        // Scan sidebar/menu definitions
        foreach ([app_path('View/Components/AdminSidebar.php'), app_path('View/Components/Sidebar.php')] as $sidebarPath) {
            if (file_exists($sidebarPath)) {
                $code = file_get_contents($sidebarPath);
                preg_match_all('/permission[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)[\'\"]/', $code, $matches);
                foreach ($matches[1] as $perm) {
                    $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
                }
            }
        }

        // Scan Blade menu-item usage
        $bladeFiles = glob(resource_path('views/**/*.blade.php'));
        foreach ($bladeFiles as $file) {
            $code = file_get_contents($file);
            preg_match_all('/can\([\'\"]([^\'\"]+)[\'\"]\)/', $code, $matches);
            foreach ($matches[1] as $perm) {
                $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
            }
        }

        // Add legacy array permissions (if any)
        $legacyPermissions = [
            'system.manage', 'system.settings', 'system.backup', 'system.logs',
            'orders.view', 'orders.create', 'orders.edit', 'orders.cancel',
            'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.cancel',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'inventory.manage', 'inventory.adjust', 'inventory.transfer', 'inventory.audit',
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete', 'menu.manage',
            'menu.categories', 'menu.pricing', 'menu.schedule', 'menu.publish',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'customers.manage', 'customers.loyalty', 'customers.communications',
            'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.orders',
            'kitchen.status', 'kitchen.recipes', 'kitchen.production',
            'kot.view', 'kot.create', 'kot.update', 'kot.manage', 'kot.print',
        ];
        foreach ($legacyPermissions as $perm) {
            $allPermissions[$perm] = $allPermissions[$perm] ?? ucwords(str_replace(['.', '_'], ' ', $perm));
        }

        // Sync all permissions
        $created = 0;
        foreach ($allPermissions as $name => $desc) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'admin'
            ]);
            $created++;
        }
        $this->command->info("    ✓ Created {$created} permissions");
    }

    /**
     * Create subscription plan for the organization
     */
    private function createSubscriptionPlan(): SubscriptionPlan
    {
        $this->command->info('  💳 Creating subscription plan...');

        $subscriptionPlan = SubscriptionPlan::create([
            'name' => 'Premium Plan',
            'price' => 99.99,
            'currency' => 'USD',
            'description' => 'Full-featured restaurant management plan',
            'is_trial' => false,
            'trial_period_days' => 30,
            'max_branches' => 10,
            'max_employees' => 100,
            'modules' => [1, 2, 3, 4, 5, 6, 7, 8],
            'features' => [
                'unlimited_orders',
                'advanced_reporting',
                'inventory_management',
                'multi_branch_support',
                'customer_management',
                'pos_integration'
            ],
            'is_active' => true
        ]);

        $this->command->info('    ✓ Premium subscription plan created');
        return $subscriptionPlan;
    }

    /**
     * Create sample organization
     */
    private function createOrganization(SubscriptionPlan $subscriptionPlan): Organization
    {
        $this->command->info('  🏢 Creating sample organization...');

        $organization = Organization::create([
            'name' => 'Delicious Bites Restaurant',
            'email' => 'admin@deliciousbites.com',
            'phone' => '+94 11 123 4567',
            'address' => '123 Main Street, Colombo 03, Sri Lanka',
            'contact_person' => 'John Manager',
            'contact_person_designation' => 'General Manager',
            'contact_person_phone' => '+94 77 123 4567',
            'business_type' => 'restaurant',
            'subscription_plan_id' => $subscriptionPlan->getKey(),
            'discount_percentage' => 5.00,
            'is_active' => true,
            'activated_at' => now(),
            'password' => Hash::make('DeliciousBites123!')
        ]);

        $this->command->info("    ✓ Organization created: {$organization->getAttribute('name')}");
        return $organization;
    }

    /**
     * Create organization, org admin, branch, and branch admin automatically
     */
    private function createOrganizationWithAdmins(SubscriptionPlan $subscriptionPlan): void
    {
        // Create organization
        $organization = Organization::create([ // Create organization
            'name' => 'Delicious Bites Restaurant',
            'email' => 'admin@deliciousbites.com',
            'phone' => '+94 11 123 4567',
            'address' => '123 Main Street, Colombo 03, Sri Lanka',
            'contact_person' => 'John Manager',
            'contact_person_designation' => 'General Manager',
            'contact_person_phone' => '+94 77 123 4567',
            'business_type' => 'restaurant',
            'subscription_plan_id' => $subscriptionPlan->getKey(), // Use getKey() for Eloquent model
            'discount_percentage' => 5.00,
            'is_active' => true,
            'activated_at' => now(),
            'password' => Hash::make('DeliciousBites123!')
        ]);
        $this->command->info("    ✓ Organization created: {$organization->getAttribute('name')}");

        // Create organization admin
        $orgAdmin = Admin::create([ // Create organization admin
            'email' => 'orgadmin@deliciousbites.com',
            'name' => 'Organization Admin',
            'password' => Hash::make('OrgAdmin123!'),
            'phone' => '+94 77 123 4567',
            'job_title' => 'Org Admin',
            'department' => 'Management',
            'organization_id' => $organization->getKey(), // Use getKey() for Eloquent model
            'branch_id' => null,
            'is_super_admin' => false,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode([
                'timezone' => 'UTC',
                'language' => 'en',
                'theme' => 'light',
                'notifications' => true
            ])
        ]);
        $orgAdminRole = Role::where('name', 'Organization Administrator')->where('guard_name', 'admin')->first();
        if ($orgAdminRole) {
            $orgAdmin->assignRole($orgAdminRole);
        }
        $this->command->info('    ✓ Organization Admin created: orgadmin@deliciousbites.com');

        // Create branch
        $branch = Branch::create([ // Create branch
            'organization_id' => $organization->getKey(), // Use getKey() for Eloquent model
            'name' => 'Main Branch - Colombo',
            'address' => '123 Main Street, Colombo 03, Sri Lanka',
            'phone' => '+94 11 123 4567',
            'email' => 'main@deliciousbites.com',
            'opening_time' => '08:00:00',
            'closing_time' => '23:00:00',
            'is_active' => true,
            'is_head_office' => true,
            'type' => 'restaurant',
            'status' => 'active',
            'max_capacity' => 80,
            'total_capacity' => 80,
            'reservation_fee' => 500.00,
            'cancellation_fee' => 250.00,
            'contact_person' => 'Sarah Branch Manager',
            'contact_person_designation' => 'Branch Manager',
            'contact_person_phone' => '+94 77 234 5678',
            'activated_at' => now(),
            'manager_name' => 'Sarah Branch Manager',
            'manager_phone' => '+94 77 234 5678',
            'code' => 'DB-COL-001'
        ]);
        $this->command->info("    ✓ Branch created: {$branch->getAttribute('name')}");

        // Create branch admin
        $branchAdmin = Admin::create([ // Create branch admin
            'email' => 'branchadmin@deliciousbites.com',
            'name' => 'Branch Admin',
            'password' => Hash::make('BranchAdmin123!'),
            'phone' => '+94 77 234 5678',
            'job_title' => 'Branch Admin',
            'department' => 'Branch Management',
            'organization_id' => $organization->getKey(), // Use getKey() for Eloquent model
            'branch_id' => $branch->getKey(), // Use getKey() for Eloquent model
            'is_super_admin' => false,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode([
                'timezone' => 'UTC',
                'language' => 'en',
                'theme' => 'light',
                'notifications' => true
            ])
        ]);
        $branchAdminRole = Role::where('name', 'Branch Administrator')->where('guard_name', 'admin')->first();
        if ($branchAdminRole) {
            $branchAdmin->assignRole($branchAdminRole);
        }
        $this->command->info('    ✓ Branch Admin created: branchadmin@deliciousbites.com');
    }

    private function createItemCategories(Organization $organization): void
    {
        $defaultCategories = [
            [
            'organization_id' => $organization->getKey(),
            'name' => 'Production Items',
            'code' => 'PI' . $organization->getKey(),
            'description' => 'Items that are produced in-house like buns, bread, etc.',
            'is_active' => true
            ],
            [
            'organization_id' => $organization->getKey(),
            'name' => 'Buy & Sell',
            'code' => 'BS' . $organization->getKey(),
            'description' => 'Items that are bought and sold directly',
            'is_active' => true
            ],
            [
            'organization_id' => $organization->getKey(),
            'name' => 'Ingredients',
            'code' => 'IG' . $organization->getKey(),
            'description' => 'Raw cooking ingredients and supplies',
            'is_active' => true
            ]
        ];
        $categories = $defaultCategories;

        foreach ($categories as $categoryData) {
            ItemCategory::create($categoryData);
        }
    }

    /**
     * Create a menu
     */
    private function createMenu(Organization $organization, Branch $branch, string $name, string $type): Menu
    {
        // Map type parameter to valid enum values
        $menuTypeMapping = [
            'morning' => 'breakfast',
            'evening' => 'dinner'
        ];

        $validMenuType = $menuTypeMapping[$type] ?? 'all_day';

        return Menu::create([
            'organization_id' => $organization->getKey(),
            'branch_id' => $branch->getKey(),
            'name' => $name,
            'description' => "Delicious {$type} options",
            'date_from' => now()->subDays(7),
            'date_to' => now()->addDays(365),
            'valid_from' => now()->subDays(7),
            'valid_until' => now()->addDays(365),
            'start_time' => $type === 'morning' ? '06:00:00' : '17:00:00',
            'end_time' => $type === 'morning' ? '12:00:00' : '23:00:00',
            'type' => $validMenuType,
            'menu_type' => 'regular',
            'is_active' => true,
            'auto_activate' => true,
            'priority' => 1
        ]);
    }

    /**
     * Create menu categories
     */
    private function createMenuCategories(Organization $organization, Branch $branch, string $menuType): array
    {
        if ($menuType === 'breakfast') {
            $categories = [
                ['name' => 'Hot Beverages', 'description' => 'Coffee, Tea, Hot Chocolate'],
                ['name' => 'Breakfast Mains', 'description' => 'Eggs, Pancakes, Toast']
            ];
        } else {
            $categories = [
                ['name' => 'Appetizers', 'description' => 'Starters and small plates'],
                ['name' => 'Main Courses', 'description' => 'Primary dinner dishes'],
                ['name' => 'Desserts', 'description' => 'Sweet endings']
            ];
        }

        $createdCategories = [];
        foreach ($categories as $index => $categoryData) {
            $category = MenuCategory::create([
                'organization_id' => $organization->getKey(),
                'branch_id' => $branch->getKey(),
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'sort_order' => $index + 1,
                'display_order' => $index + 1,
                'is_active' => true,
                'is_featured' => $index === 0
            ]);
            $createdCategories[] = $category;
        }

        return $createdCategories;
    }
}
