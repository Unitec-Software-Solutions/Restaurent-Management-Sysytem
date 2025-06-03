<?php

namespace App\Models;

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
