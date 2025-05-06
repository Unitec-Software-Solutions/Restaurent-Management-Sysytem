<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffTrainingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'trainer_id',
        'training_type',
        'description',
        'training_date',
        'is_completed',
        'notes',
    ];

    protected $casts = [
        'training_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function trainer()
    {
        return $this->belongsTo(StaffProfile::class, 'trainer_id');
    }
} 