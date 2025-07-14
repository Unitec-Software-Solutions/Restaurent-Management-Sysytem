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
            $this->createSystemPermissions();
            
            // Step 4: Create super admin role
            $this->createSuperAdminRole();
            
            // Step 5: Create super admin user
            $this->createSuperAdmin();
            
            // Step 6: Create subscription plan
            $subscriptionPlan = $this->createSubscriptionPlan();
            
            // Step 7: Create organization
            $organization = $this->createOrganization($subscriptionPlan);
            
            // Step 8: Create branch
            $branch = $this->createBranch($organization);
            
            // Step 9: Create menu structure
            $this->createMenuStructure($organization, $branch);
            
            // Step 10: Create customers
            $this->createCustomers();
            
            // Step 11: Create tables for the branch
            $this->createTables($branch);
            
            // Step 12: Create reservations and orders
            $this->createReservationsAndOrders($branch);
        });
        
        $this->command->info('✅ Minimal system foundation created successfully');
    }

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
            $this->command->info("    ✓ Module: {$module->name}");
        }
    }

    /**
     * Create essential permissions for restaurant management system
     */
    private function createSystemPermissions(): void
    {
        $this->command->info('  🔐 Creating system permissions...');
        
        $permissions = [
            // System Administration
            'system.manage', 'system.settings', 'system.backup', 'system.logs',
            
            // Order Management
            'order.view', 'order.create', 'order.update', 'order.delete', 'order.manage',
            'order.process', 'order.cancel', 'order.refund', 'order.print_kot',
            
            // Reservation Management
            'reservation.view', 'reservation.create', 'reservation.update', 'reservation.delete',
            'reservation.manage', 'reservation.approve', 'reservation.cancel', 'reservation.checkin',
            
            // Inventory Management
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete',
            'inventory.manage', 'inventory.adjust', 'inventory.transfer', 'inventory.audit',
            
            // Menu Management
            'menu.view', 'menu.create', 'menu.update', 'menu.delete', 'menu.manage',
            'menu.categories', 'menu.pricing', 'menu.schedule', 'menu.publish',
            
            // Customer Management
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'customer.manage', 'customer.loyalty', 'customer.communications',
            
            // Kitchen Operations
            'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.orders',
            'kitchen.status', 'kitchen.recipes', 'kitchen.production',
            'kot.view', 'kot.create', 'kot.update', 'kot.manage', 'kot.print',
            
            // Reports & Analytics
            'report.view', 'report.generate', 'report.export', 'report.sales',
            'report.inventory', 'report.staff', 'report.financial', 'report.dashboard',
            
            // Organization & Branch Management
            'organization.view', 'organization.create', 'organization.update', 'organization.manage',
            'branch.view', 'branch.create', 'branch.update', 'branch.manage',
            
            // User Management
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.manage',
            'role.view', 'role.create', 'role.update', 'role.delete', 'role.manage',
            'permission.view', 'permission.manage',
            
            // Staff Management
            'staff.view', 'staff.create', 'staff.update', 'staff.delete', 'staff.manage',
            'staff.schedule', 'staff.attendance', 'staff.performance',
            
            // Financial Management
            'payment.view', 'payment.process', 'payment.refund', 'payment.manage',
            'billing.view', 'billing.create', 'billing.manage',
            
            // Dashboard & Profile
            'dashboard.view', 'dashboard.manage', 'profile.view', 'profile.update'
        ];

        $created = 0;
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
            $created++;
        }

        $this->command->info("    ✓ Created {$created} permissions");
    }

    /**
     * Create super admin role with all permissions
     */
    private function createSuperAdminRole(): void
    {
        $this->command->info('  👑 Creating super admin role...');
        
        // Create Super Admin role
        $superAdminRole = Role::create([
            'name' => 'Super Administrator',
            'guard_name' => 'admin'
        ]);

        // Assign ALL permissions to Super Admin
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);
        
        $this->command->info("    ✓ Super Admin role created with {$allPermissions->count()} permissions");
    }

    /**
     * Create super admin user
     */
    private function createSuperAdmin(): void
    {
        $this->command->info('  🔑 Creating super admin user...');
        
        // Create super admin user (system level - no organization)
        $superAdmin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@rms.com',
            'password' => Hash::make('SuperAdmin123!'),
            'phone' => '+94 11 000 0000',
            'job_title' => 'System Administrator',
            'department' => 'System Administration',
            'organization_id' => null, // System level admin
            'branch_id' => null,
            'is_super_admin' => true,
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

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Administrator')
            ->where('guard_name', 'admin')
            ->first();
            
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info('    ✓ Super Admin user created');
        $this->command->info('    📧 Email: superadmin@rms.com');
        $this->command->info('    🔒 Password: SuperAdmin123!');
        $this->command->info('    🏢 Organization: System Level (No Organization)');
        $this->command->info('    ⚡ Permissions: All System Permissions');
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
            'modules' => [1, 2, 3, 4, 5, 6, 7, 8], // All module IDs
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
            'subscription_plan_id' => $subscriptionPlan->id,
            'discount_percentage' => 5.00,
            'is_active' => true,
            'activated_at' => now(),
            'password' => Hash::make('DeliciousBites123!')
        ]);

        $this->command->info("    ✓ Organization created: {$organization->name}");
        return $organization;
    }

    /**
     * Create sample branch
     */
    private function createBranch(Organization $organization): Branch
    {
        $this->command->info('  🏪 Creating sample branch...');
        
        $branch = Branch::create([
            'organization_id' => $organization->id,
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

        $this->command->info("    ✓ Branch created: {$branch->name}");
        return $branch;
    }

    /**
     * Create menu structure with categories and items
     */
    private function createMenuStructure(Organization $organization, Branch $branch): void
    {
        $this->command->info('  📋 Creating menu structure...');
        
        // Create item categories for inventory
        $this->createItemCategories($organization);
        
        // Create 2 menus
        $breakfastMenu = $this->createMenu($organization, $branch, 'Breakfast Menu', 'morning');
        $dinnerMenu = $this->createMenu($organization, $branch, 'Dinner Menu', 'evening');
        
        // Create menu categories for each menu
        $breakfastCategories = $this->createMenuCategories($organization, $branch, 'breakfast');
        $dinnerCategories = $this->createMenuCategories($organization, $branch, 'dinner');
        
        // Create menu items for breakfast menu
        $this->createMenuItems($organization, $branch, $breakfastCategories, 'breakfast');
        
        // Create menu items for dinner menu  
        $this->createMenuItems($organization, $branch, $dinnerCategories, 'dinner');
        
        $this->command->info('    ✓ Menu structure created with 2 menus and 10 items total');
    }

    /**
     * Create item categories for inventory
     */
    private function createItemCategories(Organization $organization): void
    {
        $defaultCategories = [
            [
            'organization_id' => $organization->id,
            'name' => 'Production Items',
            'code' => 'PI' . $organization->id,
            'description' => 'Items that are produced in-house like buns, bread, etc.',
            'is_active' => true
            ],
            [
            'organization_id' => $organization->id,
            'name' => 'Buy & Sell',
            'code' => 'BS' . $organization->id,
            'description' => 'Items that are bought and sold directly',
            'is_active' => true
            ],
            [
            'organization_id' => $organization->id,
            'name' => 'Ingredients',
            'code' => 'IG' . $organization->id,
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
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => $name,
            'description' => "Delicious {$type} options",
            'date_from' => now()->subDays(7),
            'date_to' => now()->addDays(365),
            'valid_from' => now()->subDays(7),
            'valid_until' => now()->addDays(365),
            'start_time' => $type === 'morning' ? '06:00:00' : '17:00:00',
            'end_time' => $type === 'morning' ? '12:00:00' : '23:00:00',
            'type' => $validMenuType, // Use valid menu type from enum
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
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
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

    /**
     * Create menu items
     */
    private function createMenuItems(Organization $organization, Branch $branch, array $categories, string $menuType): void
    {
        // For KOT items, we don't need to link to item_master (they are recipe-based, not inventory items)
        // $itemCategory = ItemCategory::where('organization_id', $organization->id)->first();
        
        if ($menuType === 'breakfast') {
            $items = [
                // Hot Beverages
                [
                    'category_id' => $categories[0]->id,
                    'name' => 'Cappuccino',
                    'description' => 'Rich espresso with steamed milk foam',
                    'price' => 450.00
                ],
                [
                    'category_id' => $categories[0]->id,
                    'name' => 'Ceylon Black Tea',
                    'description' => 'Premium Sri Lankan black tea',
                    'price' => 300.00
                ],
                // Breakfast Mains
                [
                    'category_id' => $categories[1]->id,
                    'name' => 'Classic Eggs Benedict',
                    'description' => 'Poached eggs on English muffin with hollandaise',
                    'price' => 1250.00
                ],
                [
                    'category_id' => $categories[1]->id,
                    'name' => 'Fluffy Pancakes',
                    'description' => 'Stack of 3 pancakes with maple syrup',
                    'price' => 950.00
                ],
                [
                    'category_id' => $categories[1]->id,
                    'name' => 'Avocado Toast',
                    'description' => 'Smashed avocado on sourdough with poached egg',
                    'price' => 850.00
                ]
            ];
        } else {
            $items = [
                // Appetizers
                [
                    'category_id' => $categories[0]->id,
                    'name' => 'Crispy Calamari',
                    'description' => 'Golden fried squid rings with spicy mayo',
                    'price' => 1150.00
                ],
                [
                    'category_id' => $categories[0]->id,
                    'name' => 'Bruschetta Trio',
                    'description' => 'Three varieties of Italian bruschetta',
                    'price' => 950.00
                ],
                // Main Courses
                [
                    'category_id' => $categories[1]->id,
                    'name' => 'Grilled Salmon',
                    'description' => 'Atlantic salmon with lemon butter sauce',
                    'price' => 2450.00
                ],
                [
                    'category_id' => $categories[1]->id,
                    'name' => 'Beef Tenderloin',
                    'description' => 'Premium beef with roasted vegetables',
                    'price' => 3250.00
                ],
                // Desserts
                [
                    'category_id' => $categories[2]->id,
                    'name' => 'Chocolate Lava Cake',
                    'description' => 'Warm chocolate cake with molten center',
                    'price' => 750.00
                ]
            ];
        }

        foreach ($items as $index => $itemData) {
            MenuItem::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'menu_category_id' => $itemData['category_id'],
                'item_master_id' => null, // KOT items don't need item_master link (they are recipe-based)
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'item_code' => strtoupper(substr($itemData['name'], 0, 3)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'price' => $itemData['price'],
                'cost_price' => $itemData['price'] * 0.6, // 40% markup
                'currency' => 'LKR',
                'type' => MenuItem::TYPE_KOT,
                'preparation_time' => rand(10, 30),
                'calories' => rand(200, 800),
                'spice_level' => MenuItem::SPICE_MILD,
                'is_active' => true,
                'is_available' => true,
                'is_featured' => $index < 2,
                'sort_order' => $index + 1,
                'display_order' => $index + 1
            ]);
        }
    }

    /**
     * Create sample customers
     */
    private function createCustomers(): void
    {
        $this->command->info('  👥 Creating sample customers...');
        
        $customers = [
            [
                'name' => 'Alice Johnson',
                'phone' => '+94771234567',
                'email' => 'alice.johnson@email.com',
                'preferred_contact' => 'email',
                'loyalty_points' => 150,
                'is_active' => true
            ],
            [
                'name' => 'Bob Smith',
                'phone' => '+94772234567',
                'email' => 'bob.smith@email.com',
                'preferred_contact' => 'sms',
                'loyalty_points' => 75,
                'is_active' => true
            ],
            [
                'name' => 'Carol Williams',
                'phone' => '+94773234567',
                'email' => 'carol.williams@email.com',
                'preferred_contact' => 'email',
                'loyalty_points' => 200,
                'is_active' => true
            ]
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        $this->command->info('    ✓ Created 3 sample customers');
    }

    /**
     * Create tables for the branch
     */
    private function createTables(Branch $branch): void
    {
        $this->command->info('  🪑 Creating tables...');
        
        $tables = [
            ['number' => 'T001', 'capacity' => 2],
            ['number' => 'T002', 'capacity' => 4],
            ['number' => 'T003', 'capacity' => 6],
            ['number' => 'T004', 'capacity' => 4],
            ['number' => 'T005', 'capacity' => 8]
        ];

        foreach ($tables as $tableData) {
            Table::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'number' => $tableData['number'],
                'capacity' => $tableData['capacity'],
                'is_active' => true,
                'location' => 'Main Dining Area'
            ]);
        }

        $this->command->info('    ✓ Created 5 tables');
    }

    /**
     * Create reservations and orders
     */
    private function createReservationsAndOrders(Branch $branch): void
    {
        $this->command->info('  📝 Creating reservations and orders...');
        
        $customers = Customer::take(2)->get();
        $menuItems = MenuItem::where('branch_id', $branch->id)->take(3)->get();
        $tables = Table::where('branch_id', $branch->id)->take(2)->get();

        // Create 2 reservations
        foreach ($customers as $index => $customer) {
            $reservationDate = now()->addDays($index + 1);
            
            $reservation = Reservation::create([
                'name' => $customer->name,
                'phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'email' => $customer->email,
                'type' => $index === 0 ? ReservationType::ONLINE : ReservationType::IN_CALL,
                'table_size' => $index === 0 ? 2 : 4,
                'date' => $reservationDate->toDateString(),
                'start_time' => $reservationDate->setHour(19)->setMinute(0),
                'end_time' => $reservationDate->setHour(21)->setMinute(0),
                'number_of_people' => $index === 0 ? 2 : 4,
                'comments' => $index === 0 ? 'Anniversary dinner' : 'Business meeting',
                'reservation_fee' => $branch->reservation_fee,
                'status' => 'confirmed',
                'branch_id' => $branch->id,
                'assigned_table_ids' => [$tables[$index]->id]
            ]);

            // Create order for each reservation
            $this->createOrderForReservation($reservation, $branch, $menuItems, $customer);
        }

        $this->command->info('    ✓ Created 2 reservations and 2 orders');
    }

    /**
     * Create an order for a reservation
     */
    private function createOrderForReservation(Reservation $reservation, Branch $branch, $menuItems, Customer $customer): void
    {
        $order = Order::create([
            'reservation_id' => $reservation->id,
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_phone_fk' => $customer->phone,
            'customer_email' => $customer->email,
            'order_type' => OrderType::DINE_IN_ONLINE_SCHEDULED,
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'payment_method' => Order::PAYMENT_METHOD_CASH,
            'notes' => 'Created by seeder',
            'currency' => 'LKR',
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 0.00,
            'order_date' => now()
        ]);

        // Add order items
        $subtotal = 0;
        foreach ($menuItems->take(2) as $menuItem) {
            $quantity = rand(1, 2);
            $unitPrice = $menuItem->price;
            $totalPrice = $unitPrice * $quantity;
            
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $totalPrice,
                'total_price' => $totalPrice,
                'special_instructions' => $quantity > 1 ? 'Extra portion' : null
            ]);
            
            $subtotal += $totalPrice;
        }

        // Update order totals
        $tax = $subtotal * 0.12; // 12% tax
        $total = $subtotal + $tax;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'tax' => $tax,
            'total' => $total
        ]);
    }
}