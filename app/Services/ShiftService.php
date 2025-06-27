<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftService
{
    /**
     * Auto-assign staff based on current shift and branch needs
     */
    public function assignStaff(Branch $branch, ?Carbon $dateTime = null): Collection
    {
        $dateTime = $dateTime ?? now();
        $shift = $this->determineShift($dateTime);
        $dayOfWeek = $dateTime->dayOfWeek;
        
        return Employee::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereHas('role', function($query) use ($shift, $dayOfWeek) {
                $query->where('shift_preference', $shift)
                      ->orWhere('shift_preference', 'flexible');
            })
            ->with('role')
            ->get()
            ->filter(function($employee) use ($dateTime) {
                return $this->isEmployeeAvailable($employee, $dateTime);
            });
    }

    /**
     * Determine shift based on time
     */
    protected function determineShift(Carbon $dateTime): string
    {
        $hour = $dateTime->hour;
        
        if ($hour >= 6 && $hour < 14) {
            return 'morning';
        } elseif ($hour >= 14 && $hour < 22) {
            return 'evening';
        } else {
            return 'night';
        }
    }

    /**
     * Check if employee is available for the given time
     */
    protected function isEmployeeAvailable(Employee $employee, Carbon $dateTime): bool
    {
        // Check if employee has any conflicting schedules
        // This would typically check against a schedules table
        
        // For now, simple availability check based on working hours
        $workingHours = $employee->working_hours ?? [];
        $dayName = strtolower($dateTime->format('l'));
        
        if (empty($workingHours) || !isset($workingHours[$dayName])) {
            return true; // Default to available if no specific schedule
        }
        
        $daySchedule = $workingHours[$dayName];
        if (!$daySchedule['working']) {
            return false;
        }
        
        $currentTime = $dateTime->format('H:i');
        return $currentTime >= $daySchedule['start'] && $currentTime <= $daySchedule['end'];
    }

    /**
     * Get optimal staff distribution for a branch
     */
    public function getOptimalStaffing(Branch $branch, ?Carbon $dateTime = null): array
    {
        $dateTime = $dateTime ?? now();
        $shift = $this->determineShift($dateTime);
        
        // Basic staffing requirements (can be made configurable)
        $requirements = [
            'morning' => [
                'host/hostess' => 1,
                'servers' => 2,
                'kitchen_staff' => 2,
                'cashier' => 1
            ],
            'evening' => [
                'host/hostess' => 2,
                'servers' => 4,
                'kitchen_staff' => 3,
                'cashier' => 2,
                'bartender' => 1
            ],
            'night' => [
                'servers' => 2,
                'kitchen_staff' => 1,
                'security' => 1
            ]
        ];
        
        return $requirements[$shift] ?? [];
    }

    /**
     * Calculate labor cost for a shift
     */
    public function calculateLaborCost(Collection $assignedStaff, int $shiftHours = 8): float
    {
        return $assignedStaff->sum(function($employee) use ($shiftHours) {
            return ($employee->hourly_rate ?? 0) * $shiftHours;
        });
    }
}
