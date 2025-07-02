<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Models\KitchenStation;
use App\Enums\OrderType;
use App\Models\InventoryItem;
use Carbon\Carbon;

class OrderSimulationSeeder extends Seeder
{
    /**
     * Build order scenarios with KOT/bill workflows
     */
    public function run(): void
    {
        $this->command->info('🍽️ Creating Order Scenarios...');
        
        $organizations = Organization::with(['branches', 'subscriptionPlan'])->get();
        
        foreach ($organizations as $organization) {
            $this->createOrderScenariosForOrganization($organization);
        }
        
        $this->command->info('✅ Order scenarios created successfully');
    }

    private function createOrderScenariosForOrganization(Organization $organization): void
    {
        $this->command->info("  🍽️ Creating orders for: {$organization->name}");
        
        foreach ($organization->branches as $branch) {
            // Create menu items first
            $this->createMenuStructureForBranch($branch);
            
            // Create various order scenarios
            $this->createDineInOrders($branch);
            $this->createTakeawayOrders($branch);
            $this->createDeliveryOrders($branch);
        }
    }

    private function createMenuStructureForBranch(Branch $branch): void
    {
        // Create menu categories
        $categories = $this->createMenuCategories($branch);
        
        // Create menu items for each category
        foreach ($categories as $category) {
            $this->createMenuItemsForCategory($branch, $category);
        }
        
        $categoryCount = $categories->count();
        $itemCount = MenuItem::where('branch_id', $branch->id)->count();
        $this->command->info("    📜 Created {$categoryCount} categories and {$itemCount} menu items for {$branch->name}");
    }

    private function createMenuCategories(Branch $branch)
    {
        $categoryData = [
            ['name' => 'Appetizers', 'description' => 'Start your meal with our delicious appetizers'],
            ['name' => 'Main Courses', 'description' => 'Hearty main dishes to satisfy your hunger'],
            ['name' => 'Rice & Noodles', 'description' => 'Traditional rice and noodle dishes'],
            ['name' => 'Seafood', 'description' => 'Fresh catch from the ocean'],
            ['name' => 'Vegetarian', 'description' => 'Plant-based delicious options'],
            ['name' => 'Desserts', 'description' => 'Sweet endings to your meal'],
            ['name' => 'Beverages', 'description' => 'Refreshing drinks and hot beverages']
        ];

        $categories = collect();
        
        foreach ($categoryData as $index => $data) {
            $category = MenuCategory::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'is_featured' => rand(0, 1) == 1
            ]);
            
