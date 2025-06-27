<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'user_id',
        'date',
        'status',
        'actual_start_time',
        'actual_end_time',
        'break_duration',
        'overtime_hours',
        'notes',
        'late_minutes',
        'early_departure_minutes',
        'replacement_user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'actual_start_time' => 'datetime:H:i:s',
        'actual_end_time' => 'datetime:H:i:s',
        'break_duration' => 'integer',
        'overtime_hours' => 'decimal:2',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer'
    ];

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_LATE = 'late';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the shift that owns the assignment.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user assigned to the shift.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the replacement user if applicable.
     */
    public function replacementUser()
    {
        return $this->belongsTo(User::class, 'replacement_user_id');
    }

    /**
     * Calculate actual hours worked.
     */
    public function getActualHoursWorkedAttribute()
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return 0;
        }

        $start = Carbon::createFromTimeString($this->actual_start_time);
        $end = Carbon::createFromTimeString($this->actual_end_time);
        
        // Handle overnight shifts
        if ($end < $start) {
            $end->addDay();
        }
        
        $totalMinutes = $start->diffInMinutes($end);
        $workMinutes = $totalMinutes - ($this->break_duration ?? 0);
        
        return round($workMinutes / 60, 2);
    }

    /**
     * Calculate pay for this shift.
     */
    public function calculatePay()
    {
        $hoursWorked = $this->actual_hours_worked;
        $hourlyRate = $this->user->hourly_rate ?? 0;
        $shiftMultiplier = $this->shift->hourly_multiplier ?? 1;
        
        $regularPay = $hoursWorked * $hourlyRate * $shiftMultiplier;
        $overtimePay = ($this->overtime_hours ?? 0) * $hourlyRate * 1.5;
        
        return $regularPay + $overtimePay;
    }

    /**
     * Check if the assignment is for today.
     */
    public function isToday()
    {
        return $this->date->isToday();
    }

    /**
     * Check if the assignment is in the past.
     */
    public function isPast()
    {
        return $this->date->isPast();
    }

    /**
     * Check if the assignment is upcoming.
     */
    public function isUpcoming()
    {
        return $this->date->isFuture();
    }

    /**
     * Scope to get assignments for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get assignments for a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get assignments for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get assignments for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->whereHas('shift', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }
}
