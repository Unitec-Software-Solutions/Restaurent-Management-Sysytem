<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExhaustiveEdgeCaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private $edgeCaseData = [];

    /**
     * Seed exhaustive edge case scenarios for comprehensive system testing
     */
    public function run(): void
    {
        $this->command->info('⚡ Seeding Exhaustive Edge Case Scenarios...');
        $this->command->info('═══════════════════════════════════════════════════');
        
        try {
            // Phase 1: Data Boundary Testing
            $this->command->info('🔢 Phase 1: Data Boundary & Limit Testing');
            $this->seedDataBoundaryTests();
            
            // Phase 2: Concurrent Operation Testing
            $this->command->info('🔄 Phase 2: Concurrent Operation Testing');
            $this->seedConcurrentOperationTests();
            
            // Phase 3: System State Edge Cases
            $this->command->info('🔧 Phase 3: System State Edge Cases');
            $this->seedSystemStateEdgeCases();
            
            // Phase 4: Business Logic Edge Cases
            $this->command->info('💼 Phase 4: Business Logic Edge Cases');
            $this->seedBusinessLogicEdgeCases();
            
            // Phase 5: Data Integrity Edge Cases
            $this->command->info('🔐 Phase 5: Data Integrity Edge Cases');
            $this->seedDataIntegrityEdgeCases();
            
            // Phase 6: Performance Edge Cases
            $this->command->info('⚡ Phase 6: Performance & Scalability Edge Cases');
            $this->seedPerformanceEdgeCases();
            
            // Phase 7: Time-Based Edge Cases
            $this->command->info('⏰ Phase 7: Time-Based Edge Cases');
            $this->seedTimeBasedEdgeCases();
            
            // Phase 8: User Behavior Edge Cases
            $this->command->info('👤 Phase 8: User Behavior Edge Cases');
            $this->seedUserBehaviorEdgeCases();
            
            // Phase 9: Financial Edge Cases
            $this->command->info('💰 Phase 9: Financial Transaction Edge Cases');
            $this->seedFinancialEdgeCases();
            
            // Phase 10: Integration Edge Cases
            $this->command->info('🔗 Phase 10: Integration & API Edge Cases');
            $this->seedIntegrationEdgeCases();
            
            $this->displayEdgeCaseSummary();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Edge case seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedDataBoundaryTests(): void
    {
        // 1. Maximum Length Text Fields
        $org = Organization::create([
            'name' => str_repeat('A', 255), // Maximum name length
            'email' => 'max.length@' . str_repeat('a', 240) . '.com',
            'phone' => '+94' . str_repeat('7', 9),
            'address' => str_repeat('Very long address line ', 20), // Test long address
            'city' => str_repeat('City', 63), // Max city length
            'country' => 'Sri Lanka',
            'website' => 'https://' . str_repeat('long', 60) . '.com',
            'description' => str_repeat('Long description text ', 100),
            'is_active' => true,
        ]);
        $this->edgeCaseData['max_length_texts'] = 1;
        
        // 2. Minimum/Edge Value Numbers
        $branch = Branch::create([
            'name' => 'Edge Case Branch',
            'organization_id' => $org->id,
            'email' => 'edge@test.com',
            'phone' => '+94100000000', // Minimum valid phone
            'address' => 'Test Address',
            'city' => 'Test City',
            'total_capacity' => 1, // Minimum capacity
            'reservation_fee' => 0.01, // Minimum fee
            'cancellation_fee' => 0.01,
            'opening_time' => '00:00:00', // Edge time
            'closing_time' => '23:59:59', // Edge time
            'is_active' => true,
        ]);
        $this->edgeCaseData['min_value_numbers'] = 1;
        
        // 3. Zero and Null Value Testing
        $zeroValueItem = ItemMaster::create([
            'name' => 'Zero Value Item',
            'sku' => 'ZERO001',
            'cost_price' => 0.00, // Zero cost
            'selling_price' => 0.01, // Minimum price
            'stock_quantity' => 0, // Zero stock
            'min_stock_level' => 0, // Zero minimum
            'max_stock_level' => 1, // Minimum max
            'organization_id' => $org->id,
            'branch_id' => $branch->id,
            'is_active' => true,
            'description' => null, // Null description
        ]);
        $this->edgeCaseData['zero_null_values'] = 1;
        
        // 4. Unicode and Special Characters
        $unicodeOrg = Organization::create([
            'name' => '测试餐厅 🍽️ رستوران تست',
            'email' => 'unicode@тест.com',
            'phone' => '+94123456789',
            'address' => '123 Unicode Street™ ® ©',
            'city' => 'Côté d\'Ivoire',
            'country' => 'Sri Lanka',
            'description' => 'Testing with émojis 😀🍕🍰 and special chars: @#$%^&*()',
            'is_active' => true,
        ]);
        $this->edgeCaseData['unicode_special_chars'] = 1;
    }

    private function seedConcurrentOperationTests(): void
    {
        // 1. Simultaneous Order Creation
        $branch = Branch::first();
        $orderTime = now();
        
        for ($i = 1; $i <= 10; $i++) {
            Order::create([
                'customer_name' => "Concurrent Customer {$i}",
                'customer_phone' => '+9477' . str_pad(1000000 + $i, 7, '0', STR_PAD_LEFT),
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_type' => 'dine_in',
                'status' => 'submitted',
                'order_time' => $orderTime, // Exact same time
                'subtotal' => 100.00,
                'tax' => 10.00,
                'total' => 110.00,
                'special_instructions' => json_encode(['test' => 'concurrent_order']),
            ]);
        }
        $this->edgeCaseData['concurrent_orders'] = 10;
        
        // 2. Rapid-Fire Reservations
        $reservationTime = now()->addDays(1);
        
        for ($i = 1; $i <= 8; $i++) {
            Reservation::create([
                'name' => "Rapid Reservation {$i}",
                'phone' => '+9477' . str_pad(2000000 + $i, 7, '0', STR_PAD_LEFT),
                'email' => "rapid{$i}@test.com",
                'date' => $reservationTime->toDateString(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'branch_id' => $branch->id,
                'reservation_fee' => 100.00,
                'created_at' => now(), // All created at same time
                'updated_at' => now(),
            ]);
        }
        $this->edgeCaseData['rapid_reservations'] = 8;
        
        // 3. Simultaneous User Registration
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Concurrent User {$i}",
                'email' => "concurrent{$i}@test.com",
                'phone' => '+9477' . str_pad(3000000 + $i, 7, '0', STR_PAD_LEFT),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(), // Same timestamp
                'updated_at' => now(),
            ]);
        }
        $this->edgeCaseData['concurrent_users'] = 5;
    }

    private function seedSystemStateEdgeCases(): void
    {
        // 1. Inactive but Referenced Entities
        $inactiveOrg = Organization::create([
            'name' => 'Inactive Organization',
            'email' => 'inactive@test.com',
            'phone' => '+94123456789',
            'address' => 'Inactive Address',
            'city' => 'Inactive City',
            'country' => 'Sri Lanka',
            'is_active' => false, // Inactive
        ]);
        
        // Create branch under inactive org
        $branchUnderInactive = Branch::create([
            'name' => 'Branch Under Inactive Org',
            'organization_id' => $inactiveOrg->id,
            'email' => 'branch.inactive@test.com',
            'phone' => '+94987654321',
            'address' => 'Branch Address',
            'city' => 'Branch City',
            'total_capacity' => 50,
            'is_active' => true, // Active branch under inactive org
        ]);
        $this->edgeCaseData['inactive_referenced'] = 1;
        
        // 2. Expired Subscription with Active Usage
        $expiredPlan = SubscriptionPlan::create([
            'name' => 'Expired Plan',
            'description' => 'Plan that will be expired',
            'price' => 1000.00,
            'duration_in_days' => 30,
            'max_branches' => 5,
            'max_users' => 50,
            'is_active' => false, // Inactive plan
        ]);
        
        $expiredSub = Subscription::create([
            'organization_id' => $inactiveOrg->id,
            'subscription_plan_id' => $expiredPlan->id,
            'start_date' => Carbon::now()->subDays(60),
            'end_date' => Carbon::now()->subDays(30), // Expired
            'status' => 'expired',
            'is_active' => false,
        ]);
        $this->edgeCaseData['expired_subscriptions'] = 1;
        
        // 3. Circular Reference Testing
        $org1 = Organization::create([
            'name' => 'Circular Ref Org 1',
            'email' => 'circular1@test.com',
            'phone' => '+94111111111',
            'address' => 'Address 1',
            'city' => 'City 1',
            'country' => 'Sri Lanka',
            'is_active' => true,
        ]);
        
        $org2 = Organization::create([
            'name' => 'Circular Ref Org 2',
            'email' => 'circular2@test.com',
            'phone' => '+94222222222',
            'address' => 'Address 2',
            'city' => 'City 2',
            'country' => 'Sri Lanka',
            'parent_organization_id' => $org1->id, // Custom field for testing
            'is_active' => true,
        ]);
        
        // Attempt to create circular reference (should be prevented by business logic)
        try {
            $org1->update(['parent_organization_id' => $org2->id]);
        } catch (\Exception $e) {
            // Expected to fail - this tests circular reference prevention
        }
        $this->edgeCaseData['circular_references'] = 2;
    }

    private function seedBusinessLogicEdgeCases(): void
    {
        $branch = Branch::first();
        
        // 1. Order with No Items
        $emptyOrder = Order::create([
            'customer_name' => 'Empty Order Customer',
            'customer_phone' => '+94777000001',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 0.00,
            'tax' => 0.00,
            'total' => 0.00,
            'special_instructions' => json_encode(['note' => 'Empty order for testing']),
        ]);
        $this->edgeCaseData['empty_orders'] = 1;
        
        // 2. Reservation for Past Date
        $pastReservation = Reservation::create([
            'name' => 'Past Date Reservation',
            'phone' => '+94777000002',
            'email' => 'past@test.com',
            'date' => Carbon::yesterday(), // Past date
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 4,
            'status' => 'pending', // Pending status for past date
            'branch_id' => $branch->id,
            'comments' => 'Testing past date reservation logic',
        ]);
        $this->edgeCaseData['past_reservations'] = 1;
        
        // 3. Over-Capacity Reservation
        $overCapacityReservation = Reservation::create([
            'name' => 'Over Capacity Event',
            'phone' => '+94777000003',
            'email' => 'overcapacity@test.com',
            'date' => Carbon::tomorrow(),
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 999999, // Unrealistic number
            'status' => 'cancelled',
            'branch_id' => $branch->id,
            'comments' => 'Testing over-capacity handling',
        ]);
        $this->edgeCaseData['over_capacity'] = 1;
        
        // 4. Self-Referencing User
        $selfRefUser = User::create([
            'name' => 'Self Reference User',
            'email' => 'selfref@test.com',
            'phone' => '+94777000004',
            'password' => bcrypt('password'),
            'referred_by_user_id' => null, // Will set to self after creation
        ]);
        $selfRefUser->update(['referred_by_user_id' => $selfRefUser->id]);
        $this->edgeCaseData['self_references'] = 1;
        
        // 5. Negative Quantity Order Item
        $negativeOrder = Order::create([
            'customer_name' => 'Negative Quantity Customer',
            'customer_phone' => '+94777000005',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'takeaway',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => -50.00, // Negative amount
            'tax' => -5.00,
            'total' => -55.00,
            'special_instructions' => json_encode(['note' => 'Testing negative values']),
        ]);
        $this->edgeCaseData['negative_values'] = 1;
    }

    private function seedDataIntegrityEdgeCases(): void
    {
        // 1. Orphaned Records
        $orphanedItem = ItemMaster::create([
            'name' => 'Orphaned Item',
            'sku' => 'ORPHAN001',
            'cost_price' => 10.00,
            'selling_price' => 15.00,
            'stock_quantity' => 100,
            'organization_id' => 99999, // Non-existent organization
            'branch_id' => 99999, // Non-existent branch
            'is_active' => true,
        ]);
        $this->edgeCaseData['orphaned_records'] = 1;
        
        // 2. Duplicate Key Testing
        $branch = Branch::first();
        
        // Create multiple users with same phone (if allowed)
        for ($i = 1; $i <= 3; $i++) {
            try {
                User::create([
                    'name' => "Duplicate Phone User {$i}",
                    'email' => "duplicate.phone.{$i}@test.com",
                    'phone' => '+94777999999', // Same phone number
                    'password' => bcrypt('password'),
                ]);
                $this->edgeCaseData['duplicate_phones'] = ($this->edgeCaseData['duplicate_phones'] ?? 0) + 1;
            } catch (\Exception $e) {
                // Expected to fail if unique constraint exists
            }
        }
        
        // 3. Cross-Organization Data Access
        $org1 = Organization::first();
        $org2 = Organization::skip(1)->first() ?? Organization::create([
            'name' => 'Cross Org Test',
            'email' => 'crossorg@test.com',
            'phone' => '+94999999999',
            'address' => 'Cross Address',
            'city' => 'Cross City',
            'country' => 'Sri Lanka',
            'is_active' => true,
        ]);
        
        // Try to create order in one org's branch but reference another org
        try {
            Order::create([
                'customer_name' => 'Cross Org Customer',
                'customer_phone' => '+94777000006',
                'branch_id' => $branch->id, // Org1 branch
                'organization_id' => $org2->id, // But Org2 reference
                'order_type' => 'dine_in',
                'status' => 'submitted',
                'order_time' => now(),
                'subtotal' => 100.00,
                'tax' => 10.00,
                'total' => 110.00,
            ]);
            $this->edgeCaseData['cross_org_data'] = 1;
        } catch (\Exception $e) {
            // Expected to fail due to data integrity rules
        }
        
        // 4. Foreign Key Constraint Testing
        try {
            Reservation::create([
                'name' => 'Invalid Branch Reservation',
                'phone' => '+94777000007',
                'email' => 'invalid@test.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 4,
                'status' => 'confirmed',
                'branch_id' => 99999, // Non-existent branch
            ]);
        } catch (\Exception $e) {
            $this->edgeCaseData['fk_violations'] = 1;
        }
    }

    private function seedPerformanceEdgeCases(): void
    {
        // 1. Bulk Data Operations
        $branch = Branch::first();
        $bulkOrders = [];
        
        for ($i = 1; $i <= 100; $i++) {
            $bulkOrders[] = [
                'customer_name' => "Bulk Customer {$i}",
                'customer_phone' => '+9477' . str_pad(4000000 + $i, 7, '0', STR_PAD_LEFT),
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_type' => 'takeaway',
                'status' => 'submitted',
                'order_time' => now(),
                'subtotal' => rand(50, 500),
                'tax' => 0,
                'total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Update totals
        foreach ($bulkOrders as &$order) {
            $order['tax'] = $order['subtotal'] * 0.10;
            $order['total'] = $order['subtotal'] + $order['tax'];
        }
        
        Order::insert($bulkOrders);
        $this->edgeCaseData['bulk_operations'] = 100;
        
        // 2. Deep Nested Queries Simulation
        $complexItem = ItemMaster::create([
            'name' => 'Complex Query Item',
            'sku' => 'COMPLEX001',
            'cost_price' => 25.00,
            'selling_price' => 40.00,
            'stock_quantity' => 1000,
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'is_active' => true,
            'category' => 'complex_testing',
            'tags' => json_encode(['performance', 'testing', 'complex', 'nested']),
        ]);
        $this->edgeCaseData['complex_items'] = 1;
        
        // 3. Large Text Field Storage
        $largeTextOrder = Order::create([
            'customer_name' => 'Large Text Customer',
            'customer_phone' => '+94777000008',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 150.00,
            'tax' => 15.00,
            'total' => 165.00,
            'special_instructions' => json_encode([
                'very_long_note' => str_repeat('This is a very long instruction that tests the system\'s ability to handle large amounts of text data in JSON fields. ', 100)
            ]),
        ]);
        $this->edgeCaseData['large_text_fields'] = 1;
    }

    private function seedTimeBasedEdgeCases(): void
    {
        $branch = Branch::first();
        
        // 1. Leap Year Date Testing
        $leapYearDate = Carbon::create(2024, 2, 29); // Leap year Feb 29
        
        $leapYearReservation = Reservation::create([
            'name' => 'Leap Year Customer',
            'phone' => '+94777000009',
            'email' => 'leap@test.com',
            'date' => $leapYearDate,
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 2,
            'status' => 'confirmed',
            'branch_id' => $branch->id,
        ]);
        $this->edgeCaseData['leap_year_dates'] = 1;
        
        // 2. Year-End Rollover Testing
        $yearEndReservation = Reservation::create([
            'name' => 'Year End Customer',
            'phone' => '+94777000010',
            'email' => 'yearend@test.com',
            'date' => Carbon::create(date('Y'), 12, 31), // Dec 31
            'start_time' => '23:30:00',
            'end_time' => '01:30:00', // Crosses midnight
            'number_of_people' => 4,
            'status' => 'confirmed',
            'branch_id' => $branch->id,
        ]);
        $this->edgeCaseData['year_end_dates'] = 1;
        
        // 3. Time Zone Edge Cases
        $timezoneOrder = Order::create([
            'customer_name' => 'Timezone Customer',
            'customer_phone' => '+94777000011',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'delivery',
            'status' => 'submitted',
            'order_time' => Carbon::now()->setTimezone('UTC'),
            'subtotal' => 200.00,
            'tax' => 20.00,
            'total' => 220.00,
            'special_instructions' => json_encode(['timezone' => 'UTC_test']),
        ]);
        $this->edgeCaseData['timezone_tests'] = 1;
        
        // 4. Historical Date Testing
        $historicalReservation = Reservation::create([
            'name' => 'Historical Customer',
            'phone' => '+94777000012',
            'email' => 'historical@test.com',
            'date' => Carbon::create(1990, 1, 1), // Very old date
            'start_time' => '12:00:00',
            'end_time' => '14:00:00',
            'number_of_people' => 2,
            'status' => 'completed',
            'branch_id' => $branch->id,
            'created_at' => Carbon::create(1990, 1, 1),
        ]);
        $this->edgeCaseData['historical_dates'] = 1;
        
        // 5. Future Date Extremes
        $futureReservation = Reservation::create([
            'name' => 'Future Customer',
            'phone' => '+94777000013',
            'email' => 'future@test.com',
            'date' => Carbon::create(2050, 12, 25), // Far future
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 2,
            'status' => 'confirmed',
            'branch_id' => $branch->id,
        ]);
        $this->edgeCaseData['future_dates'] = 1;
    }

    private function seedUserBehaviorEdgeCases(): void
    {
        // 1. Rapid Action User
        $rapidUser = User::create([
            'name' => 'Rapid Action User',
            'email' => 'rapid@test.com',
            'phone' => '+94777000014',
            'password' => bcrypt('password'),
        ]);
        
        // Simulate rapid order creation
        $branch = Branch::first();
        for ($i = 1; $i <= 5; $i++) {
            Order::create([
                'customer_name' => $rapidUser->name,
                'customer_phone' => $rapidUser->phone,
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'order_type' => 'takeaway',
                'status' => 'submitted',
                'order_time' => now()->addSeconds($i), // Very close timestamps
                'subtotal' => 50.00,
                'tax' => 5.00,
                'total' => 55.00,
            ]);
        }
        $this->edgeCaseData['rapid_actions'] = 5;
        
        // 2. Malformed Input Simulation
        $malformedUser = User::create([
            'name' => '<script>alert("test")</script>', // Script injection attempt
            'email' => 'malformed@test.com',
            'phone' => '+94777000015',
            'password' => bcrypt('password'),
        ]);
        $this->edgeCaseData['malformed_inputs'] = 1;
        
        // 3. Excessive Data User
        $excessiveUser = User::create([
            'name' => 'Excessive Data User',
            'email' => 'excessive@test.com',
            'phone' => '+94777000016',
            'password' => bcrypt('password'),
        ]);
        
        // Create many reservations for same user
        for ($i = 1; $i <= 20; $i++) {
            Reservation::create([
                'name' => $excessiveUser->name,
                'phone' => $excessiveUser->phone,
                'email' => $excessiveUser->email,
                'date' => Carbon::today()->addDays($i),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'branch_id' => $branch->id,
            ]);
        }
        $this->edgeCaseData['excessive_data'] = 20;
    }

    private function seedFinancialEdgeCases(): void
    {
        $branch = Branch::first();
        
        // 1. Precision Money Values
        $precisionOrder = Order::create([
            'customer_name' => 'Precision Money Customer',
            'customer_phone' => '+94777000017',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'order_time' => now(),
            'subtotal' => 123.456789, // High precision
            'tax' => 12.3456789,
            'total' => 135.8024679,
        ]);
        
        // Create payment with precision values
        Payment::create([
            'payable_type' => Order::class,
            'payable_id' => $precisionOrder->id,
            'amount' => 135.8024679,
            'payment_method' => 'card',
            'status' => 'completed',
            'payment_reference' => 'PRECISION-' . time(),
        ]);
        $this->edgeCaseData['precision_money'] = 1;
        
        // 2. Extremely Large Transaction
        $largeOrder = Order::create([
            'customer_name' => 'Large Transaction Customer',
            'customer_phone' => '+94777000018',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'order_time' => now(),
            'subtotal' => 999999.99, // Very large amount
            'tax' => 99999.999,
            'total' => 1099999.989,
        ]);
        $this->edgeCaseData['large_transactions'] = 1;
        
        // 3. Multi-Currency Edge Case
        $currencyOrder = Order::create([
            'customer_name' => 'Multi Currency Customer',
            'customer_phone' => '+94777000019',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'completed',
            'order_time' => now(),
            'subtotal' => 100.00,
            'tax' => 10.00,
            'total' => 110.00,
            'special_instructions' => json_encode([
                'currency' => 'USD',
                'exchange_rate' => 320.50,
                'original_amount' => '0.34 USD'
            ]),
        ]);
        $this->edgeCaseData['multi_currency'] = 1;
        
        // 4. Refund Chain Testing
        $refundOrder = Order::create([
            'customer_name' => 'Refund Chain Customer',
            'customer_phone' => '+94777000020',
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => 'dine_in',
            'status' => 'cancelled',
            'order_time' => now(),
            'subtotal' => 250.00,
            'tax' => 25.00,
            'total' => 275.00,
        ]);
        
        // Create original payment
        $originalPayment = Payment::create([
            'payable_type' => Order::class,
            'payable_id' => $refundOrder->id,
            'amount' => 275.00,
            'payment_method' => 'card',
            'status' => 'completed',
            'payment_reference' => 'ORIGINAL-' . time(),
        ]);
        
        // Create refund payment
        Payment::create([
            'payable_type' => Order::class,
            'payable_id' => $refundOrder->id,
            'amount' => -275.00, // Negative for refund
            'payment_method' => 'card',
            'status' => 'completed',
            'payment_reference' => 'REFUND-' . time(),
            'notes' => 'Refund for payment: ' . $originalPayment->payment_reference,
        ]);
        $this->edgeCaseData['refund_chains'] = 1;
    }

    private function seedIntegrationEdgeCases(): void
    {
        // 1. API Response Simulation
        $apiOrder = Order::create([
            'customer_name' => 'API Integration Customer',
            'customer_phone' => '+94777000021',
            'branch_id' => Branch::first()->id,
            'organization_id' => Branch::first()->organization_id,
            'order_type' => 'delivery',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 180.00,
            'tax' => 18.00,
            'total' => 198.00,
            'special_instructions' => json_encode([
                'api_source' => 'external_app',
                'api_version' => '2.1.3',
                'integration_id' => 'INT-' . Str::uuid(),
                'webhook_url' => 'https://example.com/webhook',
                'callback_data' => ['customer_id' => 12345, 'session_id' => 'sess_abc123']
            ]),
        ]);
        $this->edgeCaseData['api_integrations'] = 1;
        
        // 2. External System ID Conflicts
        $conflictOrder1 = Order::create([
            'customer_name' => 'External ID Conflict 1',
            'customer_phone' => '+94777000022',
            'branch_id' => Branch::first()->id,
            'organization_id' => Branch::first()->organization_id,
            'order_type' => 'takeaway',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 75.00,
            'tax' => 7.50,
            'total' => 82.50,
            'special_instructions' => json_encode(['external_id' => 'EXT-12345']),
        ]);
        
        $conflictOrder2 = Order::create([
            'customer_name' => 'External ID Conflict 2',
            'customer_phone' => '+94777000023',
            'branch_id' => Branch::first()->id,
            'organization_id' => Branch::first()->organization_id,
            'order_type' => 'takeaway',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 85.00,
            'tax' => 8.50,
            'total' => 93.50,
            'special_instructions' => json_encode(['external_id' => 'EXT-12345']), // Same external ID
        ]);
        $this->edgeCaseData['id_conflicts'] = 2;
        
        // 3. Incomplete Integration Data
        $incompleteOrder = Order::create([
            'customer_name' => 'Incomplete Integration Customer',
            'customer_phone' => '+94777000024',
            'branch_id' => Branch::first()->id,
            'organization_id' => Branch::first()->organization_id,
            'order_type' => 'delivery',
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 120.00,
            'tax' => 12.00,
            'total' => 132.00,
            'special_instructions' => json_encode([
                'integration_status' => 'partial',
                'missing_fields' => ['delivery_address', 'payment_method'],
                'retry_count' => 3,
                'last_sync_attempt' => now()->toISOString()
            ]),
        ]);
        $this->edgeCaseData['incomplete_integrations'] = 1;
    }

    private function displayEdgeCaseSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 EXHAUSTIVE EDGE CASE SEEDING SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════');
        
        $totalEdgeCases = array_sum($this->edgeCaseData);
        $this->command->info("⚡ Total Edge Cases Created: {$totalEdgeCases}");
        
        $this->command->newLine();
        $this->command->info('🎯 EDGE CASE BREAKDOWN:');
        
        foreach ($this->edgeCaseData as $category => $count) {
            $categoryName = ucwords(str_replace('_', ' ', $category));
            $this->command->info(sprintf('  %-30s: %d cases', $categoryName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('🔍 EDGE CASE CATEGORIES:');
        $this->command->info('  📐 Data Boundaries: Max lengths, min values, unicode, special chars');
        $this->command->info('  🔄 Concurrency: Simultaneous operations, race conditions');
        $this->command->info('  🔧 System States: Inactive entities, expired subscriptions, circular refs');
        $this->command->info('  💼 Business Logic: Empty orders, past dates, over-capacity, negatives');
        $this->command->info('  🔐 Data Integrity: Orphaned records, duplicates, FK violations');
        $this->command->info('  ⚡ Performance: Bulk operations, complex queries, large text');
        $this->command->info('  ⏰ Time-Based: Leap years, timezones, historical/future dates');
        $this->command->info('  👤 User Behavior: Rapid actions, malformed input, excessive data');
        $this->command->info('  💰 Financial: Precision values, large amounts, multi-currency');
        $this->command->info('  🔗 Integration: API responses, ID conflicts, incomplete data');
        
        $this->command->newLine();
        $this->command->info('✅ All edge case scenarios have been comprehensively seeded!');
        $this->command->info('🔍 These scenarios test system robustness, data integrity,');
        $this->command->info('    performance limits, and business rule enforcement.');
    }
}
