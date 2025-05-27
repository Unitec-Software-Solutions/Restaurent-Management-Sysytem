<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
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
}


