<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// Models
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Branch;
use App\Models\User;
use App\Models\Employee;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Supplier;

class SimpleOptimizedSeeder extends Seeder
{
    /**
     * Run optimized seeding for the restaurant management system
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Simple Optimized Restaurant Management System Seeding...');
        $this->command->newLine();

        // Clean existing data
        $this->cleanupData();
        
        // Seed core data
        $subscriptionPlans = $this->seedSubscriptionPlans();
        $organizations = $this->seedOrganizations($subscriptionPlans);
        $branches = $this->seedBranches($organizations);
        $users = $this->seedUsers($organizations, $branches);
        $employees = $this->seedEmployees($organizations, $branches);
        
        // Seed restaurant operational data
        $itemCategories = $this->seedItemCategories($organizations);
        $items = $this->seedItems($organizations, $branches, $itemCategories);
        $menuCategories = $this->seedMenuCategories($organizations);
        $menuItems = $this->seedMenuItems($organizations, $menuCategories, $items);
        $tables = $this->seedTables($branches);
        $suppliers = $this->seedSuppliers($organizations);
        
        // Create sample orders and reservations
        $orders = $this->seedOrders($branches, $tables, $users);
        $reservations = $this->seedReservations($branches, $tables);
        
        $this->displayResults();
    }    private function cleanupData(): void
    {
        $this->command->info('🧹 Cleaning up existing data...');
        
        // For PostgreSQL, disable triggers temporarily
        DB::statement('SET session_replication_role = replica');
        
        $tables = [
            'order_items', 'orders', 'reservations',
            'menu_items', 'menu_categories', 
            'item_masters', 'item_categories',
            'tables', 'suppliers',
            'employees', 'users',
            'subscriptions', 'branches', 'organizations',
            'subscription_plans'
        ];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        // Re-enable triggers
        DB::statement('SET session_replication_role = DEFAULT');
        
        $this->command->info('  ✅ Data cleaned');
    }

    private function seedSubscriptionPlans(): array
    {
        $this->command->info('📋 Seeding subscription plans...');
        
        $plans = [
            [
                'name' => 'Basic',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'basic'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                ],
                'price' => 0,
                'currency' => 'LKR',
                'description' => 'Basic plan - POS and basic kitchen',
            ],
            [
                'name' => 'Pro',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'premium'],
                    ['name' => 'inventory', 'tier' => 'premium'],
                    ['name' => 'staff', 'tier' => 'premium'],
                ],
                'price' => 5000,
                'currency' => 'LKR',
                'description' => 'Pro plan - All core features',
            ],
            [
                'name' => 'Enterprise',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'enterprise'],
                    ['name' => 'kitchen', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'staff', 'tier' => 'enterprise'],
                    ['name' => 'analytics', 'tier' => 'enterprise'],
                ],
                'price' => 15000,
                'currency' => 'LKR',
                'description' => 'Enterprise plan - All features + analytics',
            ]
        ];

        $subscriptionPlans = [];
        foreach ($plans as $planData) {
            $plan = SubscriptionPlan::create($planData);
            $subscriptionPlans[] = $plan;
        }

        $this->command->info('  ✅ ' . count($subscriptionPlans) . ' subscription plans seeded');
        return $subscriptionPlans;
    }

    private function seedOrganizations(array $subscriptionPlans): array
    {
        $this->command->info('🏢 Seeding organizations (3 organizations)...');
        
        $organizationData = [
            [
                'name' => 'Spice Garden Restaurant',
                'email' => 'admin@spicegarden.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 234 5678',
                'address' => '123 Galle Road, Colombo 03',
                'is_active' => true,
                'subscription_plan_id' => $subscriptionPlans[2]->id, // Enterprise
            ],
            [
                'name' => 'Ocean View Cafe',
                'email' => 'admin@oceanview.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 31 567 8901',
                'address' => '456 Marine Drive, Galle',
                'is_active' => true,
                'subscription_plan_id' => $subscriptionPlans[1]->id, // Pro
            ],
            [
                'name' => 'Hill Country Kitchen',
                'email' => 'admin@hillkitchen.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 81 222 3344',
                'address' => '789 Kandy Road, Kandy',
                'is_active' => true,
                'subscription_plan_id' => $subscriptionPlans[0]->id, // Basic
            ]
        ];

        $organizations = [];
        foreach ($organizationData as $data) {
            $organization = Organization::create($data);
            
            // Create active subscription
            Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $organization->subscription_plan_id,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addYear(),
                'is_active' => true,
                'activated_at' => Carbon::now()->subDays(30),
            ]);
            
            $organizations[] = $organization;
            $this->command->info("    ✅ Created organization: {$organization->name}");
        }

        return $organizations;
    }

    private function seedBranches(array $organizations): array
    {
        $this->command->info('🏪 Seeding branches (2 per organization)...');
        
        $branchMappings = [
            'Spice Garden Restaurant' => [
                ['name' => 'Spice Garden Main', 'address' => '123 Galle Road, Colombo 03', 'phone' => '+94 11 234 5678'],
                ['name' => 'Spice Garden Dehiwala', 'address' => '456 Galle Road, Dehiwala', 'phone' => '+94 11 234 5679'],
            ],
            'Ocean View Cafe' => [
                ['name' => 'Ocean View Main', 'address' => '456 Marine Drive, Galle', 'phone' => '+94 31 567 8901'],
                ['name' => 'Ocean View Beach', 'address' => '789 Beach Road, Galle', 'phone' => '+94 31 567 8902'],
            ],
            'Hill Country Kitchen' => [
                ['name' => 'Hill Kitchen Main', 'address' => '789 Kandy Road, Kandy', 'phone' => '+94 81 222 3344'],
                ['name' => 'Hill Kitchen Peradeniya', 'address' => '101 Peradeniya Road, Kandy', 'phone' => '+94 81 222 3345'],
            ]
        ];

        $branches = [];
        foreach ($organizations as $organization) {
            $branchData = $branchMappings[$organization->name];
            
            foreach ($branchData as $data) {                $branch = Branch::create(array_merge($data, [
                    'organization_id' => $organization->id,
                    'is_active' => true,
                    'opening_time' => '10:00:00',
                    'closing_time' => '23:00:00',
                    'total_capacity' => rand(60, 120),
                    'reservation_fee' => rand(200, 500) / 100 * 100, // 200-500 LKR rounded to nearest 100
                    'cancellation_fee' => rand(100, 300) / 100 * 100, // 100-300 LKR rounded to nearest 100
                ]));
                
                $branches[] = $branch;
                $this->command->info("    ✅ Created branch: {$branch->name}");
            }
        }

        return $branches;
    }

    private function seedUsers(array $organizations, array $branches): array
    {
        $this->command->info('👤 Seeding users...');
        
        $users = [];
        
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@rms.com',
            'password' => Hash::make('password123'),
            'is_super_admin' => true,
            'is_admin' => true,
            'is_active' => true,
            'is_registered' => true,
        ]);
        $users[] = $superAdmin;

        // Organization admins
        $adminEmails = [
            'admin1@spicegarden.com',
            'admin2@oceanview.com', 
            'admin3@hillkitchen.com'
        ];

        foreach ($organizations as $index => $organization) {
            $admin = User::create([
                'name' => "Admin {$organization->name}",
                'email' => $adminEmails[$index],
                'password' => Hash::make('password123'),
                'organization_id' => $organization->id,
                'is_admin' => true,
                'is_active' => true,
                'is_registered' => true,
            ]);
            $users[] = $admin;

            // Branch staff
            $orgBranches = array_filter($branches, fn($b) => $b->organization_id === $organization->id);
            foreach ($orgBranches as $branch) {
                $staffTypes = ['Manager', 'Chef', 'Cashier', 'Waiter'];
                foreach ($staffTypes as $staffType) {
                    $user = User::create([
                        'name' => "{$staffType} - {$branch->name}",
                        'email' => strtolower($staffType) . ".{$branch->id}@" . strtolower(str_replace(' ', '', $organization->name)) . ".com",
                        'password' => Hash::make('password123'),
                        'organization_id' => $organization->id,
                        'branch_id' => $branch->id,
                        'is_active' => true,
                        'is_registered' => true,
                    ]);
                    $users[] = $user;
                }
            }
        }

        $this->command->info('  ✅ ' . count($users) . ' users seeded');
        return $users;
    }

    private function seedEmployees(array $organizations, array $branches): array
    {
        $this->command->info('👥 Seeding employees...');
        
        $employees = [];
        $employeeTypes = [
            ['role' => 'manager', 'shift' => 'morning'],
            ['role' => 'chef', 'shift' => 'morning'],
            ['role' => 'chef', 'shift' => 'evening'],
            ['role' => 'waiter', 'shift' => 'morning'],
            ['role' => 'waiter', 'shift' => 'evening'],
            ['role' => 'cashier', 'shift' => 'morning'],
        ];

        foreach ($branches as $branch) {
            foreach ($employeeTypes as $index => $empType) {                $firstName = $this->getRandomName();
                $lastName = $this->getRandomLastName();
                $empId = "EMP{$branch->id}" . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $employee = Employee::create([
                    'organization_id' => $branch->organization_id,
                    'branch_id' => $branch->id,
                    'emp_id' => $empId,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => strtolower($firstName . '.' . $lastName . '.' . $empId . '@' . str_replace(' ', '', $branch->organization->name) . '.com'),
                    'phone' => '+94 7' . rand(10000000, 99999999),
                    'role' => $empType['role'],
                    'joined_date' => Carbon::now()->subMonths(rand(1, 24)),
                    'is_active' => true,
                ]);
                
                $employees[] = $employee;
            }
        }

        $this->command->info('  ✅ ' . count($employees) . ' employees seeded');
        return $employees;
    }

    private function seedItemCategories(array $organizations): array
    {
        $this->command->info('🏷️ Seeding item categories...');
        
        $categories = [
            ['name' => 'Main Course', 'code' => 'MC'],
            ['name' => 'Beverages', 'code' => 'BV'],
            ['name' => 'Desserts', 'code' => 'DS'],
            ['name' => 'Ingredients', 'code' => 'ING'],
        ];

        $itemCategories = [];
        foreach ($organizations as $organization) {
            foreach ($categories as $category) {
                $itemCategory = ItemCategory::create([
                    'name' => $category['name'],
                    'code' => $category['code'],
                    'organization_id' => $organization->id,
                    'is_active' => true,
                ]);
                
                $itemCategories[] = $itemCategory;
            }
        }

        $this->command->info('  ✅ ' . count($itemCategories) . ' item categories seeded');
        return $itemCategories;
    }

    private function seedItems(array $organizations, array $branches, array $itemCategories): array
    {
        $this->command->info('📦 Seeding items...');
        
        $itemTemplates = [
            ['name' => 'Chicken Curry', 'category' => 'Main Course', 'price' => 850, 'cost' => 400],
            ['name' => 'Fish Curry', 'category' => 'Main Course', 'price' => 950, 'cost' => 500],
            ['name' => 'Vegetable Rice', 'category' => 'Main Course', 'price' => 650, 'cost' => 200],
            ['name' => 'Fresh Lime Juice', 'category' => 'Beverages', 'price' => 250, 'cost' => 80],
            ['name' => 'King Coconut', 'category' => 'Beverages', 'price' => 300, 'cost' => 100],
            ['name' => 'Ice Cream', 'category' => 'Desserts', 'price' => 350, 'cost' => 120],
            ['name' => 'Rice', 'category' => 'Ingredients', 'price' => 0, 'cost' => 150], // Low stock item
            ['name' => 'Chicken', 'category' => 'Ingredients', 'price' => 0, 'cost' => 800], // Low stock item
        ];

        $items = [];
        foreach ($branches as $branch) {
            foreach ($itemTemplates as $index => $template) {
                $category = collect($itemCategories)->first(fn($cat) => 
                    $cat->organization_id === $branch->organization_id && 
                    $cat->name === $template['category']
                );

                if (!$category) continue;

                $item = ItemMaster::create([
                    'organization_id' => $branch->organization_id,
                    'branch_id' => $branch->id,
                    'item_category_id' => $category->id,
                    'name' => $template['name'],
                    'item_code' => 'ITM' . $branch->id . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'buying_price' => $template['cost'],
                    'selling_price' => $template['price'],
                    'unit_of_measurement' => $template['price'] > 0 ? 'plate' : 'kg',
                    'reorder_level' => $template['name'] === 'Rice' ? 50 : ($template['name'] === 'Chicken' ? 80 : 20),
                    'is_menu_item' => $template['price'] > 0,
                    'is_active' => true,
                ]);

                $items[] = $item;
            }
        }

        $this->command->info('  ✅ ' . count($items) . ' items seeded');
        return $items;
    }

    private function seedMenuCategories(array $organizations): array
    {
        $this->command->info('🍽️ Seeding menu categories...');
        
        $categories = ['Starters', 'Main Courses', 'Beverages', 'Desserts'];        $menuCategories = [];
        foreach ($categories as $index => $categoryName) {
            $menuCategory = MenuCategory::create([
                'name' => $categoryName,
                'description' => "Category for {$categoryName}",
                'display_order' => $index + 1,
                'is_active' => true,
            ]);
            
            $menuCategories[] = $menuCategory;
        }

        $this->command->info('  ✅ ' . count($menuCategories) . ' menu categories seeded');
        return $menuCategories;
    }    private function seedMenuItems(array $organizations, array $menuCategories, array $items): array
    {
        $this->command->info('🍽️ Seeding menu items...');
        
        $menuItems = [];
        
        foreach ($organizations as $organization) {
            $orgItems = array_filter($items, fn($item) => 
                $item->organization_id === $organization->id && $item->is_menu_item
            );

            foreach ($orgItems as $item) {
                $menuCategory = collect($menuCategories)->random();
                
                $menuItem = MenuItem::create([
                    'name' => $item->name,
                    'description' => "Delicious {$item->name}",
                    'price' => $item->selling_price,
                    'menu_category_id' => $menuCategory->id,
                    'is_available' => true,
                    'preparation_time' => rand(10, 25),
                    'is_vegetarian' => rand(0, 1),
                    'station' => rand(0, 1) ? 'kitchen' : 'bar',
                ]);
                
                $menuItems[] = $menuItem;
            }
        }

        $this->command->info('  ✅ ' . count($menuItems) . ' menu items seeded');
        return $menuItems;
    }

    private function seedTables(array $branches): array
    {
        $this->command->info('🪑 Seeding tables...');
        
        $tables = [];
        foreach ($branches as $branch) {
            $tableCount = rand(8, 15);
            
            for ($i = 1; $i <= $tableCount; $i++) {
                $table = Table::create([
                    'branch_id' => $branch->id,
                    'number' => $i,
                    'capacity' => rand(2, 6),
                    'status' => 'available',
                ]);
                
                $tables[] = $table;
            }
        }

        $this->command->info('  ✅ ' . count($tables) . ' tables seeded');
        return $tables;
    }

    private function seedSuppliers(array $organizations): array
    {
        $this->command->info('🚚 Seeding suppliers...');
        
        $supplierTypes = ['Vegetables', 'Meat & Seafood', 'Beverages', 'Dairy'];

        $suppliers = [];
        foreach ($organizations as $organization) {
            foreach ($supplierTypes as $index => $type) {
                $supplier = Supplier::create([
                    'organization_id' => $organization->id,
                    'supplier_id' => 'SUP' . $organization->id . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'name' => "{$type} Supplier",
                    'contact_person' => $this->getRandomName() . ' ' . $this->getRandomLastName(),
                    'phone' => '+94 7' . rand(10000000, 99999999),
                    'email' => strtolower(str_replace(' ', '', $type)) . '@supplier.lk',
                    'address' => 'Supplier Address, Colombo',
                    'is_active' => true,
                ]);
                
                $suppliers[] = $supplier;
            }
        }

        $this->command->info('  ✅ ' . count($suppliers) . ' suppliers seeded');
        return $suppliers;
    }

    private function seedOrders(array $branches, array $tables, array $users): array
    {
        $this->command->info('🛒 Seeding orders...');
        
        $orders = [];
        
        foreach ($branches as $branch) {
            $branchTables = array_filter($tables, fn($t) => $t->branch_id === $branch->id);
            $branchUsers = array_filter($users, fn($u) => $u->branch_id === $branch->id);
            
            if (empty($branchTables) || empty($branchUsers)) continue;
            
            // Create 5-10 orders per branch
            $orderCount = rand(5, 10);
            for ($i = 0; $i < $orderCount; $i++) {
                $table = collect($branchTables)->random();
                $user = collect($branchUsers)->random();
                  $order = Order::create([
                    'branch_id' => $branch->id,
                    'customer_name' => $this->getRandomName() . ' ' . $this->getRandomLastName(),
                    'customer_phone' => '+94 7' . rand(10000000, 99999999),
                    'order_type' => 'dine_in_walk_in_demand',
                    'status' => collect(['active', 'preparing', 'ready', 'served', 'completed'])->random(),
                    'total' => rand(800, 2500),
                    'order_date' => Carbon::now()->subDays(rand(0, 7)),
                ]);
                
                $orders[] = $order;
            }
        }

        $this->command->info('  ✅ ' . count($orders) . ' orders seeded');
        return $orders;
    }

    private function seedReservations(array $branches, array $tables): array
    {
        $this->command->info('📅 Seeding reservations...');
        
        $reservations = [];
        
        foreach ($branches as $branch) {
            $branchTables = array_filter($tables, fn($t) => $t->branch_id === $branch->id);
            
            if (empty($branchTables)) continue;
            
            // Create 3-6 reservations per branch for next 14 days
            for ($day = 1; $day <= 14; $day++) {
                $reservationDate = Carbon::now()->addDays($day);
                $reservationsPerDay = rand(3, 6);
                
                for ($resNum = 0; $resNum < $reservationsPerDay; $resNum++) {
                    $table = collect($branchTables)->random();
                    $startTime = Carbon::createFromTime(rand(18, 21), [0, 30][rand(0, 1)], 0);
                      $reservation = Reservation::create([
                        'branch_id' => $branch->id,
                        'name' => $this->getRandomName() . ' ' . $this->getRandomLastName(),
                        'phone' => '+94 7' . rand(10000000, 99999999),
                        'email' => strtolower($this->getRandomName()) . '@email.com',
                        'date' => $reservationDate->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $startTime->addHours(2)->format('H:i:s'),
                        'number_of_people' => rand(2, $table->capacity),
                        'status' => collect(['pending', 'confirmed', 'cancelled'])->random(),
                        'assigned_table_ids' => json_encode([$table->id]),
                        'reservation_fee' => 500,
                        'cancellation_fee' => 250,
                    ]);
                    
                    $reservations[] = $reservation;
                }
            }
        }

        $this->command->info('  ✅ ' . count($reservations) . ' reservations seeded');
        return $reservations;
    }

    private function displayResults(): void
    {
        $this->command->newLine();
        $this->command->getOutput()->writeln('<fg=white;bg=green> ✅ OPTIMIZED SEEDING COMPLETED! </fg=white;bg=green>');
        $this->command->newLine();
        
        $this->command->info('📊 <fg=cyan>Final Statistics:</fg=cyan>');
        $this->command->line('   • Organizations: ' . Organization::count());
        $this->command->line('   • Subscription Plans: ' . SubscriptionPlan::count());
        $this->command->line('   • Active Subscriptions: ' . Subscription::where('is_active', true)->count());
        $this->command->line('   • Branches: ' . Branch::count());
        $this->command->line('   • Users: ' . User::count());
        $this->command->line('   • Employees: ' . Employee::count());
        $this->command->line('   • Menu Items: ' . MenuItem::count());
        $this->command->line('   • Orders: ' . Order::count());
        $this->command->line('   • Reservations: ' . Reservation::count());
        $this->command->line('   • Suppliers: ' . Supplier::count());
        
        $this->command->newLine();
        $this->command->line('<fg=green>🔐 Login Credentials:</fg=green>');
        $this->command->line('   • Super Admin: superadmin@rms.com / password123');
        $this->command->line('   • Enterprise: admin1@spicegarden.com / password123');
        $this->command->line('   • Pro: admin2@oceanview.com / password123');
        $this->command->line('   • Basic: admin3@hillkitchen.com / password123');
        
        $this->command->newLine();
        $this->command->line('<fg=cyan>🏢 Organizations:</fg=cyan>');
        $this->command->line('   1. Spice Garden Restaurant (Enterprise Plan)');
        $this->command->line('   2. Ocean View Cafe (Pro Plan)'); 
        $this->command->line('   3. Hill Country Kitchen (Basic Plan)');
        
        $this->command->newLine();
        $this->command->line('<fg=yellow>🎯 Ready for Testing:</fg=yellow>');
        $this->command->line('   • Different subscription tier access');
        $this->command->line('   • Order workflow testing');
        $this->command->line('   • Role-based permissions');
        $this->command->line('   • Inventory management (Pro+ only)');
        $this->command->line('   • Advanced analytics (Enterprise only)');
        
        $this->command->newLine();
    }

    private function getRandomName(): string
    {
        $names = ['Kumara', 'Nimal', 'Sunil', 'Dilani', 'Nishani', 'Raj', 'Priya', 'Saman', 'Ravi', 'Maya'];
        return $names[array_rand($names)];
    }

    private function getRandomLastName(): string
    {
        $names = ['Silva', 'Fernando', 'Perera', 'Jayawardena', 'Wijeratne', 'Patel', 'Sharma'];
        return $names[array_rand($names)];
    }
}
