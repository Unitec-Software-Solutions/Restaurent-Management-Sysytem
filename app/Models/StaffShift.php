<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'shift_id',
        'branch_id',
        'date',
        'is_training_mode',
        'clock_in',
        'clock_out',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_training_mode' => 'boolean',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isClockedIn()
    {
        return !is_null($this->clock_in) && is_null($this->clock_out);
    }

    public function isClockedOut()
    {
        return !is_null($this->clock_out);
    }
} 