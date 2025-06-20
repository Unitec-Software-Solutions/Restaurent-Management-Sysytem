<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KitchenStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_id',
        'is_active',
        'station_type',
        'max_concurrent_orders',
        'current_load'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent_orders' => 'integer',
        'current_load' => 'integer'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function kots()
    {
        return $this->hasMany(Kot::class, 'station_id');
    }

    public function activeKots()
    {
        return $this->hasMany(Kot::class, 'station_id')
                    ->whereIn('status', [Kot::STATUS_PENDING, Kot::STATUS_PREPARING]);
    }

    // Helper methods
    public function isOverloaded()
    {
        return $this->current_load >= $this->max_concurrent_orders;
    }

    public function canAcceptOrder()
    {
        return $this->is_active && !$this->isOverloaded();
    }
}
