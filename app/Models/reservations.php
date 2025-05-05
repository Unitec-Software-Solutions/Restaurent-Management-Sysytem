<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reservations extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'phone_number',
        'organization_id',
        'branch_id',
        'reservation_date',
        'reservation_time',
        'party_size',
        'special_requests',
        'status', // 'confirmed', 'pending', 'canceled', 'completed'
        'reservation_fee',
        'cancellation_fee',
        'is_waitlisted',
        'notification_preference', // 'email', 'sms', 'both'
        'reservation_type', // 'online', 'in-call', 'walk-in'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime',
        'is_waitlisted' => 'boolean',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'reservation_tables')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reservationHistory()
    {
        return $this->hasMany(ReservationHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'pending']);
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('is_waitlisted', true);
    }

    // Methods
    public function cancel()
    {
        $this->status = 'canceled';
        $this->save();

        // Log the cancellation
        $this->reservationHistory()->create([
            'action' => 'canceled',
            'notes' => 'Reservation canceled by customer',
            'action_by' => auth()->id() ?? null,
        ]);

        // Calculate cancellation fee based on restaurant policies
        // This would be implemented based on specific business rules
        $this->calculateCancellationFee();

        return $this;
    }

    public function calculateCancellationFee()
    {
        // This would be implemented based on specific business rules
        // For example, different fees based on how close to the reservation time
        // the cancellation occurs
        
        // Example logic:
        $hoursDifference = now()->diffInHours($this->reservation_time);
        
        if ($hoursDifference < 24) {
            // Less than 24 hours notice - higher fee
            $this->cancellation_fee = $this->branch->cancellation_fee_high;
        } elseif ($hoursDifference < 48) {
            // Less than 48 hours notice - medium fee
            $this->cancellation_fee = $this->branch->cancellation_fee_medium;
        } else {
            // More than 48 hours notice - low or no fee
            $this->cancellation_fee = $this->branch->cancellation_fee_low;
        }
        
        $this->save();
    }
}


