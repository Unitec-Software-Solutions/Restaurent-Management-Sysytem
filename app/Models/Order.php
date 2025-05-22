<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reservation_id',
        'branch_id',
        'customer_name',
        'customer_phone',
        'order_type',
        'status',
        'subtotal',
        'tax',
        'service_charge',
        'discount',
        'total',
        
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAmountDueAttribute()
    {
        // If you have a payments table, sum payments and subtract from total
        return $this->total - ($this->payments()->sum('amount') ?? 0);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withDefault([
            'name' => 'Deleted Reservation',
            'scheduled_time' => null
        ]);
    }
};