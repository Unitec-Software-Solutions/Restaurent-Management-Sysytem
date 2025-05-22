<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'takeaway_id', // Make sure this is in your fillable if you want to mass assign
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (str_contains($order->order_type, 'takeaway')) {
                $order->takeaway_id = 'TA-' . strtoupper(Str::random(8));
            }

            if (!empty($order->placed_by_admin) && empty($order->customer_phone)) {
                $order->customer_phone = 'WALK-IN';
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withDefault([
            'name' => 'Deleted Reservation',
            'scheduled_time' => null
        ]);
    }
}