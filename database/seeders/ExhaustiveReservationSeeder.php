<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Reservation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Table;
use App\Models\Employee;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExhaustiveReservationSeeder extends Seeder
{
    use WithoutModelEvents;

    private $branches;
    private $tables;
    private $stewards;
    private $customers;
    private $reservationData = [];

    /**
     * Seed exhaustive reservation scenarios covering all complexities
     */
    public function run(): void
    {
        $this->command->info('🗓️ Seeding Exhaustive Reservation Scenarios...');
        $this->command->info('═══════════════════════════════════════════════');
        
        // Initialize test data
        $this->initializeTestData();
        
        try {
            // Phase 1: Basic Reservation Scenarios
            $this->command->info('📅 Phase 1: Basic Reservation Types');
            $this->seedBasicReservationTypes();
            
            // Phase 2: Time-Based Scenarios
            $this->command->info('⏰ Phase 2: Time-Based Complexities');
            $this->seedTimeBasedScenarios();
            
            // Phase 3: Capacity & Table Scenarios
            $this->command->info('🪑 Phase 3: Capacity & Table Management');
            $this->seedCapacityScenarios();
            
            // Phase 4: Conflict Resolution Scenarios
            $this->command->info('⚡ Phase 4: Conflict & Edge Case Scenarios');
            $this->seedConflictScenarios();
            
            // Phase 5: Payment & Financial Scenarios
            $this->command->info('💰 Phase 5: Payment & Financial Scenarios');
            $this->seedPaymentScenarios();
            
            // Phase 6: Recurring & Pattern Scenarios
            $this->command->info('🔄 Phase 6: Recurring & Pattern Scenarios');
            $this->seedRecurringScenarios();
            
            // Phase 7: Special Event Scenarios
            $this->command->info('🎉 Phase 7: Special Event & Holiday Scenarios');
            $this->seedSpecialEventScenarios();
            
            // Phase 8: Staff Management Scenarios
            $this->command->info('👥 Phase 8: Staff Assignment & Management');
            $this->seedStaffScenarios();
            
            // Phase 9: No-Show & Cancellation Scenarios
            $this->command->info('❌ Phase 9: No-Show & Cancellation Scenarios');
            $this->seedNoShowScenarios();
            
            // Phase 10: Cross-Branch & Multi-Location
            $this->command->info('🏢 Phase 10: Cross-Branch & Multi-Location');
            $this->seedCrossBranchScenarios();
            
            $this->displayReservationSummary();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Reservation seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function initializeTestData(): void
    {
        // Get all branches with their tables
        $this->branches = Branch::with(['tables', 'organization'])->get();
        $this->tables = Table::all();
        $this->stewards = Employee::whereHas('employeeRole', function($query) {
            $query->whereIn('name', ['steward', 'waiter', 'server']);
        })->get();
        
        // Get customers (users) or create some test customers
        $this->customers = User::limit(50)->get();
        if ($this->customers->isEmpty()) {
            $this->createTestCustomers();
        }
    }

    private function createTestCustomers(): void
    {
        $customers = [];
        for ($i = 1; $i <= 50; $i++) {
            $customers[] = [
                'name' => "Test Customer {$i}",
                'email' => "customer{$i}@test.com",
                'phone' => '+94' . str_pad(rand(700000000, 799999999), 9, '0', STR_PAD_LEFT),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        User::insert($customers);
        $this->customers = User::whereIn('email', collect($customers)->pluck('email'))->get();
    }

    private function seedBasicReservationTypes(): void
    {
        foreach ($this->branches as $branch) {
            $this->command->info("  📍 Seeding basic types for {$branch->name}");
            
            // 1. Standard Dinner Reservations
            $this->createReservation($branch, [
                'name' => 'John Smith',
                'phone' => '+94771234567',
                'email' => 'john.smith@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'comments' => 'Anniversary dinner',
                'scenario' => 'standard_dinner'
            ]);
            
            // 2. Business Lunch Reservations
            $this->createReservation($branch, [
                'name' => 'Corporate Events Ltd',
                'phone' => '+94112345678',
                'email' => 'events@corporate.com',
                'date' => Carbon::today()->addDays(3),
                'start_time' => '12:00:00',
                'end_time' => '14:00:00',
                'number_of_people' => 8,
                'status' => 'confirmed',
                'comments' => 'Business lunch meeting',
                'scenario' => 'business_lunch'
            ]);
            
            // 3. Family Gathering
            $this->createReservation($branch, [
                'name' => 'Sarah Fernando',
                'phone' => '+94771111111',
                'email' => 'sarah.fernando@email.com',
                'date' => Carbon::today()->addDays(7),
                'start_time' => '18:30:00',
                'end_time' => '21:30:00',
                'number_of_people' => 12,
                'status' => 'confirmed',
                'comments' => 'Family birthday celebration',
                'scenario' => 'family_gathering'
            ]);
            
            // 4. Quick Bite Reservation
            $this->createReservation($branch, [
                'name' => 'Mike Johnson',
                'phone' => '+94772222222',
                'email' => 'mike.j@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'number_of_people' => 1,
                'status' => 'confirmed',
                'comments' => 'Quick solo lunch',
                'scenario' => 'quick_bite'
            ]);
        }
    }

    private function seedTimeBasedScenarios(): void
    {
        foreach ($this->branches->take(3) as $branch) {
            $this->command->info("  ⏱️ Seeding time scenarios for {$branch->name}");
            
            // 1. Early Bird Reservations
            $this->createReservation($branch, [
                'name' => 'Early Bird Diner',
                'phone' => '+94773333333',
                'email' => 'early@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '17:00:00',
                'end_time' => '18:30:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'scenario' => 'early_bird'
            ]);
            
            // 2. Late Night Reservations
            $this->createReservation($branch, [
                'name' => 'Night Owl Diners',
                'phone' => '+94774444444',
                'email' => 'nightowl@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '21:30:00',
                'end_time' => '23:00:00',
                'number_of_people' => 4,
                'status' => 'confirmed',
                'scenario' => 'late_night'
            ]);
            
            // 3. Extended Duration Reservations
            $this->createReservation($branch, [
                'name' => 'Extended Event',
                'phone' => '+94775555555',
                'email' => 'extended@example.com',
                'date' => Carbon::today()->addDays(5),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'number_of_people' => 15,
                'status' => 'confirmed',
                'comments' => 'Company team building event',
                'scenario' => 'extended_duration'
            ]);
            
            // 4. Same-Day Last-Minute Reservations
            $this->createReservation($branch, [
                'name' => 'Last Minute',
                'phone' => '+94776666666',
                'email' => 'lastminute@example.com',
                'date' => Carbon::today(),
                'start_time' => Carbon::now()->addHours(2)->format('H:i:s'),
                'end_time' => Carbon::now()->addHours(4)->format('H:i:s'),
                'number_of_people' => 2,
                'status' => 'pending',
                'scenario' => 'last_minute'
            ]);
            
            // 5. Peak Time Rush Reservations
            $this->createMultipleReservations($branch, [
                'date' => Carbon::tomorrow(),
                'time_slots' => [
                    ['19:00:00', '21:00:00'],
                    ['19:15:00', '21:15:00'],
                    ['19:30:00', '21:30:00'],
                    ['19:45:00', '21:45:00'],
                ],
                'scenario' => 'peak_time_rush'
            ]);
        }
    }

    private function seedCapacityScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  🪑 Seeding capacity scenarios for {$branch->name}");
            
            // 1. Large Group Reservations
            $this->createReservation($branch, [
                'name' => 'Wedding Reception',
                'phone' => '+94777777777',
                'email' => 'wedding@example.com',
                'date' => Carbon::today()->addDays(30),
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'number_of_people' => 50,
                'status' => 'confirmed',
                'comments' => 'Wedding reception dinner',
                'scenario' => 'large_group'
            ]);
            
            // 2. Over-Capacity Requests (Waitlisted)
            $this->createReservation($branch, [
                'name' => 'Overflow Party',
                'phone' => '+94778888888',
                'email' => 'overflow@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '20:00:00',
                'end_time' => '22:00:00',
                'number_of_people' => 100, // Intentionally over capacity
                'status' => 'waitlisted',
                'comments' => 'Large company party - check capacity',
                'scenario' => 'over_capacity'
            ]);
            
            // 3. Table Splitting Scenarios
            $this->createReservation($branch, [
                'name' => 'Split Table Group',
                'phone' => '+94779999999',
                'email' => 'split@example.com',
                'date' => Carbon::today()->addDays(2),
                'start_time' => '19:00:00',
                'end_time' => '21:30:00',
                'number_of_people' => 18,
                'status' => 'confirmed',
                'comments' => 'Group okay with split tables',
                'scenario' => 'table_splitting'
            ]);
            
            // 4. Minimum Capacity Utilization
            $this->createReservation($branch, [
                'name' => 'Solo Diner Premium',
                'phone' => '+94771010101',
                'email' => 'solo@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '20:00:00',
                'end_time' => '22:00:00',
                'number_of_people' => 1,
                'status' => 'confirmed',
                'comments' => 'Premium table request for single diner',
                'scenario' => 'minimum_capacity'
            ]);
        }
    }

    private function seedConflictScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  ⚡ Seeding conflict scenarios for {$branch->name}");
            
            // 1. Overlapping Time Conflicts
            $baseDate = Carbon::today()->addDays(4);
            $conflictReservations = [
                [
                    'name' => 'First Booking',
                    'phone' => '+94771111100',
                    'start_time' => '19:00:00',
                    'end_time' => '21:00:00',
                    'status' => 'confirmed'
                ],
                [
                    'name' => 'Overlapping Attempt',
                    'phone' => '+94771111101',
                    'start_time' => '20:00:00',
                    'end_time' => '22:00:00',
                    'status' => 'waitlisted' // Due to conflict
                ],
                [
                    'name' => 'Partial Overlap',
                    'phone' => '+94771111102',
                    'start_time' => '20:30:00',
                    'end_time' => '22:30:00',
                    'status' => 'waitlisted'
                ]
            ];
            
            foreach ($conflictReservations as $index => $data) {
                $this->createReservation($branch, [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => strtolower(str_replace(' ', '', $data['name'])) . '@example.com',
                    'date' => $baseDate,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'number_of_people' => 4,
                    'status' => $data['status'],
                    'scenario' => 'time_conflict_' . ($index + 1)
                ]);
            }
            
            // 2. Double Booking Scenarios
            $this->createReservation($branch, [
                'name' => 'Double Booking A',
                'phone' => '+94772222200',
                'email' => 'doublea@example.com',
                'date' => Carbon::today()->addDays(6),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'number_of_people' => 6,
                'status' => 'confirmed',
                'scenario' => 'double_booking_original'
            ]);
            
            $this->createReservation($branch, [
                'name' => 'Double Booking B',
                'phone' => '+94772222201',
                'email' => 'doubleb@example.com',
                'date' => Carbon::today()->addDays(6),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'number_of_people' => 6,
                'status' => 'cancelled', // Cancelled due to double booking
                'comments' => 'Cancelled due to system double booking error',
                'scenario' => 'double_booking_conflict'
            ]);
        }
    }

    private function seedPaymentScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  💰 Seeding payment scenarios for {$branch->name}");
            
            // 1. Paid Reservation with Advance Payment
            $reservation = $this->createReservation($branch, [
                'name' => 'Advance Payment',
                'phone' => '+94773333300',
                'email' => 'advance@example.com',
                'date' => Carbon::today()->addDays(10),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 4,
                'status' => 'confirmed',
                'reservation_fee' => 2000.00,
                'scenario' => 'advance_payment'
            ]);
            
            $this->createPayment($reservation, 2000.00, 'advance_payment', 'completed');
            
            // 2. Deposit Required Reservation
            $reservation = $this->createReservation($branch, [
                'name' => 'Deposit Required',
                'phone' => '+94773333301',
                'email' => 'deposit@example.com',
                'date' => Carbon::today()->addDays(15),
                'start_time' => '20:00:00',
                'end_time' => '23:00:00',
                'number_of_people' => 12,
                'status' => 'confirmed',
                'reservation_fee' => 5000.00,
                'scenario' => 'deposit_required'
            ]);
            
            $this->createPayment($reservation, 5000.00, 'deposit', 'completed');
            
            // 3. Cancelled with Cancellation Fee
            $reservation = $this->createReservation($branch, [
                'name' => 'Cancelled Paid',
                'phone' => '+94773333302',
                'email' => 'cancelled@example.com',
                'date' => Carbon::today()->addDays(5),
                'start_time' => '19:30:00',
                'end_time' => '21:30:00',
                'number_of_people' => 6,
                'status' => 'cancelled',
                'reservation_fee' => 3000.00,
                'cancellation_fee' => 1500.00,
                'scenario' => 'cancelled_with_fee'
            ]);
            
            $this->createPayment($reservation, 3000.00, 'reservation_fee', 'completed');
            $this->createPayment($reservation, 1500.00, 'cancellation_fee', 'completed');
            
            // 4. Refund Scenarios
            $reservation = $this->createReservation($branch, [
                'name' => 'Refund Case',
                'phone' => '+94773333303',
                'email' => 'refund@example.com',
                'date' => Carbon::today()->addDays(20),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'number_of_people' => 4,
                'status' => 'cancelled',
                'reservation_fee' => 2500.00,
                'scenario' => 'refund_case'
            ]);
            
            $this->createPayment($reservation, 2500.00, 'reservation_fee', 'completed');
            $this->createPayment($reservation, -2500.00, 'refund', 'completed');
        }
    }

    private function seedRecurringScenarios(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  🔄 Seeding recurring scenarios for {$branch->name}");
            
            // 1. Weekly Recurring Reservations
            $baseDate = Carbon::next(Carbon::FRIDAY);
            for ($week = 0; $week < 8; $week++) {
                $this->createReservation($branch, [
                    'name' => 'Weekly Regular',
                    'phone' => '+94774444400',
                    'email' => 'weekly@example.com',
                    'date' => $baseDate->copy()->addWeeks($week),
                    'start_time' => '19:00:00',
                    'end_time' => '21:00:00',
                    'number_of_people' => 2,
                    'status' => 'confirmed',
                    'comments' => "Weekly date night - Week " . ($week + 1),
                    'scenario' => 'weekly_recurring'
                ]);
            }
            
            // 2. Monthly Business Meetings
            $monthlyDate = Carbon::now()->startOfMonth()->addDays(14);
            for ($month = 0; $month < 6; $month++) {
                $this->createReservation($branch, [
                    'name' => 'Monthly Board Meeting',
                    'phone' => '+94774444401',
                    'email' => 'board@company.com',
                    'date' => $monthlyDate->copy()->addMonths($month),
                    'start_time' => '12:00:00',
                    'end_time' => '15:00:00',
                    'number_of_people' => 10,
                    'status' => 'confirmed',
                    'comments' => "Monthly board meeting - Month " . ($month + 1),
                    'scenario' => 'monthly_recurring'
                ]);
            }
            
            // 3. Seasonal Events
            $seasonalDates = [
                Carbon::create(date('Y'), 12, 25), // Christmas
                Carbon::create(date('Y') + 1, 2, 14), // Valentine's Day
                Carbon::create(date('Y') + 1, 4, 13), // New Year (Sinhala)
                Carbon::create(date('Y') + 1, 5, 1),  // Labor Day
            ];
            
            foreach ($seasonalDates as $index => $date) {
                $events = ['Christmas Dinner', 'Valentine Special', 'New Year Celebration', 'Labor Day Lunch'];
                $this->createReservation($branch, [
                    'name' => 'Seasonal Celebration',
                    'phone' => '+94774444402',
                    'email' => 'seasonal@example.com',
                    'date' => $date,
                    'start_time' => '18:00:00',
                    'end_time' => '22:00:00',
                    'number_of_people' => 8,
                    'status' => 'confirmed',
                    'comments' => $events[$index],
                    'scenario' => 'seasonal_recurring'
                ]);
            }
        }
    }

    private function seedSpecialEventScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  🎉 Seeding special event scenarios for {$branch->name}");
            
            // 1. Wedding Rehearsal Dinner
            $this->createReservation($branch, [
                'name' => 'Wedding Rehearsal',
                'phone' => '+94775555500',
                'email' => 'wedding@example.com',
                'date' => Carbon::today()->addDays(25),
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
                'number_of_people' => 25,
                'status' => 'confirmed',
                'comments' => 'Wedding rehearsal dinner - special menu required',
                'scenario' => 'wedding_rehearsal'
            ]);
            
            // 2. Birthday Party with Special Requirements
            $this->createReservation($branch, [
                'name' => 'Birthday Celebration',
                'phone' => '+94775555501',
                'email' => 'birthday@example.com',
                'date' => Carbon::today()->addDays(12),
                'start_time' => '19:00:00',
                'end_time' => '22:00:00',
                'number_of_people' => 15,
                'status' => 'confirmed',
                'comments' => 'Birthday party - cake arrangement needed, dietary restrictions',
                'scenario' => 'birthday_party'
            ]);
            
            // 3. Corporate Launch Event
            $this->createReservation($branch, [
                'name' => 'Product Launch Event',
                'phone' => '+94775555502',
                'email' => 'launch@company.com',
                'date' => Carbon::today()->addDays(18),
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 40,
                'status' => 'confirmed',
                'comments' => 'Product launch event - AV setup required, cocktail reception',
                'scenario' => 'corporate_launch'
            ]);
            
            // 4. Anniversary Celebration
            $this->createReservation($branch, [
                'name' => 'Anniversary Special',
                'phone' => '+94775555503',
                'email' => 'anniversary@example.com',
                'date' => Carbon::today()->addDays(8),
                'start_time' => '19:30:00',
                'end_time' => '22:00:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'comments' => '50th wedding anniversary - special table decoration requested',
                'scenario' => 'anniversary'
            ]);
        }
    }

    private function seedStaffScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  👥 Seeding staff scenarios for {$branch->name}");
            
            $branchStewards = $this->stewards->where('branch_id', $branch->id);
            
            if ($branchStewards->isEmpty()) {
                continue; // Skip if no stewards for this branch
            }
            
            // 1. Specific Steward Request
            $preferredSteward = $branchStewards->first();
            $this->createReservation($branch, [
                'name' => 'Preferred Steward',
                'phone' => '+94776666600',
                'email' => 'preferred@example.com',
                'date' => Carbon::tomorrow(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 4,
                'status' => 'confirmed',
                'steward_id' => $preferredSteward->id,
                'comments' => 'Regular customer - prefers ' . $preferredSteward->name,
                'scenario' => 'preferred_steward'
            ]);
            
            // 2. Multiple Steward Coverage
            $this->createReservation($branch, [
                'name' => 'Large Event Multiple Staff',
                'phone' => '+94776666601',
                'email' => 'largeevent@example.com',
                'date' => Carbon::today()->addDays(14),
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'number_of_people' => 30,
                'status' => 'confirmed',
                'comments' => 'Large event requiring multiple staff coverage',
                'scenario' => 'multiple_staff'
            ]);
            
            // 3. Staff Training Event
            $this->createReservation($branch, [
                'name' => 'Staff Training Session',
                'phone' => '+94776666602',
                'email' => 'training@restaurant.com',
                'date' => Carbon::today()->addDays(21),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'number_of_people' => 8,
                'status' => 'confirmed',
                'comments' => 'Internal staff training and service practice',
                'scenario' => 'staff_training'
            ]);
        }
    }

    private function seedNoShowScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  ❌ Seeding no-show scenarios for {$branch->name}");
            
            // 1. No-Show with Penalty
            $reservation = $this->createReservation($branch, [
                'name' => 'No Show Customer',
                'phone' => '+94777777700',
                'email' => 'noshow@example.com',
                'date' => Carbon::yesterday(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 4,
                'status' => 'no_show',
                'reservation_fee' => 2000.00,
                'comments' => 'Customer did not show up',
                'scenario' => 'no_show_penalty'
            ]);
            
            $this->createPayment($reservation, 2000.00, 'no_show_penalty', 'completed');
            
            // 2. Late Arrival (Grace Period)
            $this->createReservation($branch, [
                'name' => 'Late Arrival',
                'phone' => '+94777777701',
                'email' => 'late@example.com',
                'date' => Carbon::yesterday(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 2,
                'status' => 'completed',
                'check_in_time' => Carbon::yesterday()->setTime(19, 25), // 25 minutes late
                'comments' => 'Arrived 25 minutes late - accommodated',
                'scenario' => 'late_arrival'
            ]);
            
            // 3. Last-Minute Cancellation
            $this->createReservation($branch, [
                'name' => 'Last Minute Cancel',
                'phone' => '+94777777702',
                'email' => 'lastcancel@example.com',
                'date' => Carbon::today(),
                'start_time' => '20:00:00',
                'end_time' => '22:00:00',
                'number_of_people' => 6,
                'status' => 'cancelled',
                'cancellation_fee' => 1500.00,
                'comments' => 'Cancelled 2 hours before reservation time',
                'scenario' => 'last_minute_cancellation'
            ]);
            
            // 4. Partial Group Show-Up
            $this->createReservation($branch, [
                'name' => 'Partial Group',
                'phone' => '+94777777703',
                'email' => 'partial@example.com',
                'date' => Carbon::yesterday(),
                'start_time' => '18:30:00',
                'end_time' => '21:00:00',
                'number_of_people' => 8,
                'status' => 'completed',
                'check_in_time' => Carbon::yesterday()->setTime(18, 35),
                'comments' => 'Reservation for 8, only 5 people showed up',
                'scenario' => 'partial_show'
            ]);
        }
    }

    private function seedCrossBranchScenarios(): void
    {
        if ($this->branches->count() < 2) {
            return; // Skip if not enough branches
        }
        
        $this->command->info("  🏢 Seeding cross-branch scenarios");
        
        // 1. Customer with Multiple Branch Reservations
        $customer = [
            'name' => 'Multi Branch Customer',
            'phone' => '+94778888800',
            'email' => 'multibranch@example.com'
        ];
        
        foreach ($this->branches->take(3) as $index => $branch) {
            $this->createReservation($branch, [
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'date' => Carbon::today()->addDays($index + 1),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'number_of_people' => 2,
                'status' => 'confirmed',
                'comments' => "Cross-branch customer - Branch " . ($index + 1),
                'scenario' => 'cross_branch_customer'
            ]);
        }
        
        // 2. Organization-Wide Event
        if ($this->branches->count() >= 2) {
            $organization = $this->branches->first()->organization;
            $orgBranches = $this->branches->where('organization_id', $organization->id)->take(2);
            
            foreach ($orgBranches as $index => $branch) {
                $this->createReservation($branch, [
                    'name' => 'Organization Wide Event',
                    'phone' => '+94778888801',
                    'email' => 'orgevent@company.com',
                    'date' => Carbon::today()->addDays(30),
                    'start_time' => '18:00:00',
                    'end_time' => '22:00:00',
                    'number_of_people' => 20,
                    'status' => 'confirmed',
                    'comments' => "Company-wide celebration - " . $branch->name,
                    'scenario' => 'organization_event'
                ]);
            }
        }
        
        // 3. Transferred Reservation
        $originalBranch = $this->branches->first();
        $transferBranch = $this->branches->skip(1)->first();
        
        // Original cancelled reservation
        $this->createReservation($originalBranch, [
            'name' => 'Transfer Customer',
            'phone' => '+94778888802',
            'email' => 'transfer@example.com',
            'date' => Carbon::today()->addDays(7),
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 4,
            'status' => 'cancelled',
            'comments' => 'Cancelled - transferred to ' . $transferBranch->name,
            'scenario' => 'transfer_original'
        ]);
        
        // New reservation at different branch
        $this->createReservation($transferBranch, [
            'name' => 'Transfer Customer',
            'phone' => '+94778888802',
            'email' => 'transfer@example.com',
            'date' => Carbon::today()->addDays(7),
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'number_of_people' => 4,
            'status' => 'confirmed',
            'comments' => 'Transferred from ' . $originalBranch->name,
            'scenario' => 'transfer_new'
        ]);
    }

    private function createReservation(Branch $branch, array $data): Reservation
    {
        // Assign steward if not provided
        if (!isset($data['steward_id']) && isset($data['scenario']) && $data['scenario'] !== 'preferred_steward') {
            $branchStewards = $this->stewards->where('branch_id', $branch->id);
            if ($branchStewards->isNotEmpty()) {
                $data['steward_id'] = $branchStewards->random()->id;
            }
        }
        
        // Set default fees if not provided
        $data['reservation_fee'] = $data['reservation_fee'] ?? ($branch->reservation_fee ?? 0);
        $data['cancellation_fee'] = $data['cancellation_fee'] ?? ($branch->cancellation_fee ?? 0);
        
        // Add branch_id
        $data['branch_id'] = $branch->id;
        
        // Create reservation
        $reservation = Reservation::create($data);
        
        // Auto-assign tables for confirmed reservations
        if ($reservation->status === 'confirmed') {
            $this->assignTablesToReservation($reservation);
        }
        
        // Track reservation data for summary
        $scenario = $data['scenario'] ?? 'general';
        if (!isset($this->reservationData[$scenario])) {
            $this->reservationData[$scenario] = 0;
        }
        $this->reservationData[$scenario]++;
        
        return $reservation;
    }

    private function createMultipleReservations(Branch $branch, array $config): void
    {
        foreach ($config['time_slots'] as $index => $timeSlot) {
            $this->createReservation($branch, [
                'name' => "Peak Time Customer " . ($index + 1),
                'phone' => '+9477' . str_pad($index + 1000000, 7, '0', STR_PAD_LEFT),
                'email' => "peak{$index}@example.com",
                'date' => $config['date'],
                'start_time' => $timeSlot[0],
                'end_time' => $timeSlot[1],
                'number_of_people' => rand(2, 6),
                'status' => 'confirmed',
                'scenario' => $config['scenario']
            ]);
        }
    }

    private function assignTablesToReservation(Reservation $reservation): void
    {
        $availableTables = $this->tables
            ->where('branch_id', $reservation->branch_id)
            ->sortBy('capacity');
        
        $requiredCapacity = $reservation->number_of_people;
        $selectedTables = collect();
        $totalCapacity = 0;
        
        foreach ($availableTables as $table) {
            if ($totalCapacity >= $requiredCapacity) {
                break;
            }
            
            $selectedTables->push($table);
            $totalCapacity += $table->capacity;
        }
        
        if ($totalCapacity >= $requiredCapacity) {
            $reservation->tables()->sync($selectedTables->pluck('id')->toArray());
        }
    }

    private function createPayment(Reservation $reservation, float $amount, string $type, string $status): Payment
    {
        return Payment::create([
            'payable_type' => Reservation::class,
            'payable_id' => $reservation->id,
            'amount' => $amount,
            'payment_method' => collect(['cash', 'card', 'bank_transfer', 'online_portal'])->random(),
            'status' => $status,
            'payment_reference' => 'RES-' . $reservation->id . '-' . strtoupper($type) . '-' . time(),
            'notes' => ucfirst(str_replace('_', ' ', $type)) . ' for reservation #' . $reservation->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function displayReservationSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 EXHAUSTIVE RESERVATION SEEDING SUMMARY');
        $this->command->info('═══════════════════════════════════════════════');
        
        $totalReservations = Reservation::count();
        $this->command->info("📋 Total Reservations Created: {$totalReservations}");
        
        $this->command->newLine();
        $this->command->info('🎯 SCENARIO BREAKDOWN:');
        
        foreach ($this->reservationData as $scenario => $count) {
            $scenarioName = ucwords(str_replace('_', ' ', $scenario));
            $this->command->info(sprintf('  %-25s: %d reservations', $scenarioName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('📈 STATUS DISTRIBUTION:');
        
        $statusCounts = Reservation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
            
        foreach ($statusCounts as $status => $count) {
            $statusName = ucfirst($status);
            $this->command->info(sprintf('  %-15s: %d reservations', $statusName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('💰 PAYMENT STATISTICS:');
        $totalPayments = Payment::where('payable_type', Reservation::class)->count();
        $totalPaymentAmount = Payment::where('payable_type', Reservation::class)->sum('amount');
        
        $this->command->info("  Total Payments: {$totalPayments}");
        $this->command->info("  Total Amount: LKR " . number_format($totalPaymentAmount, 2));
        
        $this->command->newLine();
        $this->command->info('✅ All reservation scenarios have been comprehensively seeded!');
        $this->command->info('🔍 Scenarios include: conflicts, large groups, recurring patterns,');
        $this->command->info('    no-shows, special events, staff assignments, and payment flows.');
    }
}
