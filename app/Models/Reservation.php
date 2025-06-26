<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'date',
        'start_time',
        'end_time',
        'number_of_people',
        'comments',
        'reservation_fee',
        'cancellation_fee',
        'status',
        'branch_id',
        'user_id',
        'steward_id',
        'check_in_time',
        'check_out_time',
        'send_notification',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->hasOneThrough(Organization::class, Branch::class, 'id', 'id', 'branch_id', 'organization_id');
    }

    /**
     * Get organization safely with null handling
     */
    public function getOrganizationAttribute()
    {
        return $this->branch?->organization;
    }

    /**
     * Check if reservation time conflicts with another reservation
     */
    public function conflictsWith(Reservation $other): bool
    {
        if ($this->branch_id !== $other->branch_id || $this->date !== $other->date) {
            return false;
        }

        $thisStart = Carbon::parse($this->date . ' ' . $this->start_time->format('H:i'));
        $thisEnd = Carbon::parse($this->date . ' ' . $this->end_time->format('H:i'));
        $otherStart = Carbon::parse($other->date . ' ' . $other->start_time->format('H:i'));
        $otherEnd = Carbon::parse($other->date . ' ' . $other->end_time->format('H:i'));

        // Add 15-minute buffer
        $bufferStart = $otherStart->copy()->subMinutes(15);
        $bufferEnd = $otherEnd->copy()->addMinutes(15);

        return $thisStart->lt($bufferEnd) && $thisEnd->gt($bufferStart);
    }

    /**
     * Check if reservation can be modified
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
               Carbon::parse($this->date . ' ' . $this->start_time->format('H:i'))->isFuture();
    }

    /**
     * Check if reservation can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'waitlisted']) &&
               Carbon::parse($this->date . ' ' . $this->start_time->format('H:i'))->isFuture();
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'reservation_tables');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('status', 'waitlisted');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForTimeSlot($query, $startTime, $endTime)
    {
        return $query->where(function($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
        });
    }
   
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function steward()
    {
        return $this->belongsTo(Employee::class, 'steward_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }
    
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}


