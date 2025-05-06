<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'credentials',
        'is_active',
        'is_test_mode',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    public function getCredentialsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = json_encode($value);
    }
} 