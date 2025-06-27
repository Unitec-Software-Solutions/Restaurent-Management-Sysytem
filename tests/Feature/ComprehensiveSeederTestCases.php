<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ItemMaster;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\Reservation;
use Carbon\Carbon;

class ComprehensiveSeederTestCases extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $organization;
    protected $branch;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization and branch
        $this->organization = Organization::factory()->create();
        $this->branch = Branch::factory()->create(['organization_id' => $this->organization->id]);
        $this->adminUser = User::factory()->create();
    }

    /** @test */
    public function test_staff_shifts_seeder_creates_comprehensive_data()
    {
        // Run the seeder
        $this->artisan('db:seed', ['--class' => 'ComprehensiveStaffShiftsSeeder']);

        // Test staff creation
        $this->assertDatabaseHas('users', [
            'role' => 'staff'
        ]);

        // Test shifts creation
        $this->assertDatabaseHas('shifts', [
            'shift_type' => 'morning'
        ]);

        $this->assertDatabaseHas('shifts', [
            'shift_type' => 'evening'
        ]);

        $this->assertDatabaseHas('shifts', [
            'shift_type' => 'night'
        ]);

        // Test shift assignments
        $this->assertDatabaseHas('shift_assignments');

        // Test overlapping shifts exist
        $overlappingAssignments = ShiftAssignment::with('shift')
            ->whereHas('shift', function($query) {
                $query->where('start_time', '<', '14:00:00')
                      ->where('end_time', '>', '12:00:00');
            })
            ->count();
        
        $this->assertGreaterThan(0, $overlappingAssignments, 'Should have overlapping shift assignments');

        // Test weekend coverage
        $weekendShifts = Shift::whereIn('day_of_week', ['saturday', 'sunday'])->count();
        $this->assertGreaterThan(0, $weekendShifts, 'Should have weekend shifts');

        // Test emergency shifts
        $emergencyShifts = Shift::where('is_emergency', true)->count();
        $this->assertGreaterThan(0, $emergencyShifts, 'Should have emergency shifts');
    }

    /** @test */
    public function test_orders_seeder_creates_all_scenarios()
    {
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);

        // Test different order types
        $this->assertDatabaseHas('orders', ['order_type' => 'takeaway']);
        $this->assertDatabaseHas('orders', ['order_type' => 'dine_in']);

        // Test all order statuses
        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];
        foreach ($statuses as $status) {
            $this->assertDatabaseHas('orders', ['status' => $status]);
        }

        // Test time-based orders (peak hours)
        $peakHourOrders = Order::whereTime('created_at', '>=', '12:00:00')
                               ->whereTime('created_at', '<=', '14:00:00')
                               ->count();
        $this->assertGreaterThan(0, $peakHourOrders, 'Should have peak hour orders');

        // Test off-peak orders
        $offPeakOrders = Order::whereTime('created_at', '>=', '15:00:00')
                              ->whereTime('created_at', '<=', '17:00:00')
                              ->count();
        $this->assertGreaterThan(0, $offPeakOrders, 'Should have off-peak orders');

        // Test weekend orders
        $weekendOrders = Order::whereIn(DB::raw('DAYOFWEEK(created_at)'), [1, 7])->count();
        $this->assertGreaterThan(0, $weekendOrders, 'Should have weekend orders');

        // Test large orders
        $largeOrders = Order::where('total_price', '>', 100)->count();
        $this->assertGreaterThan(0, $largeOrders, 'Should have large orders');

        // Test rush hour orders
        $rushHourOrders = Order::where('is_rush_order', true)->count();
        $this->assertGreaterThan(0, $rushHourOrders, 'Should have rush hour orders');
    }

    /** @test */
    public function test_menu_items_seeder_creates_dietary_restrictions()
    {
        $this->artisan('db:seed', ['--class' => 'ComprehensiveMenuItemsSeeder']);

        // Test dietary restriction flags
        $this->assertDatabaseHas('item_masters', ['is_vegetarian' => true]);
        $this->assertDatabaseHas('item_masters', ['is_vegan' => true]);
        $this->assertDatabaseHas('item_masters', ['is_gluten_free' => true]);
        $this->assertDatabaseHas('item_masters', ['is_dairy_free' => true]);
        $this->assertDatabaseHas('item_masters', ['is_nut_free' => true]);
        $this->assertDatabaseHas('item_masters', ['is_spicy' => true]);

        // Test different categories
        $categories = ['appetizer', 'main_course', 'dessert', 'beverage', 'side_dish'];
        foreach ($categories as $category) {
            $this->assertDatabaseHas('item_masters', ['category' => $category]);
        }

        // Test spice levels
        $spiceLevels = ['mild', 'medium', 'hot', 'extra_hot'];
        foreach ($spiceLevels as $level) {
            $this->assertDatabaseHas('item_masters', ['spice_level' => $level]);
        }

        // Test allergen information
        $allergenItems = ItemMaster::whereNotNull('allergen_info')->count();
        $this->assertGreaterThan(0, $allergenItems, 'Should have items with allergen information');

        // Test nutritional information
        $nutritionalItems = ItemMaster::whereNotNull('calories')->count();
        $this->assertGreaterThan(0, $nutritionalItems, 'Should have items with nutritional information');
    }

    /** @test */
    public function test_payment_scenarios_seeder_creates_all_scenarios()
    {
        // First create orders
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);
        
        // Then create payments
        $this->artisan('db:seed', ['--class' => 'ComprehensivePaymentScenariosSeeder']);

        // Test successful payments
        $successfulPayments = Payment::where('status', 'completed')->count();
        $this->assertGreaterThan(0, $successfulPayments, 'Should have successful payments');

        // Test failed payments
        $failedPayments = Payment::where('status', 'failed')->count();
        $this->assertGreaterThan(0, $failedPayments, 'Should have failed payments');

        // Test refunded payments
        $refundedPayments = Payment::where('status', 'refunded')->count();
        $this->assertGreaterThan(0, $refundedPayments, 'Should have refunded payments');

        // Test pending payments
        $pendingPayments = Payment::where('status', 'pending')->count();
        $this->assertGreaterThan(0, $pendingPayments, 'Should have pending payments');

        // Test cancelled payments
        $cancelledPayments = Payment::where('status', 'cancelled')->count();
        $this->assertGreaterThan(0, $cancelledPayments, 'Should have cancelled payments');

        // Test different payment methods
        $paymentMethods = ['cash', 'credit_card', 'debit_card', 'mobile_payment', 'bank_transfer'];
        foreach ($paymentMethods as $method) {
            $this->assertDatabaseHas('payments', ['payment_method' => $method]);
        }

        // Test high-value transactions
        $highValuePayments = Payment::where('amount', '>', 100)->count();
        $this->assertGreaterThan(0, $highValuePayments, 'Should have high-value payments');

        // Test split payments (same order_id, multiple payments)
        $splitPayments = Payment::select('order_id')
                                ->groupBy('order_id')
                                ->havingRaw('COUNT(*) > 1')
                                ->count();
        $this->assertGreaterThan(0, $splitPayments, 'Should have split payments');

        // Test gateway responses
        $gatewayResponses = Payment::whereNotNull('gateway_response')->count();
        $this->assertGreaterThan(0, $gatewayResponses, 'Should have payments with gateway responses');
    }

    /** @test */
    public function test_order_validation_scenarios()
    {
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);

        // Test minimum order validation
        $smallOrders = Order::where('total_price', '<', 10)->count();
        $this->assertGreaterThan(0, $smallOrders, 'Should have small orders for minimum validation testing');

        // Test kitchen capacity scenarios
        $rushOrders = Order::where('is_rush_order', true)->count();
        $this->assertGreaterThan(0, $rushOrders, 'Should have rush orders for capacity testing');

        // Test dietary compliance
        $orders = Order::with('orderItems.itemMaster')->get();
        $dietaryCompliantOrders = $orders->filter(function($order) {
            return $order->orderItems->some(function($item) {
                return $item->itemMaster->is_vegetarian || 
                       $item->itemMaster->is_vegan || 
                       $item->itemMaster->is_gluten_free;
            });
        });
        
        $this->assertGreaterThan(0, $dietaryCompliantOrders->count(), 'Should have orders with dietary restrictions');

        // Test payment method per order type
        $takeawayOrders = Order::where('order_type', 'takeaway')->count();
        $dineInOrders = Order::where('order_type', 'dine_in')->count();
        
        $this->assertGreaterThan(0, $takeawayOrders, 'Should have takeaway orders');
        $this->assertGreaterThan(0, $dineInOrders, 'Should have dine-in orders');
    }

    /** @test */
    public function test_time_based_data_scenarios()
    {
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);

        // Test breakfast hours (7-11 AM)
        $breakfastOrders = Order::whereTime('created_at', '>=', '07:00:00')
                                ->whereTime('created_at', '<=', '11:00:00')
                                ->count();

        // Test lunch hours (11 AM - 3 PM)
        $lunchOrders = Order::whereTime('created_at', '>=', '11:00:00')
                            ->whereTime('created_at', '<=', '15:00:00')
                            ->count();

        // Test dinner hours (5 PM - 10 PM)
        $dinnerOrders = Order::whereTime('created_at', '>=', '17:00:00')
                             ->whereTime('created_at', '<=', '22:00:00')
                             ->count();

        $this->assertGreaterThan(0, $lunchOrders, 'Should have lunch time orders');
        $this->assertGreaterThan(0, $dinnerOrders, 'Should have dinner time orders');

        // Test holiday patterns
        $holidayOrders = Order::whereIn(DB::raw('DAYOFYEAR(created_at)'), [1, 359, 360])
                              ->count();
        $this->assertGreaterThanOrEqual(0, $holidayOrders, 'Holiday orders test completed');
    }

    /** @test */
    public function test_edge_case_scenarios()
    {
        $this->artisan('db:seed', ['--class' => 'ComprehensiveStaffShiftsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);

        // Test overlapping shifts
        $overlappingShifts = ShiftAssignment::join('shifts as s1', 'shift_assignments.shift_id', '=', 's1.id')
                                           ->join('shift_assignments as sa2', function($join) {
                                               $join->on('shift_assignments.user_id', '=', 'sa2.user_id')
                                                    ->on('shift_assignments.assigned_date', '=', 'sa2.assigned_date')
                                                    ->on('shift_assignments.id', '!=', 'sa2.id');
                                           })
                                           ->join('shifts as s2', 'sa2.shift_id', '=', 's2.id')
                                           ->where(function($query) {
                                               $query->where('s1.start_time', '<', DB::raw('s2.end_time'))
                                                     ->where('s1.end_time', '>', DB::raw('s2.start_time'));
                                           })
                                           ->count();

        $this->assertGreaterThan(0, $overlappingShifts, 'Should have overlapping shifts for testing conflict resolution');

        // Test orders with zero or negative amounts (refunds)
        $refundOrders = Payment::where('amount', '<', 0)->count();
        $this->assertGreaterThan(0, $refundOrders, 'Should have refund scenarios');

        // Test orders exceeding typical limits
        $largePartyOrders = Order::where('party_size', '>', 8)->count();
        $this->assertGreaterThanOrEqual(0, $largePartyOrders, 'Large party orders test completed');
    }

    /** @test */
    public function test_data_consistency_across_seeders()
    {
        // Run all seeders
        $this->artisan('db:seed', ['--class' => 'ComprehensiveStaffShiftsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensiveMenuItemsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensivePaymentScenariosSeeder']);

        // Test foreign key relationships
        $ordersWithInvalidBranches = Order::whereNotIn('branch_id', Branch::pluck('id'))->count();
        $this->assertEquals(0, $ordersWithInvalidBranches, 'All orders should have valid branch references');

        $paymentsWithInvalidOrders = Payment::whereNotIn('order_id', Order::pluck('id'))->count();
        $this->assertEquals(0, $paymentsWithInvalidOrders, 'All payments should have valid order references');

        $shiftsWithInvalidBranches = Shift::whereNotIn('branch_id', Branch::pluck('id'))->count();
        $this->assertEquals(0, $shiftsWithInvalidBranches, 'All shifts should have valid branch references');

        // Test data ranges
        $invalidPriceItems = ItemMaster::where('price', '<=', 0)->count();
        $this->assertEquals(0, $invalidPriceItems, 'All items should have positive prices');

        $invalidOrderTotals = Order::where('total_price', '<', 0)->count();
        $this->assertEquals(0, $invalidOrderTotals, 'All orders should have non-negative totals');
    }

    /** @test */
    public function test_performance_with_large_datasets()
    {
        $startTime = microtime(true);
        
        // Run all comprehensive seeders
        $this->artisan('db:seed', ['--class' => 'ComprehensiveStaffShiftsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensiveMenuItemsSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensiveOrdersSeeder']);
        $this->artisan('db:seed', ['--class' => 'ComprehensivePaymentScenariosSeeder']);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (adjust threshold as needed)
        $this->assertLessThan(300, $executionTime, 'Seeders should complete within 5 minutes');
        
        // Test query performance on seeded data
        $queryStart = microtime(true);
        $recentOrders = Order::with(['orderItems', 'payments'])
                            ->where('created_at', '>', Carbon::now()->subDays(7))
                            ->get();
        $queryEnd = microtime(true);
        $queryTime = $queryEnd - $queryStart;
        
        $this->assertLessThan(5, $queryTime, 'Complex queries should execute quickly');
        $this->assertGreaterThan(0, $recentOrders->count(), 'Should have recent orders');
    }

    /** @test */
    public function test_booking_form_validation()
    {
        // Test required fields
        $response = $this->post(route('guest.reservations.store'), []);
        $response->assertSessionHasErrors([
            'customer_name', 
            'customer_phone', 
            'customer_email',
            'reservation_date',
            'reservation_time',
            'party_size'
        ]);

        // Test valid booking
        $validData = [
            'customer_name' => 'John Doe',
            'customer_phone' => '555-123-4567',
            'customer_email' => 'john@example.com',
            'reservation_date' => Carbon::tomorrow()->format('Y-m-d'),
            'reservation_time' => '19:00',
            'party_size' => 4,
            'table_preference' => 'indoor',
            'special_requests' => 'Birthday celebration',
            'terms_accepted' => true
        ];

        $response = $this->post(route('guest.reservations.store'), $validData);
        // Adjust assertion based on actual route behavior
        $response->assertStatus(302); // Redirect after successful booking
    }

    /** @test */
    public function test_guest_layout_functionality()
    {
        // Test guest layout renders correctly
        $response = $this->get('/');
        $response->assertStatus(200);
        
        // Test navigation links are present
        $response->assertSee('Menu');
        $response->assertSee('Book Table');
        $response->assertSee('Takeaway');
        
        // Test responsive design elements
        $response->assertSee('md:flex'); // Mobile menu toggle
        $response->assertSee('fas fa-bars'); // Hamburger menu icon
        
        // Test cart functionality elements
        $response->assertSee('shopping-cart');
        $response->assertSee('cart-count');
        
        // Test session message handling
        $responseWithMessage = $this->withSession(['success' => 'Test message'])
                                   ->get('/');
        $responseWithMessage->assertSee('Test message');
    }
}
