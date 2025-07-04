<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Branch;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Reservation Availability Scanner Service
 * Handles time-slot conflicts and resource assignment with enhanced logic
 */
class ReservationAvailabilityService
{
    /**
     * Check if a time slot is available for reservation
     */
    public function checkTimeSlotAvailability(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime,
        int $numberOfPeople,
        ?int $excludeReservationId = null
    ): array {
        try {
            $branch = Branch::with('organization')->findOrFail($branchId);

            // Validate branch is active and organization is active
            if (!$branch->is_active || !$branch->organization->is_active) {
                return [
                    'available' => false,
                    'message' => 'Branch or organization is not active',
                    'conflicts' => []
                ];
            }

            // Validate business hours
            $businessHoursCheck = $this->validateBusinessHours($branch, $date, $startTime, $endTime);
            if (!$businessHoursCheck['valid']) {
                return [
                    'available' => false,
                    'message' => $businessHoursCheck['message'],
                    'conflicts' => []
                ];
            }

            // Parse times with buffer
            $requestStart = Carbon::parse("$date $startTime");
            $requestEnd = Carbon::parse("$date $endTime");
            $bufferMinutes = 15; // 15-minute buffer between reservations

            // Find conflicting reservations
            $conflicts = $this->findTimeConflicts(
                $branchId,
                $requestStart,
                $requestEnd,
                $bufferMinutes,
                $excludeReservationId
            );

            if ($conflicts->isNotEmpty()) {
                return [
                    'available' => false,
                    'message' => 'Time slot conflicts with existing reservations',
                    'conflicts' => $conflicts->map(function ($reservation) {
                        return [
                            'id' => $reservation->id,
                            'customer' => $reservation->name,
                            'start_time' => $reservation->start_time->format('H:i'),
                            'end_time' => $reservation->end_time->format('H:i'),
                            'people' => $reservation->number_of_people
                        ];
                    })->toArray()
                ];
            }

            // Check capacity constraints
            $capacityCheck = $this->checkCapacityConstraints(
                $branchId,
                $requestStart,
                $requestEnd,
                $numberOfPeople,
                $excludeReservationId
            );

            if (!$capacityCheck['available']) {
                return $capacityCheck;
            }

            // Find available tables if needed
            $tableAssignment = $this->findAvailableTables(
                $branchId,
                $requestStart,
                $requestEnd,
                $numberOfPeople,
                $excludeReservationId
            );

            return [
                'available' => true,
                'message' => 'Time slot is available',
                'conflicts' => [],
                'capacity_info' => $capacityCheck,
                'table_assignment' => $tableAssignment,
                'suggested_times' => []
            ];

        } catch (\Exception $e) {
            Log::error('Reservation availability check failed', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);

            return [
                'available' => false,
                'message' => 'Unable to check availability: ' . $e->getMessage(),
                'conflicts' => []
            ];
        }
    }

