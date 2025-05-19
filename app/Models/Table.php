<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'number',
        'capacity',
        'status',
        'location',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isReserved()
    {
        return $this->status === 'reserved';
    }

    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    public function isMaintenance()
    {
        return $this->status === 'maintenance';
    }

    public function scopeAvailable($query, $date, $startTime, $endTime)
    {
        return $query->whereDoesntHave('reservations', function($q) use ($date, $startTime, $endTime) {
            $q->where('date', $date)
                ->where(function($q) use ($startTime, $endTime) {
                    $q->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->where('status', '!=', 'cancelled');
        });
    }

    public function scopeWithCapacity($query, $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }
} 