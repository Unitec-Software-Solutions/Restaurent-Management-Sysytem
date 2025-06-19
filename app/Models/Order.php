<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    // Order types from database check constraint
    const TYPE_TAKEAWAY_IN_CALL = 'takeaway_in_call_scheduled';
    const TYPE_TAKEAWAY_ONLINE = 'takeaway_online_scheduled';
    const TYPE_TAKEAWAY_WALKIN_SCHEDULED = 'takeaway_walk_in_scheduled';
    const TYPE_TAKEAWAY_WALKIN_DEMAND = 'takeaway_walk_in_demand';
    const TYPE_DINEIN_ONLINE = 'dine_in_online_scheduled';
    const TYPE_DINEIN_INCALL = 'dine_in_in_call_scheduled';
    const TYPE_DINEIN_WALKIN_SCHEDULED = 'dine_in_walk_in_scheduled';
    const TYPE_DINEIN_WALKIN_DEMAND = 'dine_in_walk_in_demand';

    // Statuses from database check constraint
    const STATUS_ACTIVE = 'active';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $attributes = [
        'customer_name' => 'Not Provided',
        'customer_phone' => 'Not Provided',
        'status' => self::STATUS_ACTIVE,
        'order_type' => self::TYPE_TAKEAWAY_ONLINE,
    ];

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
        'placed_by_admin',
    ];

    protected $casts = ['order_time' => 'datetime'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Automatically set to 'submitted' when created
            $order->status = self::STATUS_SUBMITTED;
            // Set customer_name to null if empty or blank
            if (empty($order->customer_name) || trim($order->customer_name) === '') {
                $order->customer_name = null;
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

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markAsPreparing()
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            throw new \Exception('Only active orders can be marked as preparing');
        }
        $this->update([
            'status' => self::STATUS_PREPARING,
            'preparation_started_at' => now()
        ]);
    }

    public function markAsReady()
    {
        if ($this->status !== self::STATUS_PREPARING) {
            throw new \Exception('Only preparing orders can be marked as ready');
        }
        $this->update([
            'status' => self::STATUS_READY,
            'ready_at' => now()
        ]);
    }

    /**
     * Check if the order is submitted.
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}