    /**
     * Validate business hours
     */
    private function validateBusinessHours(Branch $branch, string $date, string $startTime, string $endTime): array
    {
        $branchOpenTime = $branch->opening_time ? $branch->opening_time->format('H:i') : '08:00';
        $branchCloseTime = $branch->closing_time ? $branch->closing_time->format('H:i') : '22:00';

        if ($startTime < $branchOpenTime) {
            return [
                'valid' => false,
                'message' => "Start time must be after branch opening time ({$branchOpenTime})"
            ];
        }

        if ($endTime > $branchCloseTime) {
            return [
                'valid' => false,
                'message' => "End time must be before branch closing time ({$branchCloseTime})"
            ];
        }

        // For same-day reservations, ensure start time is at least 30 minutes from now
        if ($date === now()->toDateString()) {
            $minStartTime = now()->addMinutes(30)->format('H:i');
            if ($startTime < $minStartTime) {
                return [
                    'valid' => false,
                    'message' => "For same-day reservations, start time must be at least 30 minutes from now"
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Find time conflicts with existing reservations
     */
    private function findTimeConflicts(
        int $branchId,
        Carbon $requestStart,
        Carbon $requestEnd,
        int $bufferMinutes,
        ?int $excludeReservationId = null
    ) {
        $query = Reservation::where('branch_id', $branchId)
            ->where('date', $requestStart->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->get()->filter(function ($reservation) use ($requestStart, $requestEnd, $bufferMinutes) {
            $existingStart = Carbon::parse($reservation->date . ' ' . $reservation->start_time->format('H:i'));
            $existingEnd = Carbon::parse($reservation->date . ' ' . $reservation->end_time->format('H:i'));

            // Add buffer time
            $bufferedStart = $existingStart->copy()->subMinutes($bufferMinutes);
            $bufferedEnd = $existingEnd->copy()->addMinutes($bufferMinutes);

            return $this->timePeriodsOverlap($requestStart, $requestEnd, $bufferedStart, $bufferedEnd);
        });
    }

    /**
     * Check capacity constraints
     */
    private function checkCapacityConstraints(
        int $branchId,
        Carbon $requestStart,
        Carbon $requestEnd,
        int $numberOfPeople,
        ?int $excludeReservationId = null
    ): array {
        $branch = Branch::find($branchId);
        $totalCapacity = $branch->total_capacity ?? 100;

        // Get concurrent reservations
        $concurrentReservations = $this->getConcurrentReservations(
            $branchId,
            $requestStart,
            $requestEnd,
            $excludeReservationId
        );

        $usedCapacity = $concurrentReservations->sum('number_of_people');
        $availableCapacity = $totalCapacity - $usedCapacity;

        return [
            'available' => $availableCapacity >= $numberOfPeople,
            'total_capacity' => $totalCapacity,
            'used_capacity' => $usedCapacity,
            'available_capacity' => $availableCapacity,
            'required_capacity' => $numberOfPeople,
            'concurrent_reservations' => $concurrentReservations->count(),
            'message' => $availableCapacity >= $numberOfPeople
                ? 'Sufficient capacity available'
                : "Insufficient capacity. Available: {$availableCapacity}, Required: {$numberOfPeople}"
        ];
    }

    /**
     * Get concurrent reservations for a time slot
     */
    private function getConcurrentReservations(
        int $branchId,
        Carbon $requestStart,
        Carbon $requestEnd,
        ?int $excludeReservationId = null
    ) {
        $query = Reservation::where('branch_id', $branchId)
            ->where('date', $requestStart->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->get()->filter(function ($reservation) use ($requestStart, $requestEnd) {
            $existingStart = Carbon::parse($reservation->date . ' ' . $reservation->start_time->format('H:i'));
            $existingEnd = Carbon::parse($reservation->date . ' ' . $reservation->end_time->format('H:i'));

            return $this->timePeriodsOverlap($requestStart, $requestEnd, $existingStart, $existingEnd);
        });
    }

    /**
     * Check if two time periods overlap
     */
    private function timePeriodsOverlap(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2): bool
    {
        return $start1->lt($end2) && $end1->gt($start2);
    }

    /**
     * Find available tables for the reservation
     */
    private function findAvailableTables(
        int $branchId,
        Carbon $requestStart,
        Carbon $requestEnd,
        int $numberOfPeople,
        ?int $excludeReservationId = null
    ): array {
        $tables = Table::where('branch_id', $branchId)
            ->where('status', 'available')
            ->orderBy('capacity', 'asc')
            ->get();

        if ($tables->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No tables available',
                'assigned_tables' => []
            ];
        }

        // Find single table that can accommodate all people
        $singleTable = $tables->first(function ($table) use ($numberOfPeople, $requestStart, $requestEnd, $excludeReservationId) {
            return $table->capacity >= $numberOfPeople &&
                   $this->isTableAvailable($table->id, $requestStart, $requestEnd, $excludeReservationId);
        });

        if ($singleTable) {
            return [
                'success' => true,
                'message' => 'Single table assigned',
                'assigned_tables' => [$singleTable->id],
                'total_capacity' => $singleTable->capacity
            ];
        }

        // Find combination of tables
        $availableTables = $tables->filter(function ($table) use ($requestStart, $requestEnd, $excludeReservationId) {
            return $this->isTableAvailable($table->id, $requestStart, $requestEnd, $excludeReservationId);
        });

        $combination = $this->findTableCombination($availableTables->toArray(), $numberOfPeople);

        if ($combination) {
            return [
                'success' => true,
                'message' => 'Multiple tables assigned',
                'assigned_tables' => array_column($combination, 'id'),
                'total_capacity' => array_sum(array_column($combination, 'capacity'))
            ];
        }

        return [
            'success' => false,
            'message' => 'No suitable table combination found',
            'assigned_tables' => []
        ];
    }

    /**
     * Check if table is available for the time slot
     */
    private function isTableAvailable(int $tableId, Carbon $requestStart, Carbon $requestEnd, ?int $excludeReservationId = null): bool
    {
        $conflictingReservations = Reservation::where('date', $requestStart->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereHas('tables', function ($query) use ($tableId) {
                $query->where('table_id', $tableId);
            });

        if ($excludeReservationId) {
            $conflictingReservations->where('id', '!=', $excludeReservationId);
        }

        $conflictingReservations = $conflictingReservations->get();

        foreach ($conflictingReservations as $reservation) {
            $existingStart = Carbon::parse($reservation->date . ' ' . $reservation->start_time->format('H:i'));
            $existingEnd = Carbon::parse($reservation->date . ' ' . $reservation->end_time->format('H:i'));

            if ($this->timePeriodsOverlap($requestStart, $requestEnd, $existingStart, $existingEnd)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find table combination that fits the number of people
     */
    private function findTableCombination(array $tables, int $numberOfPeople): ?array
    {
        // Simple greedy algorithm - can be improved with dynamic programming
        usort($tables, function ($a, $b) {
            return $b['capacity'] - $a['capacity']; // Sort by capacity descending
        });

        $combination = [];
        $totalCapacity = 0;

        foreach ($tables as $table) {
            if ($totalCapacity < $numberOfPeople) {
                $combination[] = $table;
                $totalCapacity += $table['capacity'];
            }
        }

        return $totalCapacity >= $numberOfPeople ? $combination : null;
    }

    /**
     * Create reservation with automatic table assignment
     */
    public function createReservationWithTableAssignment(array $reservationData): array
    {
        try {
            DB::beginTransaction();

            // Check availability first
            $availability = $this->checkTimeSlotAvailability(
                $reservationData['branch_id'],
                $reservationData['date'],
                $reservationData['start_time'],
                $reservationData['end_time'],
                $reservationData['number_of_people']
            );

            if (!$availability['available']) {
                return [
                    'success' => false,
                    'message' => $availability['message'],
                    'suggestions' => $availability['suggested_times'] ?? []
                ];
            }

            // Create reservation
            $reservation = Reservation::create([
                'name' => $reservationData['name'],
                'phone' => $reservationData['phone'],
                'email' => $reservationData['email'] ?? null,
                'date' => $reservationData['date'],
                'start_time' => $reservationData['start_time'],
                'end_time' => $reservationData['end_time'],
                'number_of_people' => $reservationData['number_of_people'],
                'branch_id' => $reservationData['branch_id'],
                'status' => 'confirmed',
                'comments' => $reservationData['comments'] ?? null,
                'reservation_fee' => $reservationData['reservation_fee'] ?? 0,
                'user_id' => optional(auth())->id(),
            ]);

            // Assign tables if available
            if (isset($reservationData['auto_assign_tables']) && $reservationData['auto_assign_tables']) {
                $tableAssignment = $this->findAvailableTables(
                    $reservationData['branch_id'],
                    Carbon::parse($reservationData['date'] . ' ' . $reservationData['start_time']),
                    Carbon::parse($reservationData['date'] . ' ' . $reservationData['end_time']),
                    $reservationData['number_of_people']
                );

                if ($tableAssignment['success']) {
                    $reservation->tables()->attach($tableAssignment['assigned_tables']);
                }
            }

            DB::commit();

            Log::info('Reservation created successfully', [
                'reservation_id' => $reservation->id,
                'branch_id' => $reservationData['branch_id'],
                'date' => $reservationData['date'],
                'time' => $reservationData['start_time'] . ' - ' . $reservationData['end_time']
            ]);

            return [
                'success' => true,
                'message' => 'Reservation created successfully',
                'reservation' => $reservation,
                'table_assignment' => $tableAssignment ?? null
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation creation failed', [
                'error' => $e->getMessage(),
                'data' => $reservationData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get alternative time suggestions
     */
    public function suggestAlternativeTimes(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime,
        int $numberOfPeople
    ): array {
        $branch = Branch::find($branchId);
        $suggestions = [];

        $openTime = $branch->opening_time ? $branch->opening_time->format('H:i') : '08:00';
        $closeTime = $branch->closing_time ? $branch->closing_time->format('H:i') : '22:00';

        // Calculate duration of requested reservation
        $requestStart = Carbon::parse($startTime);
        $requestEnd = Carbon::parse($endTime);
        $duration = $requestEnd->diffInMinutes($requestStart);

        // Generate time slots every 30 minutes
        $currentTime = Carbon::parse($openTime);
        $endOfDay = Carbon::parse($closeTime);

        while ($currentTime->addMinutes(30)->addMinutes($duration)->lte($endOfDay)) {
            $slotStart = $currentTime->format('H:i');
            $slotEnd = $currentTime->copy()->addMinutes($duration)->format('H:i');

            $availability = $this->checkTimeSlotAvailability(
                $branchId,
                $date,
                $slotStart,
                $slotEnd,
                $numberOfPeople
            );

            if ($availability['available']) {
                $suggestions[] = [
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'available_capacity' => $availability['capacity_info']['available_capacity'] ?? 0
                ];

                if (count($suggestions) >= 5) {
                    break;
                }
            }
        }

        return $suggestions;
    }
}
