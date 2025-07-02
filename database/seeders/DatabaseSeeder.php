<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\KitchenStation;
use App\Models\ItemCategory;
use App\Models\Admin;
use App\Models\User;

use Illuminate\Support\Facades\Schema;

/**
 * Comprehensive Database Seeder for Restaurant Management System
 * 
 * This seeder implements the complete refactored system for Laravel + PostgreSQL + Tailwind CSS
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $startTime = microtime(true);
        
        $this->command->info('🚀 Starting Comprehensive Restaurant Management System Database Seeding...');
        $this->command->info('═══════════════════════════════════════════════════════════════════════');
        
        // Validate prerequisites
        $this->validatePrerequisites();
        
        $this->command->info('🔧 Preparing database for comprehensive seeding...');
        
        try {
            // Core system setup
            $this->seedOrganizationsAndBranches();
            $this->seedRolesAndPermissions(); 
            $this->seedUsersAndCustomers();
            $this->seedInventoryAndSuppliers();
            $this->seedMenuSystem();
            $this->seedReservations();
            $this->seedOrders();
            $this->seedPaymentsAndTransactions();
            
            $this->displayCompletionSummary($startTime);
            
        } catch (\Exception $e) {
            $this->command->error('❌ Database seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate prerequisites before seeding for Laravel + PostgreSQL + Tailwind CSS
     */
    private function validatePrerequisites(): void
    {
        // Check if required tables exist
        $requiredTables = [
            'organizations', 'branches', 'users', 'customers', 
            'suppliers', 'item_masters', 'menu_categories', 'menu_items'
        ];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                throw new \Exception("Required table '{$table}' does not exist. Please run migrations first.");
            }
        }
        
        $this->command->info('✅ All required tables exist');
    }

    /**
     * Seed organizations and branches with realistic data
     */
    private function seedOrganizationsAndBranches(): void
    {
        $this->command->info('🏢 Creating organizations and branches...');
        
        // Create 2 organizations
        $organizations = Organization::factory(2)->create([
            'is_active' => true,
        ]);
        
        $organizations->each(function ($org) {
            // Create 3 branches per organization
            Branch::factory(3)->create([
                'organization_id' => $org->id,
                'is_active' => true,
                'total_capacity' => rand(50, 150),
                'reservation_fee' => 5.00,
                'cancellation_fee' => 10.00,
            ]);
        });
        
        $this->command->info('  ✅ Created ' . $organizations->count() . ' organizations with ' . (Branch::count()) . ' branches');
    }
    
    /**
     * Seed roles and permissions
     */
    private function seedRolesAndPermissions(): void
    {
        $this->command->info('🎭 Creating roles and permissions...');
        
        // This will use the existing role and permission system
        $this->call([
            \Database\Seeders\RestaurantRolePermissionSeeder::class,
        ]);
        
        $this->command->info('  ✅ Created roles and permissions');
    }
    
    /**
     * Seed users and customers
     */
    private function seedUsersAndCustomers(): void
    {
        $this->command->info('👥 Creating users and customers...');
        
        // Create 20 staff users
        $branches = Branch::all();
        $users = collect();
        
        for ($i = 0; $i < 20; $i++) {
            $branch = $branches->random();
            $user = \App\Models\User::factory()->create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);
            $users->push($user);
        }
        
        // Create 50 phone-based customers
        $customers = \App\Models\Customer::factory(50)->create([
            'is_active' => true,
        ]);
        
        $this->command->info('  ✅ Created ' . $users->count() . ' users and ' . $customers->count() . ' customers');
    }
    
    /**
     * Seed inventory and suppliers - FIXED VERSION for PostgreSQL
     */
    private function seedInventoryAndSuppliers(): void
    {
        $this->command->info('📦 Creating inventory and suppliers...');
        
        $organizations = Organization::all();
        $suppliers = collect();
        $inventoryItems = collect();
        
        $organizations->each(function ($org) use (&$suppliers, &$inventoryItems) {
            // Create suppliers for this organization
            $orgSuppliers = \App\Models\Supplier::factory(5)->create([
                'organization_id' => $org->id,
                'is_active' => true,
            ]);
            $suppliers = $suppliers->merge($orgSuppliers);
            
            // Get organization branches for proper branch assignment
            $orgBranches = Branch::where('organization_id', $org->id)->get();
            
            // Create inventory items for this organization
            $orgInventoryItems = ItemMaster::factory(24)->create([
                'organization_id' => $org->id,
                'is_active' => true,
            ]);
            $inventoryItems = $inventoryItems->merge($orgInventoryItems);
            
            // Get a user from this organization for transaction creation
            $user = \App\Models\User::where('organization_id', $org->id)->first();
            
            // Create initial stock transactions for inventory items
            $orgInventoryItems->each(function ($item) use ($user, $orgBranches, $orgSuppliers) {
                // Get the branch for this item (use item's branch_id or get a random branch)
                $branch = $orgBranches->where('id', $item->branch_id)->first() ?? $orgBranches->random();
                $supplier = $orgSuppliers->random();
                
                // Create opening stock transaction with all required fields for PostgreSQL
                \App\Models\ItemTransaction::create([
                    'organization_id' => $item->organization_id,
                    'branch_id' => $branch->id,
                    'inventory_item_id' => $item->id,
                    'transaction_type' => 'opening_stock',
                    'quantity' => $item->current_stock ?? rand(50, 200),
                    'received_quantity' => $item->current_stock ?? rand(50, 200),
                    'damaged_quantity' => 0,
                    'cost_price' => $item->buying_price ?? $item->cost_price ?? 0,
                    'unit_price' => $item->selling_price ?? 0,
                    'source_type' => 'Manual',
                    'created_by_user_id' => $user ? $user->id : 1,
                    'notes' => 'Initial stock entry for ' . $item->name,
                    'is_active' => true,
                ]);
            });
        });
        
        $this->command->info('  ✅ Created ' . $suppliers->count() . ' suppliers and ' . $inventoryItems->count() . ' inventory items');
    }
    
    /**
     * Seed menu system with proper branch relationships for PostgreSQL - FIXED VERSION
     */
    private function seedMenuSystem(): void
    {
        $this->command->info('🍽️ Creating menu system...');
        
        $branches = Branch::all();
        $menuItems = collect();
        
        // Predefined category names to avoid randomization issues
        $categoryNames = ['Appetizers', 'Main Course', 'Desserts', 'Beverages'];
        
        $branches->each(function ($branch) use (&$menuItems, $categoryNames) {
            // Create menu categories with fixed names for PostgreSQL compatibility
            $categories = collect();
            
            foreach ($categoryNames as $index => $categoryName) {
                $category = \App\Models\MenuCategory::factory()->create([
                    'branch_id' => $branch->id,
                    'organization_id' => $branch->organization_id,
                    'name' => $categoryName,
                    'unicode_name' => $categoryName,
                    'description' => $this->getCategoryDescription($categoryName),
                    'sort_order' => $index + 1,
                    'display_order' => $index + 1,
                    'is_active' => true,
                    'is_featured' => $index < 2, // First 2 categories are featured
                ]);
                $categories->push($category);
            }
            
            // Create menu items for each category (FIXED - avoid array size issues)
            $categories->each(function ($category) use (&$menuItems, $branch) {
                // Get inventory items for this organization
                $availableInventoryItems = ItemMaster::where('organization_id', $branch->organization_id)
                    ->where('is_active', true)
                    ->get();
                
                // Calculate safe number of items to create
                $maxItemsPerCategory = 6; // Reduced from 8 to be safer
                $availableItemCount = $availableInventoryItems->count();
                $itemsToCreate = min($maxItemsPerCategory, $availableItemCount, 6);
                
                // Only proceed if we have inventory items
                if ($availableItemCount > 0 && $itemsToCreate > 0) {
                    // Get a safe subset of inventory items
                    $inventoryItemsToUse = $availableInventoryItems->take($itemsToCreate);
                    
                    $inventoryItemsToUse->each(function ($inventoryItem, $index) use ($category, $branch, &$menuItems) {
                        $menuItem = \App\Models\MenuItem::factory()->create([
                            'branch_id' => $branch->id,
                            'organization_id' => $branch->organization_id,
                            'menu_category_id' => $category->id,
                            'item_masters_id' => $inventoryItem->id,
                            'name' => $this->generateMenuItemName($category->name, $index + 1),
                            'price' => rand(8, 30) + (rand(0, 99) / 100), // Random price between $8.00 and $30.99
                            'is_available' => true,
                            'preparation_time' => rand(10, 30),
                            'calories' => rand(200, 800),
                            'sort_order' => $index + 1,
                        ]);
                        
                        $menuItems->push($menuItem);
                    });
                } else {
                    // Create basic menu items without inventory links if no inventory available
                    for ($i = 0; $i < 3; $i++) {
                        $menuItem = \App\Models\MenuItem::factory()->create([
                            'branch_id' => $branch->id,
                            'organization_id' => $branch->organization_id,
                            'menu_category_id' => $category->id,
                            'item_masters_id' => null, // No inventory link
                            'name' => $this->generateMenuItemName($category->name, $i + 1),
                            'price' => rand(8, 30) + (rand(0, 99) / 100),
                            'is_available' => true,
                            'preparation_time' => rand(10, 30),
                            'calories' => rand(200, 800),
                            'sort_order' => $i + 1,
                        ]);
                        
                        $menuItems->push($menuItem);
                    }
                }
            });
        });
        
        $this->command->info('  ✅ Created ' . $menuItems->count() . ' menu items across ' . $branches->count() . ' branches');
    }
    
    /**
     * Get category description based on category name
     */
    private function getCategoryDescription(string $categoryName): string
    {
        return match($categoryName) {
            'Appetizers' => 'Start your meal with our delicious appetizers and small plates',
            'Main Course' => 'Hearty and satisfying main dishes prepared with fresh ingredients',
            'Desserts' => 'Sweet endings to your perfect meal, crafted by our pastry chefs',
            'Beverages' => 'Refreshing drinks, specialty beverages, and curated selections',
            default => "Delicious {$categoryName} prepared fresh daily"
        };
    }
    
    /**
     * Generate menu item name based on category and index - FIXED with more items
     */
    private function generateMenuItemName(string $categoryName, int $index): string
    {
        $names = [
            'Appetizers' => [
                'Classic Caesar Salad', 'Buffalo Wings', 'Mozzarella Sticks', 'Bruschetta Trio',
                'Calamari Rings', 'Spinach Dip', 'Chicken Quesadilla', 'Loaded Nachos',
                'Garlic Bread', 'Soup of the Day', 'Fresh Spring Rolls', 'Deviled Eggs'
            ],
            'Main Course' => [
                'Grilled Salmon', 'Beef Tenderloin', 'Chicken Parmesan', 'Pasta Carbonara',
                'Fish and Chips', 'BBQ Ribs', 'Vegetable Stir Fry', 'Lamb Chops',
                'Mushroom Risotto', 'Grilled Chicken', 'Beef Steak', 'Seafood Paella'
            ],
            'Desserts' => [
                'Chocolate Brownie', 'Tiramisu', 'Ice Cream Sundae', 'Cheesecake',
                'Fruit Tart', 'Crème Brûlée', 'Apple Pie', 'Chocolate Mousse',
                'Panna Cotta', 'Lemon Tart', 'Bread Pudding', 'Key Lime Pie'
            ],
            'Beverages' => [
                'Fresh Lemonade', 'Iced Coffee', 'Fruit Smoothie', 'Hot Chocolate',
                'Green Tea', 'Fresh Orange Juice', 'Specialty Cocktail', 'Craft Beer',
                'Herbal Tea', 'Fresh Coconut Water', 'Iced Tea', 'House Wine'
            ]
        ];
        
        $categoryNames = $names[$categoryName] ?? [
            'Special Item 1', 'Special Item 2', 'Special Item 3', 'Special Item 4', 
            'Special Item 5', 'Special Item 6', 'Special Item 7', 'Special Item 8',
            'Special Item 9', 'Special Item 10', 'Special Item 11', 'Special Item 12'
        ];
        
        // Use modulo to safely cycle through available names
        $nameIndex = ($index - 1) % count($categoryNames);
        return $categoryNames[$nameIndex] ?? "Special {$categoryName} {$index}";
    }
    
    /**
     * Seed reservations with different scenarios
     */
    private function seedReservations(): void
    {
        $this->command->info('📅 Creating reservation scenarios...');
        
        $customers = \App\Models\Customer::all();
        $branches = Branch::all();
        $reservations = collect();
        
        // Create 100 reservations with different scenarios
        for ($i = 0; $i < 100; $i++) {
            $customer = $customers->random();
            $branch = $branches->random();
            
            $date = fake()->dateTimeBetween('-15 days', '+30 days');
            
            $reservation = \App\Models\Reservation::factory()->create([
                'customer_phone_fk' => $customer->phone,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'branch_id' => $branch->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $date,
                'end_time' => (clone $date)->modify('+2 hours'),
                'number_of_people' => rand(2, 8),
                'type' => \App\Enums\ReservationType::cases()[array_rand(\App\Enums\ReservationType::cases())],
                'status' => ['pending', 'confirmed', 'completed', 'cancelled'][array_rand(['pending', 'confirmed', 'completed', 'cancelled'])],
                'reservation_fee' => rand(0, 1) ? 5.00 : 0.00,
            ]);
            $reservations->push($reservation);
        }
        
        $this->command->info('  ✅ Created ' . $reservations->count() . ' reservations with various scenarios');
    }
    
    /**
     * Seed orders with different types - FIXED VERSION for Laravel + PostgreSQL + Tailwind CSS
     */
    private function seedOrders(): void
    {
        $this->command->info('🛍️ Creating order scenarios...');
        
        $customers = \App\Models\Customer::all();
        $branches = Branch::all();
        $orders = collect();
        
        // Create 200 orders with different types
        for ($i = 0; $i < 200; $i++) {
            $customer = $customers->random();
            $branch = $branches->random();
            
            $orderDate = fake()->dateTimeBetween('-30 days', 'now');
            
            $order = \App\Models\Order::factory()->create([
                'customer_phone_fk' => $customer->phone,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'branch_id' => $branch->id,
                'order_date' => $orderDate,
                'order_time' => $orderDate,
                'order_type' => \App\Enums\OrderType::cases()[array_rand(\App\Enums\OrderType::cases())],
                'status' => ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'][array_rand(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])],
                'subtotal' => rand(20, 100),
                'tax' => rand(2, 10),
                'service_charge' => rand(2, 8),
                'total' => function(array $attributes) {
                    return $attributes['subtotal'] + $attributes['tax'] + $attributes['service_charge'];
                },
            ]);
            
            $orders->push($order);
            
            // Add order items (1-5 items per order) - FIXED to handle small arrays
            $menuItems = \App\Models\MenuItem::where('branch_id', $branch->id)->get();
            
            if ($menuItems->count() > 0) {
                // Calculate safe number of items to select
                $maxItemsToSelect = min(5, $menuItems->count());
                $itemsToSelect = rand(1, $maxItemsToSelect);
                
                // Use take() instead of random() to avoid LengthException
                $selectedItems = $menuItems->shuffle()->take($itemsToSelect);
                
                $selectedItems->each(function ($menuItem) use ($order) {
                    \App\Models\OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'item_masters_id' => $menuItem->item_masters_id,
                        'quantity' => rand(1, 3),
                        'unit_price' => $menuItem->price,
                        'total_price' => function(array $attributes) {
                            return $attributes['quantity'] * $attributes['unit_price'];
                        },
                    ]);
                });
            }
        }
        
        $this->command->info('  ✅ Created ' . $orders->count() . ' orders with realistic scenarios');
    }
    
    /**
     * Seed payments and transactions
     */
    private function seedPaymentsAndTransactions(): void
    {
        $this->command->info('💳 Creating payment scenarios...');
        
        $completedOrders = \App\Models\Order::where('status', 'completed')->get();
        $payments = collect();
        
        $completedOrders->each(function ($order) use (&$payments) {
            $payment = \App\Models\Payment::factory()->create([
                'payable_type' => get_class($order),
                'payable_id' => $order->id,
                'amount' => $order->total,
                'status' => 'completed',
                'payment_method' => ['cash', 'card', 'digital_wallet', 'bank_transfer'][array_rand(['cash', 'card', 'digital_wallet', 'bank_transfer'])],
                'payment_reference' => 'PAY-' . $order->id . '-' . time(),
            ]);
            $payments->push($payment);
        });
        
        $this->command->info('  ✅ Created ' . $payments->count() . ' payment transactions');
    }
    
    /**
     * Display completion summary
     */
    private function displayCompletionSummary(float $startTime): void
    {
        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->command->info('');
        $this->command->info('🎉 RESTAURANT MANAGEMENT SYSTEM SEEDING COMPLETED!');
        $this->command->info('══════════════════════════════════════════════════════');
        $this->command->info("⏱️  Total execution time: {$executionTime} seconds");
        $this->command->info('');
        
        // Display statistics
        $this->command->info('📊 SEEDED DATA SUMMARY:');
        $this->command->info('─────────────────────────────');
        $this->command->info('🏢 Organizations: ' . Organization::count());
        $this->command->info('🏪 Branches: ' . Branch::count());
        $this->command->info('👥 Users: ' . \App\Models\User::count());
        $this->command->info('📞 Customers: ' . \App\Models\Customer::count());
        $this->command->info('🚚 Suppliers: ' . \App\Models\Supplier::count());
        $this->command->info('📦 Inventory Items: ' . ItemMaster::count());
        $this->command->info('🔄 Item Transactions: ' . \App\Models\ItemTransaction::count());
        $this->command->info('🍽️ Menu Categories: ' . \App\Models\MenuCategory::count());
        $this->command->info('🍕 Menu Items: ' . \App\Models\MenuItem::count());
        $this->command->info('📅 Reservations: ' . \App\Models\Reservation::count());
        $this->command->info('🛍️ Orders: ' . \App\Models\Order::count());
        $this->command->info('💳 Payments: ' . \App\Models\Payment::count());
        
        $this->command->info('');
        $this->command->info('✅ Your Laravel + PostgreSQL + Tailwind CSS restaurant management system is ready!');
    }
}