            $categories->push($category);
        }
        
        return $categories;
    }

    private function createMenuItemsForCategory(Branch $branch, MenuCategory $category): void
    {
        $itemsByCategory = [
            'Appetizers' => [
                ['name' => 'Spring Rolls', 'price' => 450.00, 'description' => 'Crispy vegetable spring rolls'],
                ['name' => 'Fish Cutlets', 'price' => 380.00, 'description' => 'Spiced fish cutlets with sauce'],
                ['name' => 'Chicken Wings', 'price' => 650.00, 'description' => 'BBQ glazed chicken wings']
            ],
            'Main Courses' => [
                ['name' => 'Grilled Chicken', 'price' => 1200.00, 'description' => 'Herb marinated grilled chicken'],
                ['name' => 'Beef Steak', 'price' => 1800.00, 'description' => 'Premium beef steak with vegetables'],
                ['name' => 'Pork Chops', 'price' => 1350.00, 'description' => 'Juicy pork chops with mashed potatoes']
            ],
            'Rice & Noodles' => [
                ['name' => 'Chicken Fried Rice', 'price' => 950.00, 'description' => 'Wok-fried rice with chicken'],
                ['name' => 'Vegetable Noodles', 'price' => 780.00, 'description' => 'Stir-fried noodles with vegetables'],
                ['name' => 'Biriyani', 'price' => 1100.00, 'description' => 'Aromatic basmati rice with spices']
            ],
            'Seafood' => [
                ['name' => 'Grilled Fish', 'price' => 1450.00, 'description' => 'Fresh fish grilled to perfection'],
                ['name' => 'Prawns Curry', 'price' => 1650.00, 'description' => 'Spicy prawns in coconut curry'],
                ['name' => 'Crab Curry', 'price' => 2200.00, 'description' => 'Fresh crab in traditional curry']
            ],
            'Vegetarian' => [
                ['name' => 'Dal Curry', 'price' => 420.00, 'description' => 'Traditional lentil curry'],
                ['name' => 'Vegetable Curry', 'price' => 380.00, 'description' => 'Mixed vegetable curry'],
                ['name' => 'Eggplant Curry', 'price' => 450.00, 'description' => 'Spiced eggplant curry']
            ],
            'Desserts' => [
                ['name' => 'Watalappan', 'price' => 320.00, 'description' => 'Traditional coconut pudding'],
                ['name' => 'Ice Cream', 'price' => 280.00, 'description' => 'Vanilla ice cream with toppings'],
                ['name' => 'Fruit Salad', 'price' => 350.00, 'description' => 'Fresh seasonal fruit salad']
            ],
            'Beverages' => [
                ['name' => 'Fresh Lime Juice', 'price' => 180.00, 'description' => 'Freshly squeezed lime juice'],
                ['name' => 'King Coconut', 'price' => 150.00, 'description' => 'Fresh king coconut water'],
                ['name' => 'Coffee', 'price' => 220.00, 'description' => 'Freshly brewed coffee'],
                ['name' => 'Tea', 'price' => 120.00, 'description' => 'Ceylon black tea']
            ]
        ];

        $items = $itemsByCategory[$category->name] ?? [];
        
        foreach ($items as $itemData) {
            MenuItem::create([
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
                'menu_category_id' => $category->id,
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'cost_price' => $itemData['price'] * 0.6, // 40% markup
                'is_active' => true,
                'is_available' => rand(0, 10) > 1, // 90% available, 10% out of stock
                'preparation_time' => rand(10, 45), // 10-45 minutes
                'calories' => rand(200, 800),
                'spice_level' => ['mild', 'medium', 'hot'][rand(0, 2)],
                'dietary_info' => json_encode($this->getRandomDietaryInfo()),
                'allergen_info' => json_encode($this->getRandomAllergenInfo()),
                'ingredients' => json_encode($this->getRandomIngredients($category->name)),
                'image_url' => null,
                'featured' => rand(0, 1) == 1
            ]);
        }
    }

    private function createDineInOrders(Branch $branch): void
    {
        $reservations = Reservation::where('branch_id', $branch->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->get();
        
        $tables = Table::where('branch_id', $branch->id)->get();
        $menuItems = MenuItem::where('branch_id', $branch->id)->where('is_available', true)->get();
        
        if ($menuItems->isEmpty()) {
            return;
        }

        $orderCount = min($reservations->count() + rand(10, 20), 30);
        $this->command->info("    🍽️ Creating {$orderCount} dine-in orders for {$branch->name}");
        
        for ($i = 0; $i < $orderCount; $i++) {
            $reservation = $reservations->random();
            $table = $reservation->table ?? $tables->random();
            
            $this->createDineInOrder($branch, $reservation, $table, $menuItems);
        }
    }

    private function createDineInOrder(Branch $branch, Reservation $reservation, Table $table, $menuItems): void
    {
        $orderDateTime = Carbon::parse($reservation->reservation_date . ' ' . $reservation->reservation_time)
            ->addMinutes(rand(5, 30)); // Order placed 5-30 minutes after arrival
        
        $status = $this->determineOrderStatus($orderDateTime);
        
        $order = Order::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'reservation_id' => $reservation->id,
            'table_id' => $table->id,
            'user_id' => $reservation->user_id,
            'customer_name' => $reservation->customer_name,
            'customer_phone' => $reservation->customer_phone,
            'order_number' => $this->generateOrderNumber($branch, $orderDateTime),
            'order_type' => 'dine_in',
            'status' => $status,
            'payment_status' => $this->determinePaymentStatus($status),
            'subtotal' => 0, // Will be calculated after adding items
            'tax_amount' => 0,
            'service_charge' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'payment_method' => ['cash', 'card', 'digital'][rand(0, 2)],
            'special_instructions' => $this->getRandomOrderInstructions(),
            'estimated_completion_time' => $orderDateTime->copy()->addMinutes(rand(20, 60)),
            'created_at' => $orderDateTime,
            'confirmed_at' => $status !== 'pending' ? $orderDateTime->copy()->addMinutes(2) : null,
            'prepared_at' => in_array($status, ['ready', 'served', 'completed']) ? 
                           $orderDateTime->copy()->addMinutes(rand(15, 45)) : null,
            'served_at' => in_array($status, ['served', 'completed']) ? 
                         $orderDateTime->copy()->addMinutes(rand(20, 50)) : null,
            'completed_at' => $status === 'completed' ? 
                            $orderDateTime->copy()->addMinutes(rand(60, 120)) : null
        ]);

        // Add order items
        $this->addOrderItems($order, $menuItems, $reservation->party_size);
        
        // Update order totals
        $this->calculateOrderTotals($order);
        
        // Create KOT (Kitchen Order Ticket) entries
        $this->createKOTEntries($order);
    }

    private function createTakeawayOrders(Branch $branch): void
    {
        $menuItems = MenuItem::where('branch_id', $branch->id)->where('is_available', true)->get();
        
        if ($menuItems->isEmpty()) {
            return;
        }

        $orderCount = rand(20, 30);
        $this->command->info("    🥡 Creating {$orderCount} takeaway orders for {$branch->name}");
        
        for ($i = 0; $i < $orderCount; $i++) {
            $this->createTakeawayOrder($branch, $menuItems);
        }
    }

    private function createTakeawayOrder(Branch $branch, $menuItems): void
    {
        $orderDateTime = now()->subDays(rand(0, 7))->setHour(rand(11, 21))->setMinute(rand(0, 59));
        $status = $this->determineOrderStatus($orderDateTime);
        
        $order = Order::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'customer_name' => $this->generateCustomerName(),
            'customer_phone' => $this->generatePhoneNumber(),
            'order_number' => $this->generateOrderNumber($branch, $orderDateTime),
            'order_type' => OrderType::TAKEAWAY_WALK_IN_DEMAND,
            'status' => $status,
            'payment_status' => $this->determinePaymentStatus($status),
            'subtotal' => 0,
            'tax_amount' => 0,
            'service_charge' => 0,
            'discount_amount' => rand(0, 1) ? rand(50, 200) : 0,
            'total_amount' => 0,
            'payment_method' => ['cash', 'card', 'digital'][rand(0, 2)],
            'special_instructions' => $this->getRandomOrderInstructions(),
            'estimated_completion_time' => $orderDateTime->copy()->addMinutes(rand(15, 45)),
            'created_at' => $orderDateTime,
            'confirmed_at' => $status !== 'pending' ? $orderDateTime->copy()->addMinutes(1) : null,
            'prepared_at' => in_array($status, ['ready', 'completed']) ? 
                           $orderDateTime->copy()->addMinutes(rand(10, 30)) : null,
            'completed_at' => $status === 'completed' ? 
                            $orderDateTime->copy()->addMinutes(rand(15, 45)) : null
        ]);

        // Add order items
        $this->addOrderItems($order, $menuItems, rand(1, 3));
        
        // Update order totals
        $this->calculateOrderTotals($order);
        
        // Create KOT entries
        $this->createKOTEntries($order);
        
        // Simulate inventory deduction for completed orders
        if ($status === 'completed') {
            $this->deductInventoryForOrder($order);
        }
    }

    private function createDeliveryOrders(Branch $branch): void
    {
        $menuItems = MenuItem::where('branch_id', $branch->id)->where('is_available', true)->get();
        
        if ($menuItems->isEmpty()) {
            return;
        }

        $orderCount = rand(15, 25);
        $this->command->info("    🚚 Creating {$orderCount} delivery orders for {$branch->name}");
        
        for ($i = 0; $i < $orderCount; $i++) {
            $this->createDeliveryOrder($branch, $menuItems);
        }
    }

    private function createDeliveryOrder(Branch $branch, $menuItems): void
    {
        $orderDateTime = now()->subDays(rand(0, 5))->setHour(rand(11, 21))->setMinute(rand(0, 59));
        $status = $this->determineOrderStatus($orderDateTime);
        
        $order = Order::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'customer_name' => $this->generateCustomerName(),
            'customer_phone' => $this->generatePhoneNumber(),
            'delivery_address' => $this->generateDeliveryAddress(),
            'order_number' => $this->generateOrderNumber($branch, $orderDateTime),
            'order_type' => 'delivery',
            'status' => $status,
            'payment_status' => $this->determinePaymentStatus($status),
            'subtotal' => 0,
            'tax_amount' => 0,
            'service_charge' => 0,
            'delivery_fee' => rand(150, 300),
            'discount_amount' => rand(0, 1) ? rand(100, 500) : 0,
            'total_amount' => 0,
            'payment_method' => ['cash', 'card', 'digital'][rand(0, 2)],
            'special_instructions' => $this->getRandomOrderInstructions(),
            'estimated_completion_time' => $orderDateTime->copy()->addMinutes(rand(30, 90)),
            'created_at' => $orderDateTime,
            'confirmed_at' => $status !== 'pending' ? $orderDateTime->copy()->addMinutes(2) : null,
            'prepared_at' => in_array($status, ['ready', 'out_for_delivery', 'delivered', 'completed']) ? 
                           $orderDateTime->copy()->addMinutes(rand(15, 45)) : null,
            'out_for_delivery_at' => in_array($status, ['out_for_delivery', 'delivered', 'completed']) ? 
                                   $orderDateTime->copy()->addMinutes(rand(20, 50)) : null,
            'delivered_at' => in_array($status, ['delivered', 'completed']) ? 
                            $orderDateTime->copy()->addMinutes(rand(30, 90)) : null,
            'completed_at' => $status === 'completed' ? 
                            $orderDateTime->copy()->addMinutes(rand(35, 95)) : null
        ]);

        // Add order items
        $this->addOrderItems($order, $menuItems, rand(2, 5));
        
        // Update order totals
        $this->calculateOrderTotals($order);
        
        // Create KOT entries
        $this->createKOTEntries($order);
        
        // Simulate inventory deduction for completed orders
        if ($status === 'completed') {
            $this->deductInventoryForOrder($order);
        }
    }

    private function addOrderItems(Order $order, $menuItems, int $maxItems): void
    {
        $itemCount = rand(1, min($maxItems, 5));
        $selectedItems = $menuItems->random($itemCount);
        
        foreach ($selectedItems as $menuItem) {
            $quantity = rand(1, 3);
            $unitPrice = $menuItem->price;
            
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
                'special_instructions' => rand(0, 1) ? $this->getRandomItemInstructions() : null,
                'kitchen_station_id' => $this->getKitchenStationForItem($order->branch_id, $menuItem),
                'status' => $order->status === 'completed' ? 'completed' : 'pending'
            ]);
        }
    }

    private function calculateOrderTotals(Order $order): void
    {
        $subtotal = $order->orderItems()->sum('total_price');
        $serviceCharge = $order->order_type === 'dine_in' ? $subtotal * 0.10 : 0; // 10% service charge for dine-in
        $taxAmount = ($subtotal + $serviceCharge) * 0.12; // 12% tax
        $deliveryFee = $order->delivery_fee ?? 0;
        $discountAmount = $order->discount_amount ?? 0;
        
        $totalAmount = $subtotal + $serviceCharge + $taxAmount + $deliveryFee - $discountAmount;
        
        $order->update([
            'subtotal' => $subtotal,
            'service_charge' => $serviceCharge,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);
    }

    private function createKOTEntries(Order $order): void
    {
        // Group order items by kitchen station
        $itemsByStation = $order->orderItems()
            ->with('kitchenStation')
            ->get()
            ->groupBy('kitchen_station_id');

        foreach ($itemsByStation as $stationId => $items) {
            // In a real system, you'd create KOT records
            // KOT::create([
            //     'order_id' => $order->id,
            //     'kitchen_station_id' => $stationId,
            //     'items' => $items->toArray(),
            //     'created_at' => $order->created_at
            // ]);
        }
    }

    private function deductInventoryForOrder(Order $order): void
    {
        // Simulate inventory deduction for completed orders
        foreach ($order->orderItems as $orderItem) {
            $inventoryItems = InventoryItem::where('branch_id', $order->branch_id)
                ->where('current_stock', '>', 0)
                ->limit(rand(1, 3)) // Assume 1-3 ingredients per menu item
                ->get();

            foreach ($inventoryItems as $inventoryItem) {
                $deductionAmount = $orderItem->quantity * rand(1, 3); // Random ingredient usage
                $newStock = max(0, $inventoryItem->current_stock - $deductionAmount);
                
                $inventoryItem->update([
                    'current_stock' => $newStock,
                    'last_updated' => now()
                ]);
            }
        }
    }

    // Helper methods
    private function determineOrderStatus(Carbon $orderDateTime): string
    {
        if ($orderDateTime->isFuture()) {
            return ['pending', 'confirmed'][rand(0, 1)];
        } elseif ($orderDateTime->diffInHours(now()) < 2) {
            return ['preparing', 'ready'][rand(0, 1)];
        } else {
            return ['completed', 'cancelled'][rand(0, 9)] ? 'completed' : 'cancelled'; // 90% completed
        }
    }

    private function determinePaymentStatus(string $orderStatus): string
    {
        return match($orderStatus) {
            'pending' => 'pending',
            'cancelled' => 'refunded',
            'completed' => 'paid',
            default => 'pending'
        };
    }

    private function generateOrderNumber(Branch $branch, Carbon $dateTime): string
    {
        return 'ORD-' . $branch->id . '-' . $dateTime->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function generateCustomerName(): string
    {
        $names = [
            'Amara Silva', 'Buddhika Perera', 'Chathura Fernando', 'Dilshan Jayawardene',
            'Eranga Gunasekara', 'Fathima Nazeer', 'Gayan Mendis', 'Hasini Rathnayake',
            'Ishan Wickramasinghe', 'Janani Samaraweera', 'Kamal Dissanayake', 'Lakmini Fernando'
        ];
        
        return $names[rand(0, count($names) - 1)];
    }

    private function generatePhoneNumber(): string
    {
        return '+94 7' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    }

    private function generateDeliveryAddress(): string
    {
        $addresses = [
            '123 Main Street, Colombo 03',
            '456 Galle Road, Dehiwala',
            '789 Kandy Road, Kotte',
            '321 High Level Road, Nugegoda',
            '654 Baseline Road, Colombo 09'
        ];
        
        return $addresses[rand(0, count($addresses) - 1)];
    }

    private function getRandomOrderInstructions(): ?string
    {
        $instructions = [
            'Extra spicy please',
            'No onions',
            'Less salt',
            'Pack separately',
            'Include extra sauce',
            'Make it mild'
        ];
        
        return rand(0, 1) ? $instructions[rand(0, count($instructions) - 1)] : null;
    }

    private function getRandomItemInstructions(): string
    {
        $instructions = [
            'Well done',
            'Medium rare',
            'Extra crispy',
            'No vegetables',
            'Extra cheese',
            'Separate packaging'
        ];
        
        return $instructions[rand(0, count($instructions) - 1)];
    }

    private function getKitchenStationForItem(int $branchId, MenuItem $menuItem): ?int
    {
        $station = KitchenStation::where('branch_id', $branchId)->inRandomOrder()->first();
        return $station ? $station->id : null;
    }

    private function getRandomDietaryInfo(): array
    {
        $info = ['vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'halal'];
        return array_slice($info, 0, rand(0, 2));
    }

    private function getRandomAllergenInfo(): array
    {
        $allergens = ['nuts', 'dairy', 'gluten', 'seafood', 'eggs'];
        return array_slice($allergens, 0, rand(0, 2));
    }

    private function getRandomIngredients(string $categoryName): array
    {
        $ingredientsByCategory = [
            'Appetizers' => ['flour', 'vegetables', 'spices', 'oil'],
            'Main Courses' => ['meat', 'vegetables', 'spices', 'herbs'],
            'Rice & Noodles' => ['rice', 'noodles', 'vegetables', 'soy_sauce'],
            'Seafood' => ['fish', 'prawns', 'spices', 'coconut_milk'],
            'Vegetarian' => ['vegetables', 'lentils', 'spices', 'coconut'],
            'Desserts' => ['sugar', 'coconut', 'flour', 'eggs'],
            'Beverages' => ['water', 'fruits', 'sugar', 'ice']
        ];
        
        return $ingredientsByCategory[$categoryName] ?? ['mixed_ingredients'];
    }
}
