<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Table;
use App\Models\Kot;
use App\Models\KotItem;
use Carbon\Carbon;

/**
 * Exhaustive Order Seeder
 * 
 * Creates comprehensive order lifecycle scenarios:
 * - Guest orders (walk-in, phone, online)
 * - Staff orders with different permission levels
 * - Partial orders (split bills, partial payments)
 * - Cancelled orders (customer request, kitchen issues, payment failures)
 * - Refunded orders (full/partial refunds, quality issues)
 * - Special requests (dietary modifications, allergies, custom preparations)
 * - Time-based order patterns (peak hours, late night, early morning)
 * - Group orders and split billing
 * - Takeaway vs dine-in workflows
 * - Kitchen workflow integration (KOT generation and fulfillment)
 */
class ExhaustiveOrderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  🛒 Creating order lifecycle scenarios...');

        $organizations = Organization::with(['branches.tables', 'branches.menuItems', 'users'])->get();

        foreach ($organizations as $org) {
            $this->createOrdersForOrganization($org);
        }

        $this->command->info("  ✅ Created comprehensive order scenarios");
    }

    private function createOrdersForOrganization(Organization $org): void
    {
        foreach ($org->branches as $branch) {
            // Create various order scenarios
            $this->createGuestOrders($org, $branch);
            $this->createStaffOrders($org, $branch);
            $this->createPartialOrders($org, $branch);
            $this->createCancelledOrders($org, $branch);
            $this->createRefundedOrders($org, $branch);
            $this->createSpecialRequestOrders($org, $branch);
            $this->createPeakHourOrders($org, $branch);
            $this->createGroupOrders($org, $branch);
            $this->createTakeawayOrders($org, $branch);
            $this->createComplexKitchenOrders($org, $branch);
        }
    }

    private function createGuestOrders(Organization $org, Branch $branch): void
    {
        $guestOrderTypes = [
            'walk_in' => ['count' => 15, 'payment_method' => ['cash', 'card']],
            'phone_order' => ['count' => 8, 'payment_method' => ['cash_on_delivery', 'card_payment']],
            'online_order' => ['count' => 12, 'payment_method' => ['online_payment', 'digital_wallet']],
        ];

        foreach ($guestOrderTypes as $orderType => $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $order = $this->createBaseOrder($org, $branch, $orderType);
                
                // Add items to order
                $this->addRandomItemsToOrder($order, $branch, rand(1, 6));
                
                // Set payment details
                $order->update([
                    'payment_method' => $config['payment_method'][array_rand($config['payment_method'])],
                    'payment_status' => $this->getRandomPaymentStatus(),
                    'total_amount' => $order->orderItems->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ]);

                // Create KOT if order is confirmed
                if ($order->status === 'confirmed') {
                    $this->createKotForOrder($order);
                }
            }
        }
    }

    private function createStaffOrders(Organization $org, Branch $branch): void
    {
        $staffUsers = $org->users->where('is_admin', false)->take(5);
        
        foreach ($staffUsers as $staff) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                $order = $this->createBaseOrder($org, $branch, 'staff_order');
                $order->update([
                    'user_id' => $staff->id,
                    'staff_discount_percentage' => rand(10, 25),
                    'notes' => 'Staff order - ' . $staff->name,
                ]);

                $this->addRandomItemsToOrder($order, $branch, rand(1, 3));
                $this->finalizeOrder($order);
            }
        }
    }

    private function createPartialOrders(Organization $org, Branch $branch): void
    {
        for ($i = 0; $i < 8; $i++) {
            $order = $this->createBaseOrder($org, $branch, 'dine_in');
            $this->addRandomItemsToOrder($order, $branch, rand(4, 8));
            
            $totalAmount = $order->orderItems->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });

            // Create partial payment
            $partialAmount = $totalAmount * (rand(30, 70) / 100);
            
            $order->update([
                'total_amount' => $totalAmount,
                'paid_amount' => $partialAmount,
                'payment_status' => 'partial',
                'status' => 'in_progress',
                'notes' => 'Partial payment received. Balance: ' . ($totalAmount - $partialAmount),
            ]);
        }
    }

    private function createCancelledOrders(Organization $org, Branch $branch): void
    {
        $cancellationReasons = [
            'customer_request' => 'Customer cancelled due to time constraints',
            'kitchen_issue' => 'Cancelled due to ingredient unavailability',
            'payment_failure' => 'Payment processing failed',
            'system_error' => 'Order cancelled due to system error',
            'duplicate_order' => 'Duplicate order detected and cancelled',
        ];

        foreach ($cancellationReasons as $reason => $description) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $order = $this->createBaseOrder($org, $branch, 'cancelled');
                $this->addRandomItemsToOrder($order, $branch, rand(1, 4));
                
                $order->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_at' => Carbon::now()->subMinutes(rand(5, 120)),
                    'cancelled_by' => $this->getRandomStaffUser($org)?->id,
                    'notes' => $description,
                    'refund_amount' => $order->paid_amount ?? 0,
                ]);
            }
        }
    }

    private function createRefundedOrders(Organization $org, Branch $branch): void
    {
        $refundReasons = [
            'quality_issue' => 'Food quality below standard',
            'wrong_order' => 'Incorrect order delivered',
            'long_wait_time' => 'Excessive waiting time',
            'allergy_concern' => 'Allergen contamination discovered',
            'customer_complaint' => 'Customer dissatisfaction',
        ];

        foreach ($refundReasons as $reason => $description) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $order = $this->createBaseOrder($org, $branch, 'completed');
                $this->addRandomItemsToOrder($order, $branch, rand(2, 5));
                $this->finalizeOrder($order);
                
                // Process refund
                $refundType = rand(0, 1) ? 'full' : 'partial';
                $refundAmount = $refundType === 'full' 
                    ? $order->total_amount 
                    : $order->total_amount * (rand(30, 80) / 100);
                
                $order->update([
                    'status' => 'refunded',
                    'refund_reason' => $reason,
                    'refund_type' => $refundType,
                    'refund_amount' => $refundAmount,
                    'refunded_at' => Carbon::now()->subMinutes(rand(30, 180)),
                    'refunded_by' => $this->getRandomStaffUser($org)?->id,
                    'notes' => $description . " - {$refundType} refund processed",
                ]);
            }
        }
    }

    private function createSpecialRequestOrders(Organization $org, Branch $branch): void
    {
        $specialRequests = [
            'no_onions' => 'No onions due to allergy',
            'extra_spicy' => 'Make it extra spicy',
            'gluten_free' => 'Gluten-free preparation required',
            'vegan_option' => 'Convert to vegan option',
            'less_salt' => 'Low sodium preparation',
            'extra_sauce' => 'Extra sauce on the side',
            'well_done' => 'Cook well done',
            'kids_portion' => 'Smaller portion for child',
        ];

        foreach ($specialRequests as $requestType => $description) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $order = $this->createBaseOrder($org, $branch, 'dine_in');
                $this->addRandomItemsToOrder($order, $branch, rand(1, 3), true, $requestType);
                
                $order->update([
                    'special_instructions' => $description,
                    'has_special_requests' => true,
                    'preparation_notes' => "Kitchen Alert: {$description}",
                ]);
                
                $this->finalizeOrder($order);
                $this->createKotForOrder($order, $description);
            }
        }
    }

    private function createPeakHourOrders(Organization $org, Branch $branch): void
    {
        $peakHours = [
            ['start' => '12:00', 'end' => '14:00', 'period' => 'lunch_rush'],
            ['start' => '18:00', 'end' => '21:00', 'period' => 'dinner_rush'],
            ['start' => '22:00', 'end' => '23:30', 'period' => 'late_night'],
        ];

        foreach ($peakHours as $period) {
            $orderCount = $period['period'] === 'lunch_rush' ? 20 : 
                         ($period['period'] === 'dinner_rush' ? 25 : 8);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $orderTime = Carbon::createFromFormat('H:i', $period['start'])
                    ->addMinutes(rand(0, 120));
                
                $order = $this->createBaseOrder($org, $branch, 'dine_in');
                $order->update([
                    'created_at' => $orderTime,
                    'peak_hour_surcharge' => $period['period'] === 'late_night' ? 10 : 0,
                    'rush_order' => in_array($period['period'], ['lunch_rush', 'dinner_rush']),
                ]);
                
                $itemCount = $period['period'] === 'late_night' ? rand(1, 3) : rand(2, 6);
                $this->addRandomItemsToOrder($order, $branch, $itemCount);
                $this->finalizeOrder($order);
                
                if ($order->status === 'confirmed') {
                    $this->createKotForOrder($order);
                }
            }
        }
    }

    private function createGroupOrders(Organization $org, Branch $branch): void
    {
        for ($i = 0; $i < 6; $i++) {
            $groupSize = rand(4, 12);
            $mainOrder = $this->createBaseOrder($org, $branch, 'dine_in');
            
            $mainOrder->update([
                'group_size' => $groupSize,
                'split_billing' => rand(0, 1) ? true : false,
                'notes' => "Group order for {$groupSize} people",
            ]);

            // Add multiple items for group
            $this->addRandomItemsToOrder($order = $mainOrder, $branch, rand(6, 15));
            
            if ($mainOrder->split_billing) {
                // Create sub-orders for split billing
                $this->createSplitBillOrders($mainOrder, $org, $branch, $groupSize);
            }
            
            $this->finalizeOrder($mainOrder);
            $this->createKotForOrder($mainOrder);
        }
    }

    private function createTakeawayOrders(Organization $org, Branch $branch): void
    {
        for ($i = 0; $i < 12; $i++) {
            $order = $this->createBaseOrder($org, $branch, 'takeaway');
            
            $order->update([
                'order_type' => 'takeaway',
                'pickup_time' => Carbon::now()->addMinutes(rand(20, 60)),
                'customer_phone' => '+94 7' . rand(10000000, 99999999),
                'packaging_fee' => rand(50, 200) / 100,
            ]);
            
            $this->addRandomItemsToOrder($order, $branch, rand(1, 4));
            $this->finalizeOrder($order);
            
            // Mark as ready for pickup
            if (rand(0, 1)) {
                $order->update([
                    'status' => 'ready_for_pickup',
                    'ready_at' => Carbon::now()->subMinutes(rand(5, 30)),
                ]);
            }
        }
    }

    private function createComplexKitchenOrders(Organization $org, Branch $branch): void
    {
        for ($i = 0; $i < 8; $i++) {
            $order = $this->createBaseOrder($org, $branch, 'dine_in');
            
            // Add items with different preparation times
            $complexItems = $branch->menuItems()
                ->where('preparation_time', '>', 20)
                ->take(rand(2, 4))
                ->get();
            
            foreach ($complexItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity' => rand(1, 2),
                    'unit_price' => $item->price,
                    'special_instructions' => 'Complex preparation required',
                    'estimated_prep_time' => $item->preparation_time,
                ]);
            }
            
            $this->finalizeOrder($order);
            
            // Create complex KOT with multiple stations
            $kot = $this->createKotForOrder($order);
            $this->createMultiStationKotItems($kot, $complexItems);
        }
    }

    private function createBaseOrder(Organization $org, Branch $branch, string $orderType): Order
    {
        $table = $branch->tables->random();
        
        return Order::create([
            'organization_id' => $org->id,
            'branch_id' => $branch->id,
            'table_id' => $orderType === 'takeaway' ? null : $table->id,
            'order_number' => $this->generateOrderNumber($branch),
            'order_type' => $orderType,
            'status' => $this->getInitialOrderStatus($orderType),
            'created_at' => $this->getRandomOrderTime(),
            'customer_name' => $this->getRandomCustomerName(),
            'server_id' => $this->getRandomStaffUser($org)?->id,
        ]);
    }

    private function addRandomItemsToOrder(Order $order, Branch $branch, int $itemCount, bool $hasSpecialRequest = false, ?string $requestType = null): void
    {
        $menuItems = $branch->menuItems()->where('is_available', true)->take(20)->get();
        
        for ($i = 0; $i < $itemCount; $i++) {
            $item = $menuItems->random();
            $quantity = rand(1, 3);
            
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item->id,
                'quantity' => $quantity,
                'unit_price' => $item->price,
                'special_instructions' => $hasSpecialRequest ? $this->getSpecialInstruction($requestType) : null,
            ]);
        }
    }

    private function finalizeOrder(Order $order): void
    {
        $totalAmount = $order->orderItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $order->update([
            'subtotal' => $totalAmount,
            'tax_amount' => $totalAmount * 0.12, // 12% tax
            'total_amount' => $totalAmount * 1.12,
            'payment_status' => $this->getRandomPaymentStatus(),
        ]);
    }

    private function createKotForOrder(Order $order): Kot
    {
        $kot = Kot::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'kot_number' => $this->generateKotNumber($order->branch),
            'status' => 'pending',
            'priority_level' => $this->getKotPriority($order),
            'estimated_completion_time' => Carbon::now()->addMinutes(
                $order->orderItems->max('estimated_prep_time') ?? 30
            ),
        ]);

        foreach ($order->orderItems as $orderItem) {
            KotItem::create([
                'kot_id' => $kot->id,
                'order_item_id' => $orderItem->id,
                'menu_item_id' => $orderItem->menu_item_id,
                'quantity' => $orderItem->quantity,
                'status' => 'pending',
                'kitchen_station_id' => $this->getRandomKitchenStation($order->branch),
            ]);
        }

        return $kot;
    }

    private function createSplitBillOrders(Order $mainOrder, Organization $org, Branch $branch, int $groupSize): void
    {
        $splitCount = min($groupSize, rand(2, 4));
        
        for ($i = 0; $i < $splitCount; $i++) {
            $splitOrder = Order::create([
                'organization_id' => $org->id,
                'branch_id' => $branch->id,
                'parent_order_id' => $mainOrder->id,
                'order_number' => $mainOrder->order_number . '-' . ($i + 1),
                'order_type' => 'split_bill',
                'status' => 'completed',
                'customer_name' => $this->getRandomCustomerName(),
            ]);
            
            // Assign some items to split order
            $itemsToAssign = $mainOrder->orderItems->random(rand(1, 3));
            foreach ($itemsToAssign as $item) {
                OrderItem::create([
                    'order_id' => $splitOrder->id,
                    'menu_item_id' => $item->menu_item_id,
                    'quantity' => 1,
                    'unit_price' => $item->unit_price,
                ]);
            }
            
            $this->finalizeOrder($splitOrder);
        }
    }

    private function createMultiStationKotItems(Kot $kot, $complexItems): void
    {
        $stations = ['grill', 'saute', 'cold_prep', 'dessert_station'];
        
        foreach ($kot->kotItems as $kotItem) {
            $kotItem->update([
                'kitchen_station_id' => $stations[array_rand($stations)],
                'preparation_sequence' => rand(1, 3),
                'cooking_instructions' => $this->getCookingInstructions(),
            ]);
        }
    }

    // Helper methods
    private function generateOrderNumber(Branch $branch): string
    {
        return 'ORD-' . $branch->id . '-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function generateKotNumber(Branch $branch): string
    {
        return 'KOT-' . $branch->id . '-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function getRandomOrderTime(): Carbon
    {
        return Carbon::now()->subDays(rand(0, 7))->setTime(rand(8, 23), rand(0, 59));
    }

    private function getRandomCustomerName(): string
    {
        $names = [
            'John Silva', 'Mary Fernando', 'David Perera', 'Sarah Wickramasinghe',
            'Michael De Silva', 'Lisa Jayawardena', 'James Gunasekara', 'Jennifer Herath',
            'Robert Kumara', 'Linda Mendis', 'William Gunaratne', 'Patricia Ranasinghe'
        ];
        
        return $names[array_rand($names)];
    }

    private function getRandomStaffUser(Organization $org): ?User
    {
        return $org->users->where('is_admin', false)->random();
    }

    private function getInitialOrderStatus(string $orderType): string
    {
        $statuses = ['pending', 'confirmed', 'in_progress'];
        return $statuses[array_rand($statuses)];
    }

    private function getRandomPaymentStatus(): string
    {
        $statuses = ['pending', 'paid', 'partial', 'failed'];
        $weights = [10, 70, 15, 5]; // Weighted random
        
        $rand = rand(1, 100);
        $cumulativeWeight = 0;
        
        foreach ($weights as $index => $weight) {
            $cumulativeWeight += $weight;
            if ($rand <= $cumulativeWeight) {
                return $statuses[$index];
            }
        }
        
        return 'paid';
    }

    private function getSpecialInstruction(string $requestType): string
    {
        $instructions = [
            'no_onions' => 'Please prepare without onions - customer allergy',
            'extra_spicy' => 'Extra spicy preparation requested',
            'gluten_free' => 'IMPORTANT: Gluten-free preparation required',
            'vegan_option' => 'Convert to vegan option - no animal products',
            'less_salt' => 'Reduce salt content for health reasons',
            'extra_sauce' => 'Serve extra sauce on the side',
            'well_done' => 'Cook thoroughly - well done',
            'kids_portion' => 'Prepare smaller portion for child',
        ];
        
        return $instructions[$requestType] ?? 'Special preparation required';
    }

    private function getKotPriority(Order $order): string
    {
        if ($order->rush_order) return 'high';
        if ($order->has_special_requests) return 'medium';
        return 'normal';
    }

    private function getRandomKitchenStation(Branch $branch): int
    {
        // Assuming kitchen stations exist, return a random one
        return rand(1, 5);
    }

    private function getCookingInstructions(): string
    {
        $instructions = [
            'Standard preparation',
            'Medium heat, careful not to overcook',
            'Fresh ingredients only',
            'Check temperature before serving',
            'Garnish with fresh herbs',
            'Serve immediately while hot',
        ];
        
        return $instructions[array_rand($instructions)];
    }
}
