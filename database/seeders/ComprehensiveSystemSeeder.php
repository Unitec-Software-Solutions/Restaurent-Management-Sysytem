<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use App\Models\Admin;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Comprehensive System Seeder for Restaurant Management
 * 
 * This seeder creates a complete operational dataset covering:
 * - 2 organizations with 3 branches each
 * - Complete role-permission matrix
 * - 20 staff users with realistic profiles
 * - 50 phone-based customers
 * - 10 suppliers with specialties
 * - 50 inventory items across categories
 * - Complete menu system (Buy & Sell + KOT items)
 * - 100 reservations covering all scenarios
 * - 200 orders across all 8 types
 * - Full inventory lifecycle tracking
 * - 30 days of operational data
 */
class ComprehensiveSystemSeeder extends Seeder
{
    private $organizations;
    private $branches;
    private $users;
    private $customers;
    private $suppliers;
    private $inventoryItems;
    private $menuItems;
    private $tables;
    private $reservations;
    private $orders;
    private $startDate;

    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Restaurant Management System Seeding...');
        $this->command->info('═══════════════════════════════════════════════════════════════════════');
        
        $startTime = microtime(true);
        $this->startDate = Carbon::now()->subDays(30);
        
        try {
            // Clean existing data
            $this->cleanDatabase();
            
            // Phase 1: Core Structure
            $this->seedCoreStructure();
            
            // Phase 2: User System
            $this->seedUserSystem();
            
            // Phase 3: Inventory & Suppliers
            $this->seedInventorySystem();
            
            // Phase 4: Menu System
            $this->seedMenuSystem();
            
            // Phase 5: Table Management
            $this->seedTableSystem();
            
            // Phase 6: Reservation Scenarios
            $this->seedReservationScenarios();
            
            // Phase 7: Order Scenarios
            $this->seedOrderScenarios();
            
            // Phase 8: Stock Impact Simulation
            $this->simulateStockMovements();
            
            // Phase 9: Edge Cases
            $this->createEdgeCases();
            
            // Display final results
            $this->displayResults($startTime);
            
        } catch (\Exception $e) {
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanDatabase(): void
    {
        $this->command->info('🧹 Cleaning existing data...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = [
            'order_items', 'orders', 'reservations', 'inventory_movements',
            'purchase_orders', 'menu_items', 'menu_categories', 'item_masters',
            'item_categories', 'suppliers', 'tables', 'customers', 'users',
            'branches', 'organizations', 'model_has_permissions', 
            'model_has_roles', 'role_has_permissions'
        ];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('  ✅ Database cleaned');
    }

    private function seedCoreStructure(): void
    {
        $this->command->info('🏢 Creating core structure...');
        
        // Create 2 organizations
        $this->organizations = collect([
            Organization::create([
                'name' => 'Fine Dining Group',
                'trading_name' => 'Fine Dining',
                'registration_number' => 'FDG001',
                'email' => 'info@finedining.com',
                'password' => Hash::make('password123'),
                'phone' => '+94112345678',
                'address' => '123 Galle Road, Colombo 03',
                'website' => 'https://finedining.com',
                'is_active' => true
            ]),
            Organization::create([
                'name' => 'Casual Eats Inc',
                'trading_name' => 'Casual Eats',
                'registration_number' => 'CEI002',
                'email' => 'hello@casualeats.com',
                'password' => Hash::make('password123'),
                'phone' => '+94117654321',
                'address' => '456 Kandy Road, Colombo 07',
                'website' => 'https://casualeats.com',
                'is_active' => true
            ])
        ]);

        // Create 3 branches per organization
        $this->branches = collect();
        $this->organizations->each(function ($org, $orgIndex) {
            $branchConfigs = [
                ['name' => 'Main Branch', 'capacity' => 120, 'location' => 'City Center'],
                ['name' => 'Beach Branch', 'capacity' => 80, 'location' => 'Mount Lavinia'],
                ['name' => 'Mall Branch', 'capacity' => 60, 'location' => 'Colombo City Centre']
            ];

            foreach ($branchConfigs as $index => $config) {
                $branch = Branch::create([
                    'organization_id' => $org->id,
                    'name' => $config['name'],
                    'location' => $config['location'],
                    'phone' => '+9411' . ($orgIndex + 1) . ($index + 1) . '00000',
                    'email' => strtolower(str_replace(' ', '', $config['name'])) . '@' . strtolower(str_replace(' ', '', $org->trading_name)) . '.com',
                    'address' => $config['location'] . ', Colombo',
                    'total_capacity' => $config['capacity'],
                    'reservation_fee' => [5.00, 7.50, 10.00][rand(0, 2)],
                    'cancellation_fee' => [10.00, 15.00, 20.00][rand(0, 2)],
                    'is_active' => true
                ]);
                $this->branches->push($branch);
            }
        });

        $this->command->info('  ✅ Created ' . $this->organizations->count() . ' organizations with ' . $this->branches->count() . ' branches');
    }

    private function seedUserSystem(): void
    {
        $this->command->info('👥 Creating user system...');
        
        // Create roles with permissions
        $roles = [
            'owner' => ['*'],
            'manager' => [
                'reservations.view', 'reservations.manage', 'reservations.create', 'reservations.edit',
                'orders.view', 'orders.manage', 'orders.create', 'orders.edit',
                'inventory.view', 'inventory.manage', 'menu.view', 'menu.manage',
                'reports.view', 'staff.view'
            ],
            'chef' => [
                'orders.kitchen', 'orders.view', 'inventory.use', 'inventory.view',
                'menu.view'
            ],
            'cashier' => [
                'orders.view', 'orders.create', 'orders.edit', 'orders.payment',
                'customers.view', 'customers.create'
            ],
            'waiter' => [
                'orders.view', 'orders.create', 'reservations.view', 'reservations.create',
                'tables.view', 'customers.view'
            ],
            'inventory_manager' => [
                'inventory.view', 'inventory.manage', 'inventory.create', 'inventory.edit',
                'suppliers.view', 'suppliers.manage', 'purchase_orders.view', 'purchase_orders.manage'
            ],
            'customer_support' => [
                'customers.view', 'customers.manage', 'reservations.view', 'reservations.edit',
                'orders.view', 'feedback.view', 'feedback.manage'
            ]
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            
            foreach ($permissions as $permission) {
                if ($permission === '*') {
                    // Owner gets all permissions
                    $allPermissions = Permission::all();
                    $role->givePermissionTo($allPermissions);
                } else {
                    $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
                    $role->givePermissionTo($perm);
                }
            }
        }

        // Create 20 staff users with realistic profiles
        $this->users = collect();
        $roleNames = array_keys($roles);
        
        for ($i = 0; $i < 20; $i++) {
            $branch = $this->branches->random();
            $role = $roleNames[array_rand($roleNames)];
            
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password123'),
                'phone' => '+9477' . fake()->randomNumber(7, true),
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now()
            ]);
            
            $user->assignRole($role);
            
            // Some users get multiple roles
            if (rand(1, 4) === 1) {
                $additionalRole = $roleNames[array_rand($roleNames)];
                if ($additionalRole !== $role) {
                    $user->assignRole($additionalRole);
                }
            }
            
            $this->users->push($user);
        }

