<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_profile_id',
        'dietary_restrictions',
        'favorite_dishes',
        'allergies',
        'preferred_language',
        'email_notifications',
        'sms_notifications',
    ];

    protected $casts = [
        'dietary_restrictions' => 'array',
        'favorite_dishes' => 'array',
        'allergies' => 'array',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
    ];

    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }
} 