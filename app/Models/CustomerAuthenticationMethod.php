<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAuthenticationMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_profile_id',
        'provider',
        'provider_id',
        'email',
        'phone_number',
        'password',
        'is_verified',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    // public function customerProfile()
    // {
    //     return $this->belongsTo(CustomerProfile::class);
    // }

    // public function isEmailVerified()
    // {
    //     return !is_null($this->email_verified_at);
    // }

    // public function isPhoneVerified()
    // {
    //     return !is_null($this->phone_verified_at);
    // }
} 