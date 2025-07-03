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
            $tableNumber = 'T' . str_pad($i, 2, '0', STR_PAD_LEFT);
            
            // Check if table already exists
            $existingTable = Table::where('branch_id', $branch->id)
                                 ->where('number', $tableNumber)
                                 ->first();
                                 
            if (!$existingTable) {
                Table::create([
                    'branch_id' => $branch->id,
                    'organization_id' => $branch->organization_id,
                    'number' => $tableNumber,
                    'capacity' => rand(2, 8),
                    'status' => 'available',
                    'location' => $this->getRandomTableLocation(),
                    'is_active' => true,
                    'x_position' => rand(10, 90),
                    'y_position' => rand(10, 90),
                    'description' => 'Table for ' . rand(2, 8) . ' guests'
                ]);
            }
        }
        
        $actualTableCount = Table::where('branch_id', $branch->id)->count();
        $this->command->info("    🪑 Created/verified {$actualTableCount} tables for {$branch->name}");
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
            $email = strtolower(str_replace(' ', '.', $name)) . '@email.com';
            
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            
            if (!$existingUser) {
                User::create([
                    'name' => $name,
                    'email' => $email,
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
            'branch_id' => $branch->id,
            'user_id' => $customer->id,
            'steward_id' => $steward ? $steward->id : null,
            'name' => $customer->name,
            'phone' => $customer->phone_number,
            'email' => $customer->email,
            'number_of_people' => rand(1, 8),
            'date' => $reservationDateTime->toDateString(),
            'start_time' => $reservationDateTime->toTimeString(),
            'end_time' => $reservationDateTime->copy()->addMinutes(rand(60, 180))->toTimeString(),
            'status' => $status,
            'type' => $this->getRandomReservationType(),
            'comments' => $this->getRandomReservationNotes(),
            'reservation_fee' => rand(0, 1) ? rand(5, 25) : 0,
            'cancellation_fee' => rand(0, 1) ? rand(5, 15) : 0,
            'check_in_time' => in_array($status, ['completed']) ? 
                             $reservationDateTime->copy()->addMinutes(rand(-15, 15)) : null,
            'check_out_time' => $status === 'completed' ? 
                            $reservationDateTime->copy()->addMinutes(rand(60, 180)) : null
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
            'completed' => ['pending', 'confirmed'],
            'cancelled' => ['pending']
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
            $statuses = ['completed', 'cancelled'];
            $weights = [80, 20]; // 80% completed, 20% cancelled
            
            $random = rand(1, 100);
            if ($random <= $weights[0]) return 'completed';
            return 'cancelled';
        } elseif ($reservationDateTime->isToday()) {
            // Today's reservations
            if ($reservationDateTime->isPast()) {
                return ['confirmed', 'completed'][rand(0, 1)];
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
        return ['online', 'in_call', 'walk_in'][rand(0, 2)];
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
