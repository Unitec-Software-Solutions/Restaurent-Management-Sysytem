<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Models\Admin;
use Carbon\Carbon;
use App\Enums\ReservationType;

class ReservationLifecycleSeeder extends Seeder
{
    /**
     * Simulate reservation workflow with state transitions
     */
    public function run(): void
    {
        $this->command->info('🎫 Simulating Reservations Workflow...');
        
        $organizations = Organization::with(['branches', 'subscriptionPlan'])->get();
        
        foreach ($organizations as $organization) {
            if ($this->organizationHasReservationAccess($organization)) {
                $this->createReservationsForOrganization($organization);
            } else {
                $this->command->info("  ⏭️ Skipping {$organization->name} - No reservation access in subscription");
            }
        }
        
        $this->command->info('✅ Reservation workflows simulated successfully');
    }

    private function organizationHasReservationAccess(Organization $organization): bool
    {
        $modules = $organization->subscriptionPlan->modules ?? [];
        return collect($modules)->contains(function ($module) {
            return isset($module['name']) && $module['name'] === 'reservation';
        });
    }

    private function createReservationsForOrganization(Organization $organization): void
    {
        $this->command->info("  🎫 Creating reservations for: {$organization->name}");
        
        foreach ($organization->branches as $branch) {
            if ($branch->reservation_fee > 0) { // Only branches that accept reservations
                $this->createTablesForBranch($branch);
                $this->createCustomersForBranch($branch);
                $this->createReservationsForBranch($branch);
            }
        }
    }

    private function createTablesForBranch(Branch $branch): void
    {
        $tableCount = min(intval($branch->total_capacity / 4), 20); // 4 people per table, max 20 tables
        
        for ($i = 1; $i <= $tableCount; $i++) {
            Table::create([
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
                'number' => 'T' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'capacity' => rand(2, 8),
                'status' => 'available',
                'location' => $this->getRandomTableLocation(),
                'is_active' => true,
                'x_position' => rand(10, 90),
                'y_position' => rand(10, 90),
                'description' => 'Table for ' . rand(2, 8) . ' guests'
            ]);
        }
        
        $this->command->info("    🪑 Created {$tableCount} tables for {$branch->name}");
    }

