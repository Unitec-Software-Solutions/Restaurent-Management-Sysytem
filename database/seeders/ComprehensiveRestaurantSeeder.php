<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\SubscriptionPlan;
use App\Enums\ReservationType;
use App\Enums\OrderType;

class ComprehensiveRestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting comprehensive restaurant data seeding...');

        DB::transaction(function () {
            // Step 1: Create Organization
            $organization = $this->createOrganization();
            
            // Step 2: Create Two Branches
            $branches = $this->createBranches($organization);
            
            // Step 3: Create Tables for each branch
            foreach ($branches as $branch) {
                $this->createTables($branch);
            }
            
            // Step 4: Create Menu Structure for each branch
            foreach ($branches as $branch) {
                $this->createMenuStructure($organization, $branch);
            }
            
            // Step 5: Create Customer Records
            $customers = $this->createCustomers();
            
            // Step 6: Create Reservations and Orders
            foreach ($branches as $index => $branch) {
                $this->createReservationsAndOrders($branch, $customers[$index] ?? $customers[0]);
            }
        });

        $this->command->info('✅ Comprehensive restaurant seeding completed successfully!');
    }

    /**
     * Create a sample organization
     */
    private function createOrganization(): Organization
    {
        $this->command->info('📋 Creating organization...');

        // First ensure we have a subscription plan
        $plan = SubscriptionPlan::firstOrCreate([
            'name' => 'Premium Plan'
        ], [
            'description' => 'Premium restaurant management plan',
            'price' => 5000.00,
            'currency' => 'LKR',
            'modules' => json_encode([
                'menu_management',
                'inventory_management',
                'order_management',
                'reporting',
                'customer_management'
            ]),
            'max_branches' => 10,
            'max_employees' => 50,
            'features' => json_encode([
                'menu_management' => true,
                'inventory_management' => true,
                'order_management' => true,
                'reporting' => true,
                'customer_management' => true
            ]),
            'is_active' => true
        ]);

        $organization = Organization::create([
            'name' => 'Delicious Bites Restaurant Group',
            'email' => 'admin@deliciousbites.lk',
            'phone' => '+94 11 234 5678',
            'address' => 'No. 123, Galle Road, Colombo 03, Sri Lanka',
            'contact_person' => 'John Fernando',
            'contact_person_designation' => 'Managing Director',
            'contact_person_phone' => '+94 77 123 4567',
            'business_type' => 'restaurant',
            'is_active' => true,
            'subscription_plan_id' => $plan->id,
            'activated_at' => now(),
            'password' => bcrypt('password123') // Default password
        ]);

        $this->command->info("   ✓ Organization created: {$organization->name}");
        return $organization;
    }

    /**
     * Create two branches for the organization
     */
    private function createBranches(Organization $organization): array
    {
        $this->command->info('🏢 Creating branches...');

        $branchData = [
            [
                'name' => 'Delicious Bites - Colombo',
                'email' => 'colombo@deliciousbites.lk',
                'phone' => '+94 11 234 5679',
                'address' => 'No. 456, Galle Road, Colombo 04, Sri Lanka'
            ],
            [
                'name' => 'Delicious Bites - Kandy',
                'email' => 'kandy@deliciousbites.lk',
                'phone' => '+94 81 234 5680',
                'address' => 'No. 789, Peradeniya Road, Kandy, Sri Lanka'
            ]
        ];

        $branches = [];
        foreach ($branchData as $index => $data) {
            $branch = Branch::create(array_merge($data, [
                'organization_id' => $organization->id,
                'is_active' => true,
                'status' => 'active',
                'opening_time' => '09:00:00',
                'closing_time' => '22:00:00',
                'max_capacity' => 120,
                'total_capacity' => 120,
                'reservation_fee' => 500.00,
                'cancellation_fee' => 250.00,
                'contact_person' => $index === 0 ? 'Sarah Manager' : 'Mike Manager',
                'contact_person_designation' => 'Branch Manager',
                'contact_person_phone' => $index === 0 ? '+94 77 234 5678' : '+94 77 234 5681',
                'activated_at' => now(),
                'manager_name' => $index === 0 ? 'Sarah Manager' : 'Mike Manager',
                'manager_phone' => $index === 0 ? '+94 77 234 5678' : '+94 77 234 5681',
                'code' => $index === 0 ? 'DB-COL-001' : 'DB-KDY-001'
            ]));

            $branches[] = $branch;
            $this->command->info("   ✓ Branch created: {$branch->name}");
        }

        return $branches;
    }

    /**
     * Create tables for a branch
     */
    private function createTables(Branch $branch): void
    {
        $this->command->info("🪑 Creating tables for {$branch->name}...");

        $tableData = [
            ['number' => 'T01', 'capacity' => 2],
            ['number' => 'T02', 'capacity' => 4],
            ['number' => 'T03', 'capacity' => 4],
            ['number' => 'T04', 'capacity' => 6],
            ['number' => 'T05', 'capacity' => 8],
        ];

        foreach ($tableData as $data) {
            Table::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'number' => $data['number'],
                'capacity' => $data['capacity'],
                'status' => 'available',
                'location' => 'Main Dining Area'
            ]);
        }

        $this->command->info("   ✓ Created 5 tables for {$branch->name}");
    }

    /**
     * Create comprehensive menu structure for a branch
     */
    private function createMenuStructure(Organization $organization, Branch $branch): void
    {
        $this->command->info("📋 Creating menu structure for {$branch->name}...");

        // Create item categories for inventory
        $this->createItemCategories($organization);

        // Create 2 menus for the branch
        $breakfastMenu = $this->createMenu($organization, $branch, 'Breakfast Menu', 'morning');
        $dinnerMenu = $this->createMenu($organization, $branch, 'Dinner Menu', 'evening');

        // Create menu categories and items for breakfast
        $this->createMenuWithItems($organization, $branch, $breakfastMenu, 'breakfast');
        
        // Create menu categories and items for dinner
        $this->createMenuWithItems($organization, $branch, $dinnerMenu, 'dinner');

        $this->command->info("   ✓ Menu structure created for {$branch->name}");
    }

    /**
     * Create item categories for inventory
     */
    private function createItemCategories(Organization $organization): void
    {
        $categories = [
            [
                'organization_id' => $organization->id,
                'name' => 'Beverages',
                'description' => 'Hot and cold drinks',
                'is_active' => true
            ],
            [
                'organization_id' => $organization->id,
                'name' => 'Main Dishes',
                'description' => 'Primary food items',
                'is_active' => true
            ],
            [
                'organization_id' => $organization->id,
                'name' => 'Appetizers',
                'description' => 'Starters and small plates',
                'is_active' => true
            ]
        ];

        foreach ($categories as $categoryData) {
            ItemCategory::firstOrCreate(
                ['name' => $categoryData['name'], 'organization_id' => $organization->id],
                $categoryData
            );
        }
    }

    /**
     * Create a menu
     */
    private function createMenu(Organization $organization, Branch $branch, string $name, string $type): Menu
    {
        return Menu::create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => $name,
            'description' => "Delicious {$type} options",
            'date_from' => now()->subDays(7),
            'date_to' => now()->addDays(365),
            'start_time' => $type === 'morning' ? '06:00:00' : '17:00:00',
            'end_time' => $type === 'morning' ? '12:00:00' : '23:00:00',
            'menu_type' => 'regular',
            'is_active' => true,
            'auto_activate' => true,
            'priority' => 1
        ]);
    }

    /**
     * Create menu categories and items for a specific menu
     */
    private function createMenuWithItems(Organization $organization, Branch $branch, Menu $menu, string $menuType): void
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

        // Create menu items for each category
        $this->createMenuItems($organization, $branch, $createdCategories, $menuType, $menu);
    }

    /**
     * Create menu items for categories
     */
    private function createMenuItems(Organization $organization, Branch $branch, array $categories, string $menuType, Menu $menu): void
    {
        // Don't link to ItemMaster for KOT items - they are recipe-based

        if ($menuType === 'breakfast') {
            $itemsByCategory = [
                0 => [ // Hot Beverages
                    ['name' => 'Ceylon Black Tea', 'description' => 'Premium Ceylon black tea', 'price' => 250.00],
                    ['name' => 'Filter Coffee', 'description' => 'Fresh brewed filter coffee', 'price' => 300.00],
                    ['name' => 'Hot Chocolate', 'description' => 'Rich hot chocolate with marshmallows', 'price' => 350.00],
                    ['name' => 'Green Tea', 'description' => 'Healthy green tea', 'price' => 280.00],
                    ['name' => 'Cappuccino', 'description' => 'Italian style cappuccino', 'price' => 400.00]
                ],
                1 => [ // Breakfast Mains
                    ['name' => 'String Hoppers & Curry', 'description' => 'Traditional string hoppers with chicken curry', 'price' => 450.00],
                    ['name' => 'Pancakes', 'description' => 'Fluffy pancakes with maple syrup', 'price' => 500.00],
                    ['name' => 'French Toast', 'description' => 'Golden french toast with butter', 'price' => 380.00],
                    ['name' => 'Egg Hoppers', 'description' => 'Traditional egg hoppers', 'price' => 320.00],
                    ['name' => 'English Breakfast', 'description' => 'Full English breakfast with eggs, bacon, sausage', 'price' => 750.00]
                ]
            ];
        } else {
            $itemsByCategory = [
                0 => [ // Appetizers
                    ['name' => 'Prawn Tempura', 'description' => 'Crispy prawn tempura with dipping sauce', 'price' => 850.00],
                    ['name' => 'Chicken Satay', 'description' => 'Grilled chicken skewers with peanut sauce', 'price' => 650.00],
                    ['name' => 'Spring Rolls', 'description' => 'Crispy vegetable spring rolls', 'price' => 450.00],
                    ['name' => 'Calamari Rings', 'description' => 'Golden fried calamari rings', 'price' => 750.00],
                    ['name' => 'Garlic Bread', 'description' => 'Toasted garlic bread with herbs', 'price' => 380.00]
                ],
                1 => [ // Main Courses
                    ['name' => 'Grilled Salmon', 'description' => 'Atlantic salmon with lemon butter sauce', 'price' => 1450.00],
                    ['name' => 'Beef Tenderloin', 'description' => 'Premium beef with roasted vegetables', 'price' => 1850.00],
                    ['name' => 'Chicken Curry', 'description' => 'Spicy Sri Lankan chicken curry with rice', 'price' => 950.00],
                    ['name' => 'Seafood Pasta', 'description' => 'Fresh seafood pasta in creamy sauce', 'price' => 1250.00],
                    ['name' => 'Lamb Chops', 'description' => 'Grilled lamb chops with mint sauce', 'price' => 1650.00]
                ],
                2 => [ // Desserts
                    ['name' => 'Chocolate Lava Cake', 'description' => 'Warm chocolate cake with molten center', 'price' => 450.00],
                    ['name' => 'Tiramisu', 'description' => 'Classic Italian tiramisu', 'price' => 520.00],
                    ['name' => 'Creme Brulee', 'description' => 'Vanilla creme brulee with caramelized sugar', 'price' => 480.00],
                    ['name' => 'Ice Cream Sundae', 'description' => 'Vanilla ice cream with chocolate sauce', 'price' => 350.00],
                    ['name' => 'Fruit Salad', 'description' => 'Fresh seasonal fruit salad', 'price' => 380.00]
                ]
            ];
        }

        foreach ($itemsByCategory as $categoryIndex => $items) {
            $category = $categories[$categoryIndex];
            
            foreach ($items as $index => $itemData) {
                $menuItem = MenuItem::create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'menu_category_id' => $category->id,
                    'item_master_id' => null, // KOT items don't need item master link
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'cost_price' => $itemData['price'] * 0.6, // 40% markup
                    'currency' => 'LKR',
                    'is_available' => true,
                    'is_active' => true,
                    'is_featured' => $index === 0,
                    'requires_preparation' => true,
                    'preparation_time' => rand(10, 30),
                    'station' => 'kitchen',
                    'sort_order' => $index + 1,
                    'display_order' => $index + 1,
                    'type' => MenuItem::TYPE_KOT // Assuming these are KOT items
                ]);

                // Attach menu item to the menu
                $menu->menuItems()->attach($menuItem->id, [
                    'is_available' => true,
                    'sort_order' => $index + 1
                ]);
            }
        }
    }

    /**
     * Create sample customers
     */
    private function createCustomers(): array
    {
        $this->command->info('👥 Creating customers...');

        $customers = [
            [
                'name' => 'Amal Perera',
                'phone' => '+94 77 123 4567',
                'email' => 'amal.perera@gmail.com'
            ],
            [
                'name' => 'Nimal Silva',
                'phone' => '+94 71 234 5678',
                'email' => 'nimal.silva@gmail.com'
            ]
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);
            $createdCustomers[] = $customer;
            $this->command->info("   ✓ Customer created: {$customer->name}");
        }

        return $createdCustomers;
    }

    /**
     * Create reservations and orders for a branch
     */
    private function createReservationsAndOrders(Branch $branch, Customer $customer): void
    {
        $this->command->info("📅 Creating reservations and orders for {$branch->name}...");

        $tables = Table::where('branch_id', $branch->id)->take(2)->get();
        $menuItems = MenuItem::where('branch_id', $branch->id)->take(3)->get();

        // Create 2 reservations per branch
        for ($i = 0; $i < 2; $i++) {
            $reservationDate = now()->addDays($i + 1);
            
            $reservation = Reservation::create([
                'branch_id' => $branch->id,
                'customer_phone_fk' => $customer->phone,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'date' => $reservationDate->toDateString(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => rand(2, 6),
                'table_size' => rand(2, 6),
                'status' => 'confirmed',
                'type' => ReservationType::ONLINE->value,
                'assigned_table_ids' => [$tables[$i % count($tables)]->id],
                'comments' => $i === 0 ? 'Vegetarian options preferred' : 'Window seat if available',
                'reservation_fee' => $branch->reservation_fee ?? 500.00
            ]);

            // Create order for each reservation
            $this->createOrderForReservation($reservation, $branch, $menuItems, $customer);

            $this->command->info("   ✓ Reservation and order created for {$reservationDate->format('Y-m-d')}");
        }
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
            'order_type' => OrderType::DINE_IN_ONLINE_SCHEDULED->value,
            'status' => 'pending',
            'order_number' => $this->generateOrderNumber($branch->id),
            'order_date' => now()->toDateString(),
            'subtotal' => 0,
            'total_amount' => 0,
            'notes' => 'Seeded order for testing'
        ]);

        $subtotal = 0;
        
        // Add 2-3 random menu items to each order
        $selectedItems = $menuItems->random(rand(2, 3));
        
        foreach ($selectedItems as $menuItem) {
            $quantity = rand(1, 3);
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
                'special_instructions' => $quantity > 2 ? 'Extra spicy' : null
            ]);

            $subtotal += $totalPrice;
        }

        // Update order totals
        $order->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal // No tax or service charge for simplicity
        ]);
    }

    /**
     * Generate a unique order number
     */
    private function generateOrderNumber(int $branchId): string
    {
        $date = now()->format('ymd');
        $todayCount = Order::whereDate('created_at', now())->count() + 1;
        $sequence = str_pad($todayCount, 3, '0', STR_PAD_LEFT);
        
        return "ORD{$branchId}{$date}{$sequence}";
    }
}
