<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ComprehensiveOrdersSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🍽️ Creating comprehensive orders data...');

        $branches = Branch::with('organization')->get();
        
        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please run BranchSeeder first.');
            return;
        }

        foreach ($branches as $branch) {
            $this->createBranchOrders($branch);
        }

        $this->command->info('✅ Orders seeding completed!');
    }

    private function createBranchOrders($branch)
    {
        $this->command->info("Creating orders for: {$branch->name}");

        // Get menu items for this branch
        $menuItems = ItemMaster::where('is_menu_item', true)
            ->where('active', true)
            ->get();

        if ($menuItems->isEmpty()) {
            $this->command->warn("No menu items found for {$branch->name}");
            return;
        }

        // Create orders for the last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $this->createDailyOrders($branch, $menuItems, $date);
        }

        // Create future orders (next 7 days)
        for ($i = 1; $i <= 7; $i++) {
            $futureDate = now()->addDays($i);
            $this->createFutureOrders($branch, $menuItems, $futureDate);
        }

        // Create special scenario orders
        $this->createSpecialScenarioOrders($branch, $menuItems);
    }

    private function createDailyOrders($branch, $menuItems, $date)
    {
        $isWeekend = $date->isWeekend();
        $dayOfWeek = $date->dayOfWeek;

        // Different order volumes based on day and time
        $orderCounts = [
            'breakfast' => $isWeekend ? rand(8, 15) : rand(5, 12),
            'lunch' => $isWeekend ? rand(20, 35) : rand(15, 28),
            'dinner' => $isWeekend ? rand(25, 40) : rand(18, 30),
            'late_night' => $isWeekend ? rand(5, 12) : rand(2, 8),
        ];

        foreach ($orderCounts as $period => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createTimeBasedOrder($branch, $menuItems, $date, $period);
            }
        }
    }

    private function createTimeBasedOrder($branch, $menuItems, $date, $period)
    {
        // Set order time based on period
        $timeRanges = [
            'breakfast' => ['07:00', '10:30'],
            'lunch' => ['11:30', '15:00'],
            'dinner' => ['17:30', '21:30'],
            'late_night' => ['22:00', '23:59'],
        ];

        $timeRange = $timeRanges[$period];
        $orderTime = $this->randomTimeInRange($date, $timeRange[0], $timeRange[1]);

        // Determine order type based on time and randomness
        $orderType = $this->getOrderType($period);
        
        // Create order
        $order = $this->createOrder($branch, $orderTime, $orderType);
        
        // Add items based on meal period
        $this->addOrderItems($order, $menuItems, $period);
        
        // Add payment and finalize
        $this->finalizeOrder($order);
    }

    private function createOrder($branch, $orderTime, $orderType)
    {
        $customerNames = [
            'John Smith', 'Sarah Johnson', 'Mike Chen', 'Emily Davis',
            'David Wilson', 'Lisa Wang', 'James Brown', 'Anna Garcia',
            'Robert Lee', 'Maria Rodriguez', 'Kevin Park', 'Jessica Kim',
            'Chris Taylor', 'Amanda White', 'Daniel Martinez', 'Rachel Green'
        ];

        $phoneNumbers = [
            '+94771234567', '+94771234568', '+94771234569', '+94771234570',
            '+94771234571', '+94771234572', '+94771234573', '+94771234574',
            '+94771234575', '+94771234576', '+94771234577', '+94771234578'
        ];

        $customerName = $customerNames[array_rand($customerNames)];
        $customerPhone = $phoneNumbers[array_rand($phoneNumbers)];

        // Create takeaway ID for takeaway orders
        $takeawayId = null;
        if (str_contains($orderType, 'takeaway')) {
            $takeawayId = 'TW' . $orderTime->format('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }

        return Order::create([
            'order_type' => $orderType,
            'branch_id' => $branch->id,
            'order_time' => $orderTime,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'status' => $this->getOrderStatus($orderTime),
            'takeaway_id' => $takeawayId,
            'table_number' => $orderType === 'dine_in' ? rand(1, 20) : null,
            'special_instructions' => $this->getRandomInstructions(),
            'placed_by_admin' => rand(0, 100) > 70, // 30% placed by admin
            'created_at' => $orderTime,
            'updated_at' => $orderTime,
        ]);
    }

    private function addOrderItems($order, $menuItems, $period)
    {
        // Select appropriate items based on meal period
        $periodItems = $this->getMenuItemsForPeriod($menuItems, $period);
        
        // Number of different items in order
        $itemCount = $this->getItemCountForPeriod($period);
        
        $selectedItems = $periodItems->random(min($itemCount, $periodItems->count()));
        
        $subtotal = 0;
        
        foreach ($selectedItems as $menuItem) {
            $quantity = rand(1, $period === 'lunch' || $period === 'dinner' ? 3 : 2);
            $unitPrice = $menuItem->selling_price;
            $totalPrice = $unitPrice * $quantity;
            
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'inventory_item_id' => $menuItem->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'special_instructions' => $this->getItemInstructions(),
            ]);
            
            $subtotal += $totalPrice;
        }
        
        // Update order totals
        $tax = $subtotal * 0.10;
        $serviceCharge = $order->order_type === 'dine_in' ? $subtotal * 0.05 : 0;
        $total = $subtotal + $tax + $serviceCharge;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $serviceCharge,
            'total' => $total,
        ]);
    }

    private function finalizeOrder($order)
    {
        // Skip payment for future orders or pending orders
        if ($order->status === 'pending' || $order->order_time->isFuture()) {
            return;
        }

        // Create payment record
        $this->createPayment($order);
        
        // Update order status based on time
        $this->updateOrderStatus($order);
    }

    private function createPayment($order)
    {
        $paymentMethods = ['cash', 'card', 'mobile_payment', 'digital_wallet'];
        $paymentStatuses = ['completed', 'pending', 'failed', 'refunded'];
        
        // Most payments are successful
        $status = $paymentStatuses[rand(0, 100) > 90 ? rand(1, 3) : 0];
        
        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
            'status' => $status,
            'transaction_id' => 'TXN' . $order->id . time(),
            'payment_date' => $order->created_at->addMinutes(rand(5, 30)),
            'reference_number' => $status === 'failed' ? null : 'REF' . rand(100000, 999999),
            'failure_reason' => $status === 'failed' ? $this->getFailureReason() : null,
        ]);
    }

    private function createFutureOrders($branch, $menuItems, $date)
    {
        // Create fewer future orders (scheduled/reservations)
        $count = rand(3, 8);
        
        for ($i = 0; $i < $count; $i++) {
            $orderTime = $this->randomTimeInRange($date, '09:00', '21:00');
            $orderType = rand(0, 100) > 50 ? 'takeaway_in_call_scheduled' : 'dine_in';
            
            $order = $this->createOrder($branch, $orderTime, $orderType);
            $order->update(['status' => 'scheduled']);
            
            $this->addOrderItems($order, $menuItems, 'lunch'); // Default to lunch items
        }
    }

    private function createSpecialScenarioOrders($branch, $menuItems)
    {
        $this->command->info("  Creating special scenario orders...");

        // Scenario 1: Large group order with dietary restrictions
        $this->createLargeGroupOrder($branch, $menuItems);
        
        // Scenario 2: Rush hour stress test
        $this->createRushHourOrders($branch, $menuItems);
        
        // Scenario 3: Failed payment scenarios
        $this->createFailedPaymentOrders($branch, $menuItems);
        
        // Scenario 4: Cancelled and refunded orders
        $this->createCancelledOrders($branch, $menuItems);
        
        // Scenario 5: Kitchen capacity test
        $this->createKitchenCapacityTest($branch, $menuItems);
    }

    private function createLargeGroupOrder($branch, $menuItems)
    {
        $orderTime = now()->subDays(rand(1, 5))->setTime(19, rand(0, 59));
        
        $order = Order::create([
            'order_type' => 'dine_in',
            'branch_id' => $branch->id,
            'order_time' => $orderTime,
            'customer_name' => 'Corporate Event - Tech Solutions Ltd',
            'customer_phone' => '+94771234599',
            'status' => 'completed',
            'table_number' => '20-25', // Multiple tables
            'special_instructions' => 'Large group booking. 3 vegetarians, 2 gluten-free, 1 vegan. Please coordinate service.',
            'placed_by_admin' => true,
            'created_at' => $orderTime,
            'updated_at' => $orderTime,
        ]);

        // Add many items for large group
        $subtotal = 0;
        $itemsToAdd = [
            ['item' => $menuItems->where('category', 'appetizer')->first(), 'qty' => 8],
            ['item' => $menuItems->where('category', 'main_course')->first(), 'qty' => 12],
            ['item' => $menuItems->where('name', 'LIKE', '%vegetarian%')->first(), 'qty' => 3],
            ['item' => $menuItems->where('name', 'LIKE', '%salad%')->first(), 'qty' => 5],
            ['item' => $menuItems->where('category', 'beverage')->first(), 'qty' => 15],
            ['item' => $menuItems->where('category', 'dessert')->first(), 'qty' => 10],
        ];

        foreach ($itemsToAdd as $itemData) {
            if ($itemData['item']) {
                $totalPrice = $itemData['item']->selling_price * $itemData['qty'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['item']->id,
                    'inventory_item_id' => $itemData['item']->id,
                    'quantity' => $itemData['qty'],
                    'unit_price' => $itemData['item']->selling_price,
                    'total_price' => $totalPrice,
                ]);
                
                $subtotal += $totalPrice;
            }
        }

        $tax = $subtotal * 0.10;
        $serviceCharge = $subtotal * 0.05;
        $total = $subtotal + $tax + $serviceCharge;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $serviceCharge,
            'total' => $total,
        ]);

        $this->createPayment($order);
    }

    private function createRushHourOrders($branch, $menuItems)
    {
        $rushTime = now()->subDays(1)->setTime(12, 30); // Yesterday lunch rush
        
        // Create 20 orders within 30 minutes
        for ($i = 0; $i < 20; $i++) {
            $orderTime = $rushTime->copy()->addMinutes(rand(0, 30));
            
            $order = Order::create([
                'order_type' => rand(0, 100) > 60 ? 'takeaway_walk_in_demand' : 'dine_in',
                'branch_id' => $branch->id,
                'order_time' => $orderTime,
                'customer_name' => 'Rush Customer ' . ($i + 1),
                'customer_phone' => '+9477123' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => $i < 3 ? 'delayed' : 'completed', // Some orders delayed
                'takeaway_id' => rand(0, 100) > 60 ? 'TW' . $orderTime->format('YmdHis') . $i : null,
                'table_number' => rand(0, 100) > 60 ? null : rand(1, 15),
                'special_instructions' => $i < 3 ? 'RUSH ORDER - Customer waiting' : null,
                'placed_by_admin' => false,
                'created_at' => $orderTime,
                'updated_at' => $orderTime,
            ]);

            // Quick items during rush
            $quickItems = $menuItems->where('preparation_time', '<=', 15)->take(rand(1, 3));
            $subtotal = 0;
            
            foreach ($quickItems as $item) {
                $quantity = rand(1, 2);
                $totalPrice = $item->selling_price * $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $item->selling_price,
                    'total_price' => $totalPrice,
                ]);
                
                $subtotal += $totalPrice;
            }
            
            $tax = $subtotal * 0.10;
            $total = $subtotal + $tax;
            
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            if ($order->status === 'completed') {
                $this->createPayment($order);
            }
        }
    }

    private function createFailedPaymentOrders($branch, $menuItems)
    {
        for ($i = 0; $i < 3; $i++) {
            $orderTime = now()->subDays(rand(1, 7))->setTime(rand(11, 20), rand(0, 59));
            
            $order = $this->createOrder($branch, $orderTime, 'takeaway_online_scheduled');
            $this->addOrderItems($order, $menuItems, 'lunch');
            
            // Create failed payment
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'payment_method' => 'card',
                'status' => 'failed',
                'transaction_id' => 'TXN' . $order->id . time(),
                'payment_date' => $order->created_at->addMinutes(5),
                'failure_reason' => $this->getFailureReason(),
            ]);
            
            $order->update(['status' => 'payment_failed']);
        }
    }

    private function createCancelledOrders($branch, $menuItems)
    {
        for ($i = 0; $i < 5; $i++) {
            $orderTime = now()->subDays(rand(1, 10))->setTime(rand(9, 21), rand(0, 59));
            
            $order = $this->createOrder($branch, $orderTime, 'takeaway_in_call_scheduled');
            $this->addOrderItems($order, $menuItems, 'dinner');
            
            // Some cancelled before payment, some after
            if (rand(0, 100) > 50) {
                $this->createPayment($order);
                $order->update(['status' => 'cancelled_after_payment']);
                
                // Create refund payment
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => -$order->total, // Negative for refund
                    'payment_method' => 'refund',
                    'status' => 'completed',
                    'transaction_id' => 'REF' . $order->id . time(),
                    'payment_date' => $order->created_at->addHours(rand(1, 24)),
                    'reference_number' => 'REFUND' . rand(100000, 999999),
                ]);
            } else {
                $order->update(['status' => 'cancelled']);
            }
        }
    }

    private function createKitchenCapacityTest($branch, $menuItems)
    {
        $peakTime = now()->subDays(1)->setTime(20, 0); // Peak dinner time
        
        // Create orders that would stress kitchen capacity
        $complexItems = $menuItems->where('preparation_time', '>', 20);
        
        for ($i = 0; $i < 10; $i++) {
            $orderTime = $peakTime->copy()->addMinutes(rand(0, 15));
            
            $order = Order::create([
                'order_type' => 'dine_in',
                'branch_id' => $branch->id,
                'order_time' => $orderTime,
                'customer_name' => 'Kitchen Test ' . ($i + 1),
                'customer_phone' => '+9477199' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => $i < 2 ? 'kitchen_delayed' : 'completed',
                'table_number' => rand(1, 20),
                'special_instructions' => $i < 2 ? 'Kitchen overwhelmed - extended preparation time' : null,
                'placed_by_admin' => false,
                'created_at' => $orderTime,
                'updated_at' => $orderTime,
            ]);

            // Add complex items that take time
            $subtotal = 0;
            foreach ($complexItems->random(rand(2, 4)) as $item) {
                $quantity = rand(1, 2);
                $totalPrice = $item->selling_price * $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $item->selling_price,
                    'total_price' => $totalPrice,
                ]);
                
                $subtotal += $totalPrice;
            }
            
            $tax = $subtotal * 0.10;
            $serviceCharge = $subtotal * 0.05;
            $total = $subtotal + $tax + $serviceCharge;
            
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'total' => $total,
            ]);

            if ($order->status === 'completed') {
                $this->createPayment($order);
            }
        }
    }

    // Helper methods
    private function randomTimeInRange($date, $startTime, $endTime)
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $startTime);
        $end = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $endTime);
        
        $randomTimestamp = rand($start->timestamp, $end->timestamp);
        return Carbon::createFromTimestamp($randomTimestamp);
    }

    private function getOrderType($period)
    {
        $types = [
            'breakfast' => ['takeaway_walk_in_demand', 'dine_in'],
            'lunch' => ['takeaway_walk_in_demand', 'takeaway_in_call_scheduled', 'dine_in'],
            'dinner' => ['dine_in', 'takeaway_in_call_scheduled', 'takeaway_online_scheduled'],
            'late_night' => ['takeaway_walk_in_demand', 'dine_in'],
        ];
        
        return $types[$period][array_rand($types[$period])];
    }

    private function getOrderStatus($orderTime)
    {
        if ($orderTime->isFuture()) {
            return 'scheduled';
        }
        
        $statuses = ['completed', 'preparing', 'ready', 'cancelled'];
        $weights = [70, 15, 10, 5]; // Weighted distribution
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $i => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $statuses[$i];
            }
        }
        
        return 'completed';
    }

    private function getMenuItemsForPeriod($menuItems, $period)
    {
        $categories = [
            'breakfast' => ['breakfast', 'beverage', 'pastry'],
            'lunch' => ['appetizer', 'main_course', 'beverage', 'salad'],
            'dinner' => ['appetizer', 'main_course', 'dessert', 'beverage'],
            'late_night' => ['snack', 'beverage', 'dessert'],
        ];
        
        $periodCategories = $categories[$period] ?? ['main_course', 'beverage'];
        
        return $menuItems->filter(function ($item) use ($periodCategories) {
            return in_array($item->category, $periodCategories);
        });
    }

    private function getItemCountForPeriod($period)
    {
        $counts = [
            'breakfast' => rand(1, 3),
            'lunch' => rand(2, 5),
            'dinner' => rand(3, 6),
            'late_night' => rand(1, 3),
        ];
        
        return $counts[$period] ?? rand(2, 4);
    }

    private function getRandomInstructions()
    {
        $instructions = [
            'No spicy',
            'Extra sauce on side',
            'Well done',
            'Medium rare',
            'No onions',
            'Extra cheese',
            'Gluten free bread',
            'Vegan option',
            'Less salt',
            'Extra vegetables',
            null, null, null // Many orders have no special instructions
        ];
        
        return $instructions[array_rand($instructions)];
    }

    private function getItemInstructions()
    {
        $instructions = [
            'Medium spice level',
            'No cilantro',
            'Sauce on side',
            'Extra crispy',
            'Well done',
            'Light seasoning',
            null, null, null, null // Most items have no special instructions
        ];
        
        return $instructions[array_rand($instructions)];
    }

    private function getFailureReason()
    {
        $reasons = [
            'Insufficient funds',
            'Card declined',
            'Payment gateway timeout',
            'Invalid card details',
            'Network error',
            'Bank authorization failed'
        ];
        
        return $reasons[array_rand($reasons)];
    }

    private function updateOrderStatus($order)
    {
        // Update status based on how old the order is
        $hoursOld = $order->created_at->diffInHours(now());
        
        if ($hoursOld > 2) {
            $order->update(['status' => 'completed']);
        } elseif ($hoursOld > 1) {
            $order->update(['status' => rand(0, 100) > 20 ? 'completed' : 'ready']);
        }
    }
}
