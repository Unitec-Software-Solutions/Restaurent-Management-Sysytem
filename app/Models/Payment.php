<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'payment_method',
        'status',
        'payment_reference',
        'is_active',
        'notes',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'amount', 
        'payment_method',
        'status',
        'transaction_id',
        'paid_at'
    ];

    /**
     * Get the parent payable model (Reservation or Order).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}