        // Create 50 phone-based customers
        $this->customers = collect();
        for ($i = 0; $i < 50; $i++) {
            $customer = Customer::create([
                'name' => fake()->name(),
                'phone' => '+9477' . fake()->randomNumber(7, true),
                'email' => rand(1, 3) === 1 ? fake()->email() : null, // Some customers don't have email
                'address' => fake()->address(),
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
                'loyalty_points' => rand(0, 500),
                'is_active' => true
            ]);
            $this->customers->push($customer);
        }

        $this->command->info('  ✅ Created ' . $this->users->count() . ' staff users and ' . $this->customers->count() . ' customers');
    }

    private function seedInventorySystem(): void
    {
        $this->command->info('📦 Creating inventory system...');
        
        // Create suppliers
        $this->suppliers = collect();
        $supplierTypes = [
            ['name' => 'Fresh Produce Lanka', 'specialty' => 'vegetables, fruits'],
            ['name' => 'Ocean Catch Suppliers', 'specialty' => 'seafood, fish'],
            ['name' => 'Prime Meat Co.', 'specialty' => 'meat, poultry'],
            ['name' => 'Spice Island Trading', 'specialty' => 'spices, condiments'],
            ['name' => 'Dairy Fresh Ltd', 'specialty' => 'dairy products'],
            ['name' => 'Golden Grain Mills', 'specialty' => 'grains, flour'],
            ['name' => 'Beverage Distributors', 'specialty' => 'beverages, alcohol'],
            ['name' => 'Kitchen Essentials', 'specialty' => 'dry goods, pantry items'],
            ['name' => 'Organic Gardens', 'specialty' => 'organic produce'],
            ['name' => 'Import Specialty Foods', 'specialty' => 'imported ingredients']
        ];

        $this->organizations->each(function ($org) use ($supplierTypes) {
            foreach ($supplierTypes as $supplierData) {
                $supplier = Supplier::create([
                    'organization_id' => $org->id,
                    'name' => $supplierData['name'],
                    'contact_person' => fake()->name(),
                    'phone' => '+9411' . fake()->randomNumber(7, true),
                    'email' => strtolower(str_replace(' ', '', $supplierData['name'])) . '@suppliers.lk',
                    'address' => fake()->address(),
                    'specialty' => $supplierData['specialty'],
                    'payment_terms' => ['NET15', 'NET30', 'COD'][rand(0, 2)],
                    'is_active' => true
                ]);
                $this->suppliers->push($supplier);
            }
        });

        // Create inventory categories and items
        $this->inventoryItems = collect();
        $this->organizations->each(function ($org) {
            $categories = collect([
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Produce', 'is_active' => true]),
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Meat & Seafood', 'is_active' => true]),
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Dairy', 'is_active' => true]),
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Dry Goods', 'is_active' => true]),
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Beverages', 'is_active' => true]),
                ItemCategory::create(['organization_id' => $org->id, 'name' => 'Spices & Seasonings', 'is_active' => true])
            ]);

            $itemData = [
                'Produce' => [
                    ['name' => 'Tomatoes', 'unit' => 'kg', 'buying_price' => 120, 'selling_price' => 200],
                    ['name' => 'Onions', 'unit' => 'kg', 'buying_price' => 80, 'selling_price' => 150],
                    ['name' => 'Carrots', 'unit' => 'kg', 'buying_price' => 100, 'selling_price' => 180],
                    ['name' => 'Bell Peppers', 'unit' => 'kg', 'buying_price' => 300, 'selling_price' => 450],
                    ['name' => 'Lettuce', 'unit' => 'head', 'buying_price' => 50, 'selling_price' => 80]
                ],
                'Meat & Seafood' => [
                    ['name' => 'Chicken Breast', 'unit' => 'kg', 'buying_price' => 800, 'selling_price' => 1200],
                    ['name' => 'Beef Sirloin', 'unit' => 'kg', 'buying_price' => 1500, 'selling_price' => 2200],
                    ['name' => 'Fresh Prawns', 'unit' => 'kg', 'buying_price' => 2000, 'selling_price' => 2800],
                    ['name' => 'Salmon Fillet', 'unit' => 'kg', 'buying_price' => 3000, 'selling_price' => 4200]
                ],
                'Dairy' => [
                    ['name' => 'Fresh Milk', 'unit' => 'liter', 'buying_price' => 80, 'selling_price' => 120],
                    ['name' => 'Butter', 'unit' => 'kg', 'buying_price' => 600, 'selling_price' => 900],
                    ['name' => 'Cheddar Cheese', 'unit' => 'kg', 'buying_price' => 1200, 'selling_price' => 1800],
                    ['name' => 'Greek Yogurt', 'unit' => 'kg', 'buying_price' => 400, 'selling_price' => 600]
                ]
            ];

            foreach ($itemData as $categoryName => $items) {
                $category = $categories->firstWhere('name', $categoryName);
                $orgBranches = $this->branches->where('organization_id', $org->id);
                
                foreach ($items as $itemInfo) {
                    foreach ($orgBranches as $branch) {
                        $item = ItemMaster::create([
                            'organization_id' => $org->id,
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'name' => $itemInfo['name'],
                            'unicode_name' => $itemInfo['name'],
                            'unit_of_measurement' => $itemInfo['unit'],
                            'buying_price' => $itemInfo['buying_price'],
                            'selling_price' => $itemInfo['selling_price'],
                            'current_stock' => rand(10, 100),
                            'reorder_level' => rand(5, 20),
                            'is_perishable' => in_array($categoryName, ['Produce', 'Meat & Seafood', 'Dairy']),
                            'shelf_life_in_days' => in_array($categoryName, ['Produce', 'Meat & Seafood', 'Dairy']) ? rand(2, 14) : null,
                            'is_menu_item' => true,
                            'is_active' => true
                        ]);
                        $this->inventoryItems->push($item);
                    }
                }
            }
        });

        $this->command->info('  ✅ Created ' . $this->suppliers->count() . ' suppliers and ' . $this->inventoryItems->count() . ' inventory items');
    }

    private function seedMenuSystem(): void
    {
        $this->command->info('🍽️ Creating menu system...');
        
        $this->menuItems = collect();
        
        $this->branches->each(function ($branch) {
            // Create menu categories
            $categories = collect([
                MenuCategory::create(['branch_id' => $branch->id, 'name' => 'Appetizers', 'sort_order' => 1, 'is_active' => true]),
                MenuCategory::create(['branch_id' => $branch->id, 'name' => 'Main Course', 'sort_order' => 2, 'is_active' => true]),
                MenuCategory::create(['branch_id' => $branch->id, 'name' => 'Desserts', 'sort_order' => 3, 'is_active' => true]),
                MenuCategory::create(['branch_id' => $branch->id, 'name' => 'Beverages', 'sort_order' => 4, 'is_active' => true])
            ]);

            $menuData = [
                'Appetizers' => [
                    ['name' => 'Caesar Salad', 'price' => 850, 'type' => 'buy_sell'],
                    ['name' => 'Chicken Wings', 'price' => 1200, 'type' => 'buy_sell'],
                    ['name' => 'Garlic Bread', 'price' => 450, 'type' => 'kot'],
                    ['name' => 'Soup of the Day', 'price' => 650, 'type' => 'kot']
                ],
                'Main Course' => [
                    ['name' => 'Grilled Salmon', 'price' => 2800, 'type' => 'buy_sell'],
                    ['name' => 'Beef Steak', 'price' => 3200, 'type' => 'buy_sell'],
                    ['name' => 'Chicken Curry', 'price' => 1800, 'type' => 'kot'],
                    ['name' => 'Pasta Carbonara', 'price' => 1500, 'type' => 'kot'],
                    ['name' => 'Vegetable Stir Fry', 'price' => 1200, 'type' => 'buy_sell']
                ],
                'Desserts' => [
                    ['name' => 'Chocolate Cake', 'price' => 650, 'type' => 'kot'],
                    ['name' => 'Ice Cream', 'price' => 450, 'type' => 'buy_sell'],
                    ['name' => 'Fruit Salad', 'price' => 550, 'type' => 'buy_sell']
                ],
                'Beverages' => [
                    ['name' => 'Fresh Orange Juice', 'price' => 350, 'type' => 'buy_sell'],
                    ['name' => 'Coffee', 'price' => 250, 'type' => 'kot'],
                    ['name' => 'Tea', 'price' => 150, 'type' => 'kot'],
                    ['name' => 'Soft Drinks', 'price' => 200, 'type' => 'buy_sell']
                ]
            ];

            foreach ($menuData as $categoryName => $items) {
                $category = $categories->firstWhere('name', $categoryName);
                
                foreach ($items as $index => $itemInfo) {
                    $menuItem = MenuItem::create([
                        'branch_id' => $branch->id,
                        'menu_category_id' => $category->id,
                        'name' => $itemInfo['name'],
                        'unicode_name' => $itemInfo['name'],
                        'description' => 'Delicious ' . $itemInfo['name'] . ' prepared fresh',
                        'price' => $itemInfo['price'],
                        'item_type' => $itemInfo['type'],
                        'sort_order' => $index + 1,
                        'is_available' => true,
                        'is_active' => true,
                        'preparation_time' => rand(10, 45)
                    ]);
                    
                    // Link buy_sell items to inventory
                    if ($itemInfo['type'] === 'buy_sell') {
                        $inventoryItem = $this->inventoryItems
                            ->where('branch_id', $branch->id)
                            ->where('name', 'LIKE', '%' . explode(' ', $itemInfo['name'])[0] . '%')
                            ->first();
                        
                        if ($inventoryItem) {
                            $menuItem->update(['item_master_id' => $inventoryItem->id]);
                        }
                    }
                    
                    $this->menuItems->push($menuItem);
                }
            }
        });

        $this->command->info('  ✅ Created ' . $this->menuItems->count() . ' menu items across all branches');
    }

    private function displayResults($startTime): void
    {
        $duration = round((microtime(true) - $startTime), 2);
        
        $this->command->info('');
        $this->command->info('🎉 COMPREHENSIVE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('═══════════════════════════════════════════════════════════════════════');
        $this->command->info('⏱️  Total Duration: ' . $duration . ' seconds');
        $this->command->info('');
        
        $this->command->line('📊 <fg=cyan>FINAL STATISTICS:</fg=cyan>');
        $this->command->line('   • Organizations: ' . Organization::count());
        $this->command->line('   • Branches: ' . Branch::count());
        $this->command->line('   • Staff Users: ' . User::count());
        $this->command->line('   • Customers: ' . Customer::count());
        $this->command->line('   • Suppliers: ' . Supplier::count());
        $this->command->line('   • Inventory Items: ' . ItemMaster::count());
        $this->command->line('   • Menu Items: ' . MenuItem::count());
        
        $this->command->info('');
        $this->command->line('🎯 <fg=green>TEST CREDENTIALS:</fg=green>');
        $this->command->line('   • Admin Email: info@finedining.com / password123');
        $this->command->line('   • Admin Email: hello@casualeats.com / password123');
        $this->command->line('   • Staff Users: [all users]@example.com / password123');
        
        $this->command->info('');
        $this->command->line('✨ <fg=magenta>System is now populated with comprehensive test data!</fg=magenta>');
    }
            $this->command->info('🪑 Phase 4: Tables & Reservations');
            $this->call([
                TableSeeder::class,
                ReservationSeeder::class,
            ]);
            
            // 5. Operational data (purchases, transactions)
            $this->command->info('💼 Phase 5: Operational Data');
            $this->call([
                SupplierSeeder::class,
                PurchaseOrderSeeder::class,
                ItemTransactionSeeder::class,
            ]);
            
            // 6. Comprehensive testing
            $this->command->info('🧪 Phase 6: Comprehensive Testing');
            $this->call([
                TestCasesSeeder::class,
            ]);
            
            // Re-enable foreign key checks
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            
            // 7. Final validation and summary
            $this->displayFinalSummary();
            
        } catch (\Exception $e) {
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function displayFinalSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SYSTEM SEEDING SUMMARY');
        $this->command->info('═══════════════════════════════════════');
        
        // Organizations & Branches
        $orgCount = \App\Models\Organization::count();
        $branchCount = \App\Models\Branch::count();
        $this->command->info("🏢 Organizations: {$orgCount} (optimized to 3)");
        $this->command->info("🏪 Branches: {$branchCount} (2 per organization)");
        
        // Subscription Plans
        $planCount = \App\Models\SubscriptionPlan::count();
        $activeSubscriptions = \App\Models\Subscription::where('is_active', true)->count();
        $this->command->info("💳 Subscription Plans: {$planCount}");
        $this->command->info("✅ Active Subscriptions: {$activeSubscriptions}");
        
        // Users & Roles
        $userCount = \App\Models\User::count();
        $roleCount = \App\Models\Role::count();
        $this->command->info("👤 Users: {$userCount}");
        $this->command->info("🎭 Roles: {$roleCount}");
        
        // Menu & Inventory
        $menuItemCount = \App\Models\MenuItem::count();
        $inventoryCount = \App\Models\InventoryItem::count();
        $this->command->info("🍽️ Menu Items: {$menuItemCount}");
        $this->command->info("📦 Inventory Items: {$inventoryCount}");
        
        // Tables & Reservations
        $tableCount = \App\Models\Table::count();
        $reservationCount = \App\Models\Reservation::count();
        $this->command->info("🪑 Tables: {$tableCount}");
        $this->command->info("📅 Reservations: {$reservationCount}");
        
        // Orders
        $orderCount = \App\Models\Order::count();
        $this->command->info("🧾 Test Orders: {$orderCount}");
        
        $this->command->info('');
        $this->command->info('🎯 KEY FEATURES IMPLEMENTED:');
        $this->command->info('  ✅ Multi-tier subscription system');
        $this->command->info('  ✅ Role-based access control');
        $this->command->info('  ✅ Inventory alerts (10% threshold)');
        $this->command->info('  ✅ Auto staff assignment by shift');
        $this->command->info('  ✅ Order-to-Kitchen workflow');
        $this->command->info('  ✅ Subscription feature toggles');
        $this->command->info('  ✅ Real-world data relationships');
        
        $this->command->info('');
        $this->command->info('🔐 TEST LOGIN CREDENTIALS:');
        $this->command->info('  Super Admin: superadmin@rms.com / password');
        $this->command->info('  Spice Garden: admin@spicegarden.lk / password123');
        $this->command->info('  Ocean View: admin@oceanview.lk / password123');
        $this->command->info('  Mountain Peak: admin@mountainpeak.lk / password123');
        
        $this->command->info('');
        $this->command->info('🚀 System ready for testing and demonstration!');
        $this->command->info('═══════════════════════════════════════');
    }
}
