<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use App\Enums\ReservationType;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'customer_phone_fk',
        'email',
        'type',
        'table_size',
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
        'created_by_admin_id', 
        'assigned_table_ids', 
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'type' => \App\Enums\ReservationType::class,
        'assigned_table_ids' => 'array', // Cast JSON to array
    ];

    /**
     * Boot method to handle reservation creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reservation) {
            // Set default reservation type if not provided
            if (!$reservation->type) {
                $reservation->type = ReservationType::ONLINE;
            }
            
            // Apply reservation fee based on type
            if (!$reservation->reservation_fee && $reservation->branch_id) {
                $reservation->reservation_fee = RestaurantConfig::getReservationFee(
                    $reservation->type->value, 
                    $reservation->branch_id,
                    $reservation->branch?->organization_id
                );
            }
            
            // Link customer phone
            if ($reservation->phone && !$reservation->customer_phone_fk) {
                $customer = Customer::findOrCreateByPhone($reservation->phone, [
                    'name' => $reservation->name,
                    'email' => $reservation->email
                ]);
                $reservation->customer_phone_fk = $customer->phone;
            }
        });
        
        static::updating(function ($reservation) {
            // Update customer info if phone changed
            if ($reservation->isDirty('phone') && $reservation->phone) {
                $customer = Customer::findOrCreateByPhone($reservation->phone, [
                    'name' => $reservation->name,
                    'email' => $reservation->email
                ]);
                $reservation->customer_phone_fk = $customer->phone;
            }
        });
    }

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
        return in_array($this->status, ['pending', 'confirmed']) &&
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

    /**
     * Relationship to the admin who created this reservation
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
    
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Relationship to customer via phone
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_phone_fk', 'phone');
    }

    /**
     * Check if reservation is cancelled late (subject to cancellation fee)
     */
    public function isCancelledLate(): bool
    {
        if ($this->status !== 'cancelled') {
            return false;
        }
        
        $reservationTime = Carbon::parse($this->date . ' ' . $this->start_time->format('H:i'));
        $config = RestaurantConfig::getCancellationFeeRules($this->branch_id, $this->branch?->organization_id);
        
        return now()->diffInHours($reservationTime) < ($config['hours_before'] ?? 24);
    }

    /**
     * Apply cancellation fee if applicable
     */
    public function chargeCancellationFee(): void
    {
        if ($this->isCancelledLate()) {
            $fee = RestaurantConfig::calculateCancellationFee($this);
            $this->update(['cancellation_fee' => $fee]);
            
            // Here you would integrate with payment service
            // PaymentService::charge($this->customer, $fee);
        }
    }

    /**
     * Get reservation type label
     */
    public function getTypeLabel(): string
    {
        return $this->type ? $this->type->getLabel() : 'Unknown';
    }

    /**
     * Generate reservation summary for confirmation
     */
    public function getReservationSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'date' => $this->date->format('Y-m-d'),
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'number_of_people' => $this->number_of_people,
            'type' => $this->getTypeLabel(),
            'reservation_fee' => $this->reservation_fee,
            'status' => $this->status,
            'table_size' => $this->table_size,
            'tables_assigned' => $this->assigned_table_ids ?? [],
        ];
    }

    /**
     * Confirm reservation and prepare for order workflow
     */
    public function confirmReservation(): bool
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending reservations can be confirmed');
        }

        $this->status = 'confirmed';
        $this->save();

        // Send confirmation notification if enabled
        if ($this->send_notification) {
            // NotificationService::sendReservationConfirmation($this);
        }

        return true;
    }

    /**
     * Check if reservation has any orders
     */
    public function hasOrders(): bool
    {
        return $this->orders()->exists();
    }

    /**
     * Get admin defaults for reservation creation
     */
    public static function getAdminDefaults($adminUser): array
    {
        $defaults = [
            'type' => ReservationType::WALK_IN,
            'date' => now()->addHours(1)->toDateString(),
            'start_time' => now()->addHours(1)->format('H:i'),
            'end_time' => now()->addHours(3)->format('H:i'),
            'status' => 'pending',
            'branch_id' => $adminUser->branch_id,
            'created_by_admin_id' => $adminUser->id,
            'send_notification' => false,
        ];

        // Get admin's default phone if configured
        $defaultPhone = RestaurantConfig::get('admin_default_phone', null, $adminUser->branch_id, $adminUser->organization_id);
        if ($defaultPhone) {
            $defaults['phone'] = $defaultPhone;
        }

        return $defaults;
    }

    /**
     * Arrange tables based on reservation size
     */
    public function arrangeTables(): array
    {
        $tableSize = $this->table_size ?? $this->number_of_people;
        
        // Get available tables that can accommodate the party size
        $availableTables = Table::where('branch_id', $this->branch_id)
            ->where('capacity', '>=', $tableSize)
            ->whereDoesntHave('reservations', function($query) {
                $query->forDate($this->date)
                      ->forTimeSlot($this->start_time, $this->end_time)
                      ->whereIn('status', ['confirmed', 'checked_in']);
            })
            ->orderBy('capacity')
            ->get();

        $selectedTables = [];
        $remainingSeats = $tableSize;

        foreach ($availableTables as $table) {
            if ($remainingSeats <= 0) break;
            
            $selectedTables[] = $table->id;
            $remainingSeats -= $table->capacity;
        }

        if ($remainingSeats > 0) {
            throw new \Exception('Not enough table capacity available for this reservation');
        }

        $this->assigned_table_ids = $selectedTables;
        $this->save();

        return $selectedTables;
    }
}


