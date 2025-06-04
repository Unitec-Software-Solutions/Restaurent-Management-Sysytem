<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_RECEIVED = 'Received';
    const STATUS_PARTIAL = 'Partial';
    const STATUS_CANCELLED = 'Cancelled';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'po_details';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'po_detail_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'po_id',
        'item_id',  // Changed from item_code
        'batch_no',
        'buying_price',
        'previous_buying_price', // Added
        'quantity',
        'quantity_received',
        'line_total',
        'po_status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'buying_price' => 'decimal:4',
        'quantity' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'line_total' => 'decimal:2',
        'previous_buying_price' => 'decimal:4',
    ];

    public static $rules = [
        'item_id' => 'required|exists:item_master,id',
        'buying_price' => 'required|numeric|min:0',
        'quantity' => 'required|numeric|min:0.01'
    ];

    // Add this to track which fields should be treated as dates
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Relationships
    /**
     * Get the purchase order that owns this item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if ($model->item) {
            $model->previous_buying_price = $model->item->buying_price;
        }
    });

    static::created(function ($model) {
        // Update item master with new price if needed
        $model->item->update([
            'buying_price' => $model->buying_price
        ]);
    });
}

    /**
     * Get the item details (if linked to items_master).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class);
    }

    // Helper to get item name
    public function getItemNameAttribute(): string
    {
        return $this->item->name ?? '';
    }

    // Helper to get previous price
    public function getPriceDifferenceAttribute(): float
    {
        return $this->buying_price - ($this->previous_buying_price ?? 0);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'po_detail_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('po_status', self::STATUS_PENDING);
    }

    public function scopeReceived($query)
    {
        return $query->where('po_status', self::STATUS_RECEIVED);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->po_status === self::STATUS_PENDING;
    }

    public function isReceived(): bool
    {
        return $this->po_status === self::STATUS_RECEIVED;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->po_status === self::STATUS_PARTIAL;
    }

    public function markAsReceived(): void
    {
        $this->update([
            'po_status' => $this->quantity_received >= $this->quantity ?
                self::STATUS_RECEIVED : self::STATUS_PARTIAL
        ]);
    }

    // Quantity helpers
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - $this->quantity_received;
    }

    public function hasRemainingQuantity(): bool
    {
        return $this->remaining_quantity > 0;
    }

    public function updateReceivedQuantity(float $received): void
    {
        $this->update(['quantity_received' => $received]);
        $this->markAsReceived();
    }
}
