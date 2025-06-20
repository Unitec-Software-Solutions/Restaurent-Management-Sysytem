<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffAssignmentService
{
    const SHIFT_DAY = 'day';
    const SHIFT_EVENING = 'evening';
    const SHIFT_NIGHT = 'night';

    /**
     * Auto-assign staff based on current shift and workload
     */
    public function autoAssignStaff(Order $order): ?Employee
    {
        $currentShift = $this->getCurrentShift();
        $branch = $order->branch;
        
        // Get available staff for current shift
        $availableStaff = $this->getAvailableStaff($branch, $currentShift);
        
        if ($availableStaff->isEmpty()) {
            // Fallback to any available staff
            $availableStaff = $this->getAvailableStaff($branch);
        }

        if ($availableStaff->isEmpty()) {
            return null;
        }

        // Assign based on workload balancing
        return $this->selectBestStaffMember($availableStaff, $order);
    }

    /**
     * Get current shift based on time
     */
    public function getCurrentShift(): string
    {
        $hour = Carbon::now()->hour;
        
        if ($hour >= 6 && $hour < 15) {
            return self::SHIFT_DAY;
        } elseif ($hour >= 15 && $hour < 22) {
            return self::SHIFT_EVENING;
        } else {
            return self::SHIFT_NIGHT;
        }
    }

    /**
     * Get available staff for specific shift
     */
    public function getAvailableStaff(Branch $branch, ?string $shift = null): Collection
    {
        $query = Employee::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereIn('role', ['waiter', 'steward', 'server']);

        if ($shift) {
            $query->where(function($q) use ($shift) {
                $q->where('shift_preference', $shift)
                  ->orWhereNull('shift_preference'); // Include flexible staff
            });
        }

        return $query->get();
    }

    /**
     * Select the best staff member based on workload
     */
    protected function selectBestStaffMember(Collection $staff, Order $order): Employee
    {
        $staffWithWorkload = $staff->map(function ($employee) {
            $currentOrders = Order::where('steward_id', $employee->id)
                ->whereIn('status', [Order::STATUS_SUBMITTED, Order::STATUS_PREPARING])
                ->count();

            return [
                'employee' => $employee,
                'current_workload' => $currentOrders,
                'priority_score' => $this->calculatePriorityScore($employee, $currentOrders)
            ];
        });

        // Sort by priority score (lower is better)
        $bestStaff = $staffWithWorkload->sortBy('priority_score')->first();
        
        return $bestStaff['employee'];
    }

    /**
     * Calculate priority score for staff assignment
     */
    protected function calculatePriorityScore(Employee $employee, int $currentWorkload): int
    {
        $score = $currentWorkload * 10; // Base workload penalty
        
        // Experience bonus (lower score is better)
        if ($employee->experience_years && $employee->experience_years > 2) {
            $score -= 5;
        }
        
        // Performance rating bonus
        if (isset($employee->performance_rating) && $employee->performance_rating > 4) {
            $score -= 3;
        }
        
        // Shift preference match bonus
        $currentShift = $this->getCurrentShift();
        if ($employee->shift_preference === $currentShift) {
            $score -= 2;
        }
        
        return $score;
    }

    /**
     * Get shift schedule for branch
     */
    public function getShiftSchedule(Branch $branch, Carbon $date = null): array
    {
        if (!$date) {
            $date = Carbon::today();
        }

        $dayShift = $this->getAvailableStaff($branch, self::SHIFT_DAY);
        $eveningShift = $this->getAvailableStaff($branch, self::SHIFT_EVENING);
        $nightShift = $this->getAvailableStaff($branch, self::SHIFT_NIGHT);

        return [
            'date' => $date->format('Y-m-d'),
            'shifts' => [
                self::SHIFT_DAY => [
                    'hours' => '06:00 - 15:00',
                    'staff_count' => $dayShift->count(),
                    'staff' => $dayShift->pluck('name', 'id')->toArray()
                ],
                self::SHIFT_EVENING => [
                    'hours' => '15:00 - 22:00',
                    'staff_count' => $eveningShift->count(),
                    'staff' => $eveningShift->pluck('name', 'id')->toArray()
                ],
                self::SHIFT_NIGHT => [
                    'hours' => '22:00 - 06:00',
                    'staff_count' => $nightShift->count(),
                    'staff' => $nightShift->pluck('name', 'id')->toArray()
                ]
            ]
        ];
    }

    /**
     * Get staff workload distribution
     */
    public function getWorkloadDistribution(Branch $branch): array
    {
        $staff = $this->getAvailableStaff($branch);
        
        $distribution = $staff->map(function ($employee) {
            $activeOrders = Order::where('steward_id', $employee->id)
                ->whereIn('status', [Order::STATUS_SUBMITTED, Order::STATUS_PREPARING])
                ->count();

            $completedToday = Order::where('steward_id', $employee->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->whereDate('created_at', Carbon::today())
                ->count();

            return [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'shift' => $employee->shift_preference ?? 'flexible',
                'active_orders' => $activeOrders,
                'completed_today' => $completedToday,
                'load_percentage' => min(($activeOrders / 5) * 100, 100) // Assume max 5 concurrent orders
            ];
        });

        return [
            'distribution' => $distribution->toArray(),
            'summary' => [
                'total_staff' => $staff->count(),
                'average_load' => round($distribution->avg('load_percentage'), 1),
                'overloaded_staff' => $distribution->where('load_percentage', '>', 80)->count()
            ]
        ];
    }

    /**
     * Suggest optimal staff for upcoming shift
     */
    public function suggestStaffForShift(Branch $branch, string $shift, Carbon $date): array
    {
        $historicalData = $this->getHistoricalOrderData($branch, $shift, $date);
        $availableStaff = $this->getAvailableStaff($branch, $shift);
        
        // Calculate recommended staff count based on historical data
        $avgOrders = $historicalData['avg_orders_per_hour'] ?? 0;
        $recommendedStaffCount = max(2, ceil($avgOrders / 3)); // Minimum 2, 1 staff per 3 orders/hour

        return [
            'shift' => $shift,
            'date' => $date->format('Y-m-d'),
            'historical_data' => $historicalData,
            'recommended_staff_count' => $recommendedStaffCount,
            'available_staff_count' => $availableStaff->count(),
            'staff_shortage' => max(0, $recommendedStaffCount - $availableStaff->count()),
            'suggested_staff' => $availableStaff->take($recommendedStaffCount)->pluck('name', 'id')->toArray()
        ];
    }

    /**
     * Get historical order data for shift planning
     */
    protected function getHistoricalOrderData(Branch $branch, string $shift, Carbon $date): array
    {
        $dayOfWeek = $date->dayOfWeek;
        $startDate = $date->copy()->subWeeks(4); // Last 4 weeks of same day
        
        // Define shift hours
        $shiftHours = $this->getShiftHours($shift);
        
        $orders = Order::where('branch_id', $branch->id)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereDate('created_at', '>=', $startDate)
            ->whereTime('created_at', '>=', $shiftHours['start'])
            ->whereTime('created_at', '<=', $shiftHours['end'])
            ->whereRaw('DAYOFWEEK(created_at) = ?', [$dayOfWeek + 1]) // MySQL DAYOFWEEK is 1-indexed
            ->get();

        $avgOrdersPerHour = $orders->count() > 0 ? 
            round($orders->count() / (4 * $this->getShiftDurationHours($shift)), 1) : 0;

        return [
            'avg_orders_per_hour' => $avgOrdersPerHour,
            'total_orders_sampled' => $orders->count(),
            'peak_hour' => $this->findPeakHour($orders),
            'sample_period' => '4 weeks'
        ];
    }

    protected function getShiftHours(string $shift): array
    {
        switch ($shift) {
            case self::SHIFT_DAY:
                return ['start' => '06:00:00', 'end' => '15:00:00'];
            case self::SHIFT_EVENING:
                return ['start' => '15:00:00', 'end' => '22:00:00'];
            case self::SHIFT_NIGHT:
                return ['start' => '22:00:00', 'end' => '06:00:00'];
            default:
                return ['start' => '00:00:00', 'end' => '23:59:59'];
        }
    }

    protected function getShiftDurationHours(string $shift): int
    {
        switch ($shift) {
            case self::SHIFT_DAY:
            case self::SHIFT_EVENING:
                return 9;
            case self::SHIFT_NIGHT:
                return 8;
            default:
                return 24;
        }
    }

    protected function findPeakHour(Collection $orders): ?int
    {
        if ($orders->isEmpty()) {
            return null;
        }

        $hourlyDistribution = $orders->groupBy(function ($order) {
            return $order->created_at->hour;
        });

        return $hourlyDistribution->map->count()->sortDesc()->keys()->first();
    }
}
