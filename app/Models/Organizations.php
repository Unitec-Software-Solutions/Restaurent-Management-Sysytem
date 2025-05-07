<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizations extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'trading_name',
        'registration_number',
        'email',
        'phone',
        'alternative_phone',
        'address',
        'website',
        'logo',
        'business_hours',
        'business_type',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'business_hours' => 'array', 
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
} 