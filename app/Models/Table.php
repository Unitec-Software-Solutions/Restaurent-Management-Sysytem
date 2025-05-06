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
        'table_number',
        'capacity',
        'status',
        'location',
        'is_active',
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
        return $this->belongsToMany(Reservation::class, 'reservation_tables')
            ->withTimestamps();
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
} 