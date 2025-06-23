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

    // Additional fillable fields for stock management
    protected $fillable = [
        'reservation_id',
        'branch_id',
        'menu_id',
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
        'steward_id',
        'order_date',
        'kot_generated',
        'bill_generated',
        'stock_deducted',
        'notes'
    ];

    protected $casts = [
        'order_time' => 'datetime',
        'order_date' => 'datetime',
        'kot_generated' => 'boolean',
        'bill_generated' => 'boolean',
        'stock_deducted' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Set order date if not provided
            if (!$order->order_date) {
                $order->order_date = now();
            }
            
            // Automatically set to 'submitted' when created
            $order->status = self::STATUS_SUBMITTED;
            
            // Set customer_name to null if empty or blank
            if (empty($order->customer_name) || trim($order->customer_name) === '') {
                $order->customer_name = null;
            }
        });
    }

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
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

    public function steward()
    {
        return $this->belongsTo(Employee::class, 'steward_id');
    }

    // New method that aliases steward to server for better naming
    public function server()
    {
        return $this->belongsTo(Employee::class, 'steward_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('order_type', $type);
    }

    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('order_date', 'desc');
    }

    // Status management methods
    public function markAsPreparing()
    {
        if ($this->status !== self::STATUS_SUBMITTED) {
            throw new \Exception('Only submitted orders can be marked as preparing');
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

    public function markAsCompleted()
    {
        if (!in_array($this->status, [self::STATUS_READY, self::STATUS_PREPARING])) {
            throw new \Exception('Order must be ready or preparing to be completed');
        }
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'bill_generated' => true
        ]);
    }

    public function cancel($reason = null)
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \Exception('Cannot cancel completed or already cancelled order');
        }
        
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason
        ]);
    }

    // Stock management methods
    public function generateKOT()
    {
        if ($this->kot_generated) {
            return false;
        }

        $this->update(['kot_generated' => true]);
        return true;
    }

    public function canDeductStock()
    {
        return !$this->stock_deducted && in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_PREPARING]);
    }

    public function deductStock()
    {
        if (!$this->canDeductStock()) {
            return false;
        }

        $this->update(['stock_deducted' => true]);
        return true;
    }

    // Helper methods
    public function calculateTotal()
    {
        $subtotal = $this->items->sum('total_price');
        $tax = $subtotal * 0.13; // 13% VAT
        $serviceCharge = $subtotal * 0.10; // 10% service charge
        $total = $subtotal + $tax + $serviceCharge - ($this->discount ?? 0);

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $serviceCharge,
            'total' => $total
        ]);

        return $total;
    }

    public function getOrderNumberAttribute()
    {
        return 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the order is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPreparing(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}