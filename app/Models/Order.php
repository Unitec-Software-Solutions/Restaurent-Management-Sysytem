<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'order_type',
        'status',
        'subtotal',
        'tax_amount',        
        'discount_amount',   
        'service_charge',
        'delivery_fee',
        'total_amount',      
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'notes',
        'order_date',
        'reservation_id',
        'branch_id',
        'organization_id',
        'table_id',
        'user_id',
        'created_by',
        'placed_by_admin',       
        'tax',              
        'discount',         
        'total',            
        'requested_time',
        'confirmed_at',
        'prepared_at',
        'completed_at',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'special_instructions',
        'metadata'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'requested_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'completed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        // Compatibility casts
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_by_admin' => 'boolean',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'metadata' => 'array'
    ];

    /**
     * Boot method to generate order number and handle field mapping
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
            
            // Handle field mapping for compatibility
            $order->mapCompatibilityFields();
        });
        
        static::updating(function ($order) {
            $order->mapCompatibilityFields();
        });
    }

    /**
     * Map compatibility fields to ensure database consistency
     */
    private function mapCompatibilityFields()
    {
        // Map new fields to legacy fields for database consistency
        if (!is_null($this->total) && is_null($this->total_amount)) {
            $this->total_amount = $this->total;
        } elseif (!is_null($this->total_amount) && is_null($this->total)) {
            $this->total = $this->total_amount;
        }
        
        if (!is_null($this->tax) && is_null($this->tax_amount)) {
            $this->tax_amount = $this->tax;
        } elseif (!is_null($this->tax_amount) && is_null($this->tax)) {
            $this->tax = $this->tax_amount;
        }
        
        if (!is_null($this->discount) && is_null($this->discount_amount)) {
            $this->discount_amount = $this->discount;
        } elseif (!is_null($this->discount_amount) && is_null($this->discount)) {
            $this->discount = $this->discount_amount;
        }
    }

    /**
     * Generate unique order number following UI/UX guidelines
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $branchId = str_pad($this->branch_id ?? '00', 2, '0', STR_PAD_LEFT);
        $date = now()->format('Ymd');
        
        // Get today's order count for this branch
        $todayCount = static::where('branch_id', $this->branch_id)
            ->whereDate('created_at', today())
            ->count() + 1;
        
        $sequence = str_pad($todayCount, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$branchId}{$date}{$sequence}";
    }

    /**
     * Relationships
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Calculate totals automatically with proper field mapping
     */
    public function calculateTotals()
    {
        $subtotal = $this->orderItems->sum('total_price');
        $this->subtotal = $subtotal;
        
        $tax = $subtotal * 0.10; // 10% tax
        $this->tax = $tax;
        $this->tax_amount = $tax;
        
        $serviceCharge = $subtotal * 0.05; // 5% service charge  
        $this->service_charge = $serviceCharge;
        
        $discount = $this->discount ?? $this->discount_amount ?? 0;
        $this->discount = $discount;
        $this->discount_amount = $discount;
        
        $deliveryFee = $this->delivery_fee ?? 0;
        
        $total = $subtotal + $tax + $serviceCharge + $deliveryFee - $discount;
        $this->total = $total;
        $this->total_amount = $total;
        
        return $this;
    }

    /**
     * Scopes for common queries
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'refunded']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Status badge for UI display following guidelines
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'preparing' => 'bg-orange-100 text-orange-800',
            'ready' => 'bg-green-100 text-green-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        $amount = $this->total_amount ?? $this->total ?? 0;
        return number_format($amount, 2);
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbolAttribute(): string
    {
        $symbols = [
            'USD' => '$',
            'LKR' => 'Rs.',
            'EUR' => '€',
            'GBP' => '£'
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }
}