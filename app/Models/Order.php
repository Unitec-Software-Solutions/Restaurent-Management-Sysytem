<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Enums\OrderType;

class Order extends Model
{
    // Order Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment Status Constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    // Payment Method Constants
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_DIGITAL = 'digital';

    // Order Type Constants
    const TYPE_DINE_IN = 'dine_in';
    const TYPE_TAKEAWAY = 'takeaway';
    const TYPE_DELIVERY = 'delivery';
    const TYPE_TAKEAWAY_IN_CALL = 'takeaway_in_call';
    const TYPE_TAKEAWAY_ONLINE = 'takeaway_online';
    const TYPE_TAKEAWAY_WALKIN_SCHEDULED = 'takeaway_walkin_scheduled';
    const TYPE_TAKEAWAY_WALKIN_DEMAND = 'takeaway_walkin_demand';

    // Order States for State Machine
    const STATES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SUBMITTED,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED
    ];

    /**
     * Valid status transitions for order state machine
     * draft → pending → confirmed → completed/canceled
     */
    const VALID_STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [], // Terminal state
        self::STATUS_CANCELLED => []  // Terminal state
    ];

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_phone_fk',
        'customer_email',
        'order_type',
        'order_source',
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
        'reservation_required',
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
        'pickup_time',
        'confirmed_at',
        'prepared_at',
        'completed_at',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'special_instructions',
        'estimated_prep_time',
        'priority',
        'metadata'
    ];

    protected $casts = [
        'order_type' => OrderType::class,
        'order_date' => 'datetime',
        'requested_time' => 'datetime',
        'pickup_time' => 'datetime',
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
        'reservation_required' => 'boolean',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'estimated_prep_time' => 'integer',
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
            
            // Set reservation requirement based on order type
            if ($order->order_type) {
                // Convert string to enum if needed
                $orderTypeEnum = is_string($order->order_type) 
                    ? OrderType::tryFrom($order->order_type) 
                    : $order->order_type;
                    
                if ($orderTypeEnum) {
                    $order->reservation_required = $orderTypeEnum->requiresReservation();
                    
                    // Validate dine-in orders have reservations
                    if ($orderTypeEnum->isDineIn() && !$order->reservation_id) {
                        throw new \Exception('Reservation required for dine-in orders');
                    }
                }
            }
            
            // Link customer phone
            if ($order->customer_phone && !$order->customer_phone_fk) {
                $customer = Customer::findOrCreateByPhone($order->customer_phone, [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email
                ]);
                $order->customer_phone_fk = $customer->phone;
            }
        });

        static::updating(function ($order) {
            $order->mapCompatibilityFields();
            
            // Update customer info if phone changed
            if ($order->isDirty('customer_phone') && $order->customer_phone) {
                $customer = Customer::findOrCreateByPhone($order->customer_phone, [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email
                ]);
                $order->customer_phone_fk = $customer->phone;
            }
            
            // Validate order type changes
            if ($order->isDirty('order_type') && $order->order_type) {
                $orderTypeEnum = is_string($order->order_type) 
                    ? OrderType::tryFrom($order->order_type) 
                    : $order->order_type;
                    
                if ($orderTypeEnum && $orderTypeEnum->isDineIn() && !$order->reservation_id) {
                    throw new \Exception('Reservation required for dine-in orders');
                }
            }
        });

        static::saved(function ($order) {
            // Update customer statistics
            if ($order->customer_phone_fk) {
                $customer = Customer::find($order->customer_phone_fk);
                $customer?->updateStats();
            }
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
     * Alias for orderItems (for backward compatibility)
     */
    public function items()
    {
        return $this->orderItems();
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

    /**
     * Mass assignment protection as requested in refactoring
     * Protect critical fields from mass assignment
     */
    protected $guarded = ['id', 'status', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = self::VALID_STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Transition order status with validation
     */
    public function transitionToStatus(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;
        
        // Set timestamp based on status
        switch ($newStatus) {
            case self::STATUS_CONFIRMED:
                $this->confirmed_at = now();
                break;
            case self::STATUS_PREPARING:
                $this->prepared_at = now();
                break;
            case self::STATUS_COMPLETED:
                $this->completed_at = now();
                break;
        }

        return $this->save();
    }

    /**
     * Scope for branch-specific orders (admin context)
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for admin user's accessible orders
     */
    public function scopeForAdmin($query, $admin)
    {
        if ($admin->is_super_admin) {
            return $query; // Super admin can see all
        }
        
        if ($admin->branch_id) {
            return $query->where('branch_id', $admin->branch_id);
        }
        
        return $query->where('organization_id', $admin->organization_id);
    }

    /**
     * Check if this is a dine-in order
     */
    public function isDineIn(): bool
    {
        if (is_string($this->order_type)) {
            $orderType = OrderType::tryFrom($this->order_type);
            return $orderType ? $orderType->isDineIn() : str_starts_with($this->order_type, 'dine_in_');
        }
        
        return $this->order_type instanceof OrderType ? $this->order_type->isDineIn() : false;
    }

    /**
     * Check if this is a takeaway order
     */
    public function isTakeaway(): bool
    {
        if (is_string($this->order_type)) {
            $orderType = OrderType::tryFrom($this->order_type);
            return $orderType ? $orderType->isTakeaway() : str_starts_with($this->order_type, 'takeaway_');
        }
        
        return $this->order_type instanceof OrderType ? $this->order_type->isTakeaway() : false;
    }

    /**
     * Check if this is a scheduled order
     */
    public function isScheduled(): bool
    {
        if (is_string($this->order_type)) {
            $orderType = OrderType::tryFrom($this->order_type);
            return $orderType ? $orderType->isScheduled() : str_contains($this->order_type, 'scheduled');
        }
        
        return $this->order_type instanceof OrderType ? $this->order_type->isScheduled() : false;
    }

    /**
     * Get order type enum
     */
    public function getOrderTypeEnum(): ?OrderType
    {
        if (is_string($this->order_type)) {
            return OrderType::tryFrom($this->order_type);
        }
        
        return $this->order_type instanceof OrderType ? $this->order_type : null;
    }

    /**
     * Get display label for order type
     */
    public function getOrderTypeLabel(): string
    {
        $orderType = $this->getOrderTypeEnum();
        return $orderType ? $orderType->getLabel() : ucfirst(str_replace('_', ' ', (string) $this->order_type));
    }

    /**
     * Relationship to customer via phone
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_phone_fk', 'phone');
    }
}