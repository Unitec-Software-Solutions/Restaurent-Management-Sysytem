<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\User;
use App\Enums\OrderType;
use Carbon\Carbon;

class GuestActivitySeeder extends Seeder
{
    /**
     * Generate public-facing guest interactions
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding Guest Interactions...');
        
        $organizations = Organization::with('branches')->get();
        
        foreach ($organizations as $organization) {
            $this->createGuestActivitiesForOrganization($organization);
        }
        
        $this->command->info('✅ Guest interactions seeded successfully');
    }

    private function createGuestActivitiesForOrganization(Organization $organization): void
    {
        $this->command->info("  👥 Creating guest activities for: {$organization->name}");
        
        foreach ($organization->branches as $branch) {
            // Create guest users (unregistered customers)
            $this->createGuestUsers($branch);
            
            // Simulate menu browsing activities
            $this->simulateMenuViewing($branch);
            
            // Create guest carts and orders
            $this->createGuestCartsAndOrders($branch);
            
            // Create guest reservations
            $this->createGuestReservations($branch);
            
            // Simulate order tracking scenarios
            $this->simulateOrderTracking($branch);
        }
    }

    private function createGuestUsers(Branch $branch): void
    {
        // Create anonymous guest users (potential customers)
        $guestCount = rand(10, 20);
        
        for ($i = 0; $i < $guestCount; $i++) {
            $user = User::create([
                'name' => $this->generateGuestName(),
                'email' => $this->generateGuestEmail(),
                'phone_number' => $this->generatePhoneNumber(),
                'password' => bcrypt('guest123'),
                'is_registered' => false, // Guest user
                'organization_id' => null, // Not tied to specific organization
                'branch_id' => null,
                'email_verified_at' => null,
                'guest_session_id' => 'guest_' . uniqid(),
                'preferences' => json_encode([
                    'dietary_preferences' => $this->getRandomDietaryPreferences(),
                    'spice_tolerance' => ['mild', 'medium', 'hot'][rand(0, 2)],
                    'preferred_cuisine' => $this->getRandomCuisinePreference(),
                    'price_range' => ['budget', 'moderate', 'premium'][rand(0, 2)]
                ]),
                'last_activity' => now()->subMinutes(rand(1, 1440)), // Active within last 24 hours
                'created_at' => now()->subDays(rand(0, 30))
            ]);
        }
        
        $this->command->info("    👤 Created {$guestCount} guest users for {$branch->name}");
    }

    private function simulateMenuViewing(Branch $branch): void
    {
        $menuItems = MenuItem::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->get();
        
        if ($menuItems->isEmpty()) {
            return;
        }
        
        $guestUsers = User::where('is_registered', false)
            ->whereNotNull('guest_session_id')
            ->get();
        
        $viewingActivities = [];
        
        foreach ($guestUsers as $guest) {
            // Each guest views multiple menu items
            $viewCount = rand(3, 15);
            $viewedItems = $menuItems->random(min($viewCount, $menuItems->count()));
            
            foreach ($viewedItems as $item) {
                $viewingActivities[] = [
                    'guest_id' => $guest->id,
                    'menu_item_id' => $item->id,
                    'branch_id' => $branch->id,
                    'view_duration' => rand(5, 180), // 5 seconds to 3 minutes
                    'viewed_at' => now()->subMinutes(rand(1, 1440)),
                    'device_type' => ['mobile', 'desktop', 'tablet'][rand(0, 2)],
                    'source' => ['direct', 'search', 'social_media', 'referral'][rand(0, 3)]
                ];
            }
        }
        
        // In a real system, you'd store these in a menu_views table
        $this->command->info("    👀 Simulated " . count($viewingActivities) . " menu views for {$branch->name}");
        
        // Simulate stock indicator views (items that are out of stock)
        $outOfStockCount = $menuItems->where('is_available', false)->count();
        if ($outOfStockCount > 0) {
            $this->command->info("    ❌ {$outOfStockCount} items showing as out of stock");
        }
    }

    private function createGuestCartsAndOrders(Branch $branch): void
    {
        $menuItems = MenuItem::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->get();
        
        if ($menuItems->isEmpty()) {
            return;
        }
        
        $guestUsers = User::where('is_registered', false)
            ->whereNotNull('guest_session_id')
            ->get();
        
        $cartCount = 0;
        $orderCount = 0;
        
        foreach ($guestUsers as $guest) {
            $action = rand(1, 100);
            
            if ($action <= 40) {
                // 40% chance: Create cart but abandon it
                $this->createAbandonedCart($guest, $branch, $menuItems);
                $cartCount++;
            } elseif ($action <= 70) {
                // 30% chance: Create cart and complete order
                $this->createGuestOrder($guest, $branch, $menuItems);
                $orderCount++;
            }
            // 30% chance: Just browse without adding to cart
        }
        
        $this->command->info("    🛒 Created {$cartCount} abandoned carts and {$orderCount} guest orders for {$branch->name}");
    }

    private function createAbandonedCart(User $guest, Branch $branch, $menuItems): void
    {
        // In a real system, you'd have a shopping_carts table
        $cartItems = [];
        $itemCount = rand(1, 5);
        $selectedItems = $menuItems->random(min($itemCount, $menuItems->count()));
        
        foreach ($selectedItems as $item) {
            $cartItems[] = [
                'guest_id' => $guest->id,
                'menu_item_id' => $item->id,
                'quantity' => rand(1, 3),
                'unit_price' => $item->price,
                'added_at' => now()->subMinutes(rand(5, 120)),
                'abandoned_at' => now()->subMinutes(rand(1, 60))
            ];
        }
        
        // Simulate cart abandonment reasons
        $abandonmentReasons = [
            'high_delivery_fee',
            'long_delivery_time',
            'changed_mind',
            'found_better_option',
            'payment_issue',
            'minimum_order_not_met'
        ];
        
        $abandonment = [
            'guest_id' => $guest->id,
            'branch_id' => $branch->id,
            'items' => $cartItems,
            'total_value' => collect($cartItems)->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            }),
            'abandonment_reason' => $abandonmentReasons[rand(0, count($abandonmentReasons) - 1)],
            'abandoned_at' => now()->subMinutes(rand(1, 60))
        ];
    }

    private function createGuestOrder(User $guest, Branch $branch, $menuItems): void
    {
        $orderDateTime = now()->subMinutes(rand(30, 1440)); 
        
        $order = Order::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'user_id' => $guest->id,
            'customer_name' => $guest->name,
            'customer_phone' => $guest->phone_number,
            'customer_email' => $guest->email,
            'order_number' => $this->generateGuestOrderNumber($branch, $orderDateTime),
            'order_type' => OrderType::TAKEAWAY_WALK_IN_DEMAND, // Only use takeaway for guest orders
            'order_date' => $orderDateTime->toDateString(),
            'status' => $this->determineGuestOrderStatus($orderDateTime),
            'payment_status' => 'pending',
            'subtotal' => 0,
            'tax_amount' => 0,
            'delivery_fee' => rand(0, 1) ? rand(150, 300) : 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'payment_method' => ['card', 'mobile', 'cash'][rand(0, 2)],
            'special_instructions' => $this->getRandomGuestInstructions(),
            'delivery_address' => $this->generateDeliveryAddress(),
            'guest_order' => true,
            'device_info' => json_encode([
                'device_type' => ['mobile', 'desktop', 'tablet'][rand(0, 2)],
                'browser' => ['chrome', 'firefox', 'safari', 'edge'][rand(0, 3)],
                'platform' => ['android', 'ios', 'windows', 'macos'][rand(0, 3)]
            ]),
            'created_at' => $orderDateTime,
            'estimated_delivery_time' => $orderDateTime->copy()->addMinutes(rand(30, 90))
        ]);

        // Add order items
        $this->addGuestOrderItems($order, $menuItems);
        
        // Calculate totals
        $this->calculateGuestOrderTotals($order);
        
        // Create order tracking entries
        $this->createOrderTrackingEntries($order);
    }

    private function addGuestOrderItems(Order $order, $menuItems): void
    {
        $itemCount = rand(1, 4);
        $selectedItems = $menuItems->random(min($itemCount, $menuItems->count()));
        
        foreach ($selectedItems as $menuItem) {
            $quantity = rand(1, 2); // Guests typically order smaller quantities
            
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'quantity' => $quantity,
                'unit_price' => $menuItem->price,
                'total_price' => $menuItem->price * $quantity,
                'subtotal' => $menuItem->price * $quantity,
                'special_instructions' => rand(0, 1) ? $this->getRandomItemSpecialRequest() : null,
                'status' => 'pending'
            ]);
        }
    }

    private function calculateGuestOrderTotals(Order $order): void
    {
        $subtotal = $order->orderItems()->sum('total_price');
        $taxAmount = $subtotal * 0.12; // 12% tax
        $deliveryFee = $order->delivery_fee ?? 0;
        $totalAmount = $subtotal + $taxAmount + $deliveryFee;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_status' => $totalAmount > 0 ? 'pending' : 'paid'
        ]);
    }

    private function createGuestReservations(Branch $branch): void
    {
        if ($branch->reservation_fee <= 0) {
            return; // Skip branches that don't accept reservations
        }
        
        $guestUsers = User::where('is_registered', false)
            ->whereNotNull('guest_session_id')
            ->get();
        
        $reservationCount = rand(5, 15);
        $this->command->info("    📅 Creating {$reservationCount} guest reservations for {$branch->name}");
        
        for ($i = 0; $i < $reservationCount; $i++) {
            $guest = $guestUsers->random();
            $this->createGuestReservation($guest, $branch);
        }
    }

    private function createGuestReservation(User $guest, Branch $branch): void
    {
        $reservationDateTime = now()->addDays(rand(1, 30))->setHour(rand(11, 21))->setMinute([0, 15, 30, 45][rand(0, 3)]);
        
        $reservation = Reservation::create([
            'branch_id' => $branch->id,
            'user_id' => $guest->id,
            'name' => $guest->name,
            'phone' => $guest->phone_number,
            'email' => $guest->email,
            'number_of_people' => rand(2, 6),
            'date' => $reservationDateTime->toDateString(),
            'start_time' => $reservationDateTime,
            'end_time' => $reservationDateTime->copy()->addMinutes(rand(90, 180)),
            'status' => 'pending',
            'type' => 'online',
            'comments' => $this->getRandomReservationRequest(),
            'reservation_fee' => rand(0, 1) ? rand(5, 25) : 0
        ]);
        
        // Create confirmation workflow
        if (rand(0, 1)) {
            $reservation->update([
                'status' => 'confirmed'
            ]);
        }
    }

    private function simulateOrderTracking(Branch $branch): void
    {
        $guestOrders = Order::where('branch_id', $branch->id)
            ->where('guest_order', true)
            ->get();
        
        $trackingActivities = 0;
        
        foreach ($guestOrders as $order) {
            // Simulate multiple tracking views per order
            $trackingViews = rand(1, 5);
            
            for ($i = 0; $i < $trackingViews; $i++) {
                $trackingActivities++;
                
                // In a real system, you'd store these tracking activities
                $trackingActivity = [
                    'order_id' => $order->id,
                    'guest_id' => $order->user_id,
                    'tracking_time' => now()->subMinutes(rand(1, 120)),
                    'device_type' => ['mobile', 'desktop'][rand(0, 1)],
                    'page_view_duration' => rand(10, 300), // 10 seconds to 5 minutes
                    'status_at_view' => $order->status
                ];
            }
        }
        
        if ($trackingActivities > 0) {
            $this->command->info("    📱 Simulated {$trackingActivities} order tracking activities for {$branch->name}");
        }
    }

    private function createOrderTrackingEntries(Order $order): void
    {
        // Create realistic order status progression
        $statusProgression = $this->getOrderStatusProgression(is_object($order->order_type) ? $order->order_type->value : $order->order_type);
        $currentTime = $order->created_at;
        
        foreach ($statusProgression as $status) {
            $currentTime = $currentTime->copy()->addMinutes(rand(5, 30));
            
            // In a real system, you'd have an order_status_history table
            $statusEntry = [
                'order_id' => $order->id,
                'status' => $status,
                'timestamp' => $currentTime,
                'notes' => $this->getStatusChangeNotes($status),
                'updated_by' => 'system'
            ];
            
            // Update order status if it matches current progression
            if ($currentTime->isPast() && $status === $order->status) {
                break;
            }
        }
    }

    // Helper methods
    private function generateGuestName(): string
    {
        $firstNames = ['Alex', 'Sam', 'Casey', 'Jordan', 'Taylor', 'Morgan', 'Avery', 'Blake'];
        $lastNames = ['Guest', 'Visitor', 'Customer', 'User', 'Anonymous'];
        
        return $firstNames[rand(0, count($firstNames) - 1)] . ' ' . $lastNames[rand(0, count($lastNames) - 1)];
    }

    private function generateGuestEmail(): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com'];
        $username = 'guest' . rand(100000, 999999) . uniqid();
        
        return $username . '@' . $domains[rand(0, count($domains) - 1)];
    }

    private function generatePhoneNumber(): string
    {
        return '+94 7' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    }

    private function getRandomDietaryPreferences(): array
    {
        $preferences = ['vegetarian', 'vegan', 'halal', 'gluten_free', 'dairy_free', 'keto', 'low_sodium'];
        return array_slice($preferences, 0, rand(0, 3));
    }

    private function getRandomCuisinePreference(): string
    {
        return ['sri_lankan', 'indian', 'chinese', 'italian', 'thai', 'japanese'][rand(0, 5)];
    }

    private function generateGuestOrderNumber(Branch $branch, Carbon $dateTime): string
    {
        // Add microseconds and a larger random number to ensure uniqueness
        return 'GUEST-' . $branch->id . '-' . $dateTime->format('Ymd') . '-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT) . substr(microtime(), 2, 3);
    }

    private function determineGuestOrderStatus(Carbon $orderDateTime): string
    {
        $hoursAgo = $orderDateTime->diffInHours(now());
        
        if ($hoursAgo < 1) {
            return ['pending', 'confirmed'][rand(0, 1)];
        } elseif ($hoursAgo < 2) {
            return ['confirmed', 'preparing'][rand(0, 1)];
        } elseif ($hoursAgo < 4) {
            return ['preparing', 'ready', 'out_for_delivery'][rand(0, 2)];
        } else {
            return ['completed', 'delivered'][rand(0, 1)];
        }
    }

    private function getRandomGuestInstructions(): ?string
    {
        $instructions = [
            'Please call when you arrive',
            'Leave at the door',
            'Ring the bell twice',
            'Contactless delivery preferred',
            'Include extra napkins',
            'Pack items separately'
        ];
        
        return rand(0, 1) ? $instructions[rand(0, count($instructions) - 1)] : null;
    }

    private function generateDeliveryAddress(): string
    {
        $addresses = [
            '123 Residential Lane, Colombo 05',
            '456 Apartment Complex, Dehiwala',
            '789 Housing Scheme, Nugegoda',
            '321 Condominium Tower, Bambalapitiya',
            '654 Garden City, Mount Lavinia'
        ];
        
        return $addresses[rand(0, count($addresses) - 1)];
    }

    private function getRandomItemSpecialRequest(): string
    {
        $requests = [
            'Extra spicy',
            'Mild spice level',
            'No vegetables',
            'Extra sauce',
            'Well done',
            'Medium rare'
        ];
        
        return $requests[rand(0, count($requests) - 1)];
    }

    private function getRandomReservationRequest(): ?string
    {
        $requests = [
            'Window table preferred',
            'Quiet corner please',
            'Birthday celebration',
            'Business meeting setup',
            'High chair needed',
            'Wheelchair accessible table'
        ];
        
        return rand(0, 1) ? $requests[rand(0, count($requests) - 1)] : null;
    }

    private function getOrderStatusProgression($orderType): array
    {
        if ($orderType instanceof OrderType && $orderType->isTakeaway()) {
            return ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
        }
        
        return match($orderType) {
            'delivery' => ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'completed'],
            default => ['pending', 'confirmed', 'preparing', 'ready', 'completed']
        };
    }

    private function getStatusChangeNotes(string $status): string
    {
        return match($status) {
            'pending' => 'Order received and awaiting confirmation',
            'confirmed' => 'Order confirmed and sent to kitchen',
            'preparing' => 'Kitchen started preparing your order',
            'ready' => 'Order is ready for pickup/delivery',
            'out_for_delivery' => 'Order is on the way',
            'delivered' => 'Order has been delivered',
            'completed' => 'Order completed successfully',
            default => 'Order status updated'
        };
    }
}