    private function createCustomersForBranch(Branch $branch): void
    {
        $customerNames = [
            'Arjuna Ranatunga', 'Priya Jayawardene', 'Kamal Silva', 'Nilanthi Fernando',
            'Roshan Perera', 'Sanduni Wickramasinghe', 'Chaminda Rathnayake', 'Dilani Gunasekara',
            'Tharanga Mendis', 'Ishara Dissanayake', 'Nuwan Kulasekara', 'Manjula Samaraweera',
            'Kumari Jayasena', 'Sunil Gavaskar', 'Anjali Bandara'
        ];

        for ($i = 0; $i < 15; $i++) {
            $name = $customerNames[$i];
            $firstName = explode(' ', $name)[0];
            
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@email.com',
                'phone_number' => '+94 7' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'password' => bcrypt('customer123'),
                'is_registered' => rand(0, 1) == 1,
                'organization_id' => $branch->organization_id,
                'branch_id' => null, // Customers can visit any branch
                'email_verified_at' => rand(0, 1) == 1 ? now() : null,
                'preferences' => json_encode([
                    'dietary_restrictions' => $this->getRandomDietaryRestrictions(),
                    'preferred_seating' => $this->getRandomSeatingPreference(),
                    'contact_preference' => ['email', 'sms'][rand(0, 1)]
                ])
            ]);
        }
    }

    private function createReservationsForBranch(Branch $branch): void
    {
        $tables = Table::where('branch_id', $branch->id)->get();
        $customers = User::where('organization_id', $branch->organization_id)->get();
        $stewards = Admin::where('branch_id', $branch->id)->get();
        
        if ($tables->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $reservationCount = rand(40, 60);
        $this->command->info("    🎫 Creating {$reservationCount} reservations for {$branch->name}");
        
        for ($i = 0; $i < $reservationCount; $i++) {
            $this->createSingleReservation($branch, $tables, $customers, $stewards);
        }
    }

    private function createSingleReservation(Branch $branch, $tables, $customers, $stewards): void
    {
        $customer = $customers->random();
        $table = $tables->random();
        $steward = $stewards->isNotEmpty() ? $stewards->random() : null;
        
        // Generate reservation date/time (past, present, future)
        $reservationDateTime = $this->generateReservationDateTime();
        $status = $this->determineReservationStatus($reservationDateTime);
        
        $reservation = Reservation::create([
            'organization_id' => $branch->organization_id,
            'branch_id' => $branch->id,
            'table_id' => $table->id,
            'user_id' => $customer->id,
            'steward_id' => $steward ? $steward->id : null,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'party_size' => rand(1, $table->seating_capacity),
            'reservation_date' => $reservationDateTime->toDateString(),
            'reservation_time' => $reservationDateTime->toTimeString(),
            'duration_minutes' => rand(60, 180), // 1-3 hours
            'status' => $status,
            'reservation_type' => $this->getRandomReservationType(),
            'special_requests' => $this->getRandomSpecialRequests(),
            'notes' => $this->getRandomReservationNotes(),
            'created_at' => $reservationDateTime->copy()->subDays(rand(1, 7)),
            'confirmed_at' => in_array($status, ['confirmed', 'checked_in', 'completed']) ? 
                            $reservationDateTime->copy()->subDays(rand(1, 3)) : null,
            'checked_in_at' => in_array($status, ['checked_in', 'completed']) ? 
                             $reservationDateTime->copy()->addMinutes(rand(-15, 15)) : null,
            'completed_at' => $status === 'completed' ? 
                            $reservationDateTime->copy()->addMinutes(rand(60, 180)) : null,
            'cancelled_at' => $status === 'cancelled' ? 
                            $reservationDateTime->copy()->subHours(rand(1, 24)) : null,
            'no_show_at' => $status === 'no_show' ? 
                          $reservationDateTime->copy()->addMinutes(15) : null
        ]);

        // Create state transition history for demonstration
        $this->createReservationStateHistory($reservation, $status, $reservationDateTime);
    }

    private function createReservationStateHistory(Reservation $reservation, string $finalStatus, Carbon $reservationDateTime): void
    {
        // This would typically be handled by events/observers in a real system
        $stateTransitions = [
            'pending' => [],
            'confirmed' => ['pending'],
            'checked_in' => ['pending', 'confirmed'],
            'completed' => ['pending', 'confirmed', 'checked_in'],
            'cancelled' => ['pending'],
            'no_show' => ['pending', 'confirmed']
        ];

        $states = $stateTransitions[$finalStatus] ?? [];
        
        // Log state transitions (this would typically be in a separate activity log table)
        foreach ($states as $index => $state) {
            $timestamp = $reservationDateTime->copy()->subDays(count($states) - $index);
            
            // In a real system, you'd use an activity log package
            // ActivityLog::create([
            //     'subject_type' => 'App\Models\Reservation',
            //     'subject_id' => $reservation->id,
            //     'description' => "Reservation status changed to {$state}",
            //     'created_at' => $timestamp
            // ]);
        }
    }

    private function generateReservationDateTime(): Carbon
    {
        $now = now();
        
        // 30% past reservations, 20% today, 50% future
        $type = rand(1, 100);
        
        if ($type <= 30) {
            // Past reservations
            return $now->copy()->subDays(rand(1, 30))->setHour(rand(11, 21))->setMinute([0, 15, 30, 45][rand(0, 3)]);
        } elseif ($type <= 50) {
            // Today's reservations
            return $now->copy()->setHour(rand(11, 21))->setMinute([0, 15, 30, 45][rand(0, 3)]);
        } else {
            // Future reservations
            return $now->copy()->addDays(rand(1, 30))->setHour(rand(11, 21))->setMinute([0, 15, 30, 45][rand(0, 3)]);
        }
    }

    private function determineReservationStatus(Carbon $reservationDateTime): string
    {
        $now = now();
        
        if ($reservationDateTime->isPast()) {
            // Past reservations - determine final status
            $statuses = ['completed', 'cancelled', 'no_show'];
            $weights = [70, 20, 10]; // 70% completed, 20% cancelled, 10% no-show
            
            $random = rand(1, 100);
            if ($random <= $weights[0]) return 'completed';
            if ($random <= $weights[0] + $weights[1]) return 'cancelled';
            return 'no_show';
        } elseif ($reservationDateTime->isToday()) {
            // Today's reservations
            if ($reservationDateTime->isPast()) {
                return ['checked_in', 'completed'][rand(0, 1)];
            } else {
                return ['pending', 'confirmed'][rand(0, 1)];
            }
        } else {
            // Future reservations
            return ['pending', 'confirmed'][rand(0, 1)];
        }
    }

    // Helper methods for generating random data
    private function getRandomTableType(): string
    {
        return ['standard', 'booth', 'high_top', 'outdoor', 'private'][rand(0, 4)];
    }

    private function getRandomTableLocation(): string
    {
        return ['main_hall', 'window_side', 'outdoor_patio', 'private_room', 'bar_area'][rand(0, 4)];
    }

    private function getTableFeatures(): array
    {
        $features = ['high_chair_available', 'wheelchair_accessible', 'power_outlet', 'scenic_view', 'quiet_area'];
        return array_slice($features, 0, rand(1, 3));
    }

    private function getTableNotes(): ?string
    {
        $notes = [
            'Near window with garden view',
            'Quiet corner table',
            'High traffic area',
            'Close to kitchen',
            'Perfect for couples'
        ];
        
        return rand(0, 1) ? $notes[rand(0, count($notes) - 1)] : null;
    }

    private function getRandomDietaryRestrictions(): array
    {
        $restrictions = ['vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'nut_allergy'];
        return rand(0, 1) ? [array_rand(array_flip($restrictions))] : [];
    }

    private function getRandomSeatingPreference(): string
    {
        return ['window', 'booth', 'outdoor', 'quiet', 'no_preference'][rand(0, 4)];
    }

    private function getRandomReservationType(): string
    {
        return ['regular', 'special_occasion', 'business', 'group', 'date'][rand(0, 4)];
    }

    private function getRandomSpecialRequests(): ?string
    {
        $requests = [
            'Birthday celebration - please arrange cake',
            'Anniversary dinner - quiet table preferred',
            'Business meeting - need WiFi',
            'Wheelchair accessible table required',
            'Child-friendly seating needed',
            'Surprise proposal setup'
        ];
        
        return rand(0, 1) ? $requests[rand(0, count($requests) - 1)] : null;
    }

    private function getRandomReservationNotes(): ?string
    {
        $notes = [
            'Regular customer',
            'First time visitor',
            'Celebrating birthday',
            'Large group expected',
            'VIP customer',
            'Prefers corner table'
        ];
        
        return rand(0, 1) ? $notes[rand(0, count($notes) - 1)] : null;
    }
}
