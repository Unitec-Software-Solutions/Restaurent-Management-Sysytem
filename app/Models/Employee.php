<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emp_id',
        'name',
        'email',
        'phone',
        'role',
        'branch_id',
        'is_active',
        'joined_date',
        'address',
        'emergency_contact'
    ];

    protected $casts = [
        'joined_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}