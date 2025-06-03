<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'total_capacity',
        'reservation_fee',
        'cancellation_fee',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
    ];
    
    /**
     * Get the inventory transactions for the branch.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableCapacity($date, $startTime, $endTime)
    {
        $reservedCapacity = $this->reservations()
            ->where('date', $date)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->sum('number_of_people');

        return $this->total_capacity - $reservedCapacity;
    }

    public function getAvailableTables($date, $startTime, $endTime, $requiredCapacity)
    {
        return $this->tables()
            ->available($date, $startTime, $endTime)
            ->withCapacity($requiredCapacity)
            ->get();
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
    
} 