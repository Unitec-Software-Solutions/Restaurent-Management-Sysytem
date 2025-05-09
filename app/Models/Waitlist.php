<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waitlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'date',
        'preferred_time',
        'number_of_people',
        'comments',
        'branch_id',
        'user_id',
        'status',
        'notify_when_available'
    ];

    protected $casts = [
        'date' => 'date',
        'preferred_time' => 'datetime',
        'notify_when_available' => 'boolean'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
