<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
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
        'item_code',
        'batch_no',
        'buying_price',
        'quantity',
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
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the purchase order that owns this item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    /**
     * Get the item details (if linked to items_master).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'item_code', 'item_code');
    }

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_RECEIVED = 'Received';
    const STATUS_PARTIAL = 'Partial';
    const STATUS_CANCELLED = 'Cancelled';

    // Helper methods
    public function isPending(): bool
    {
        return $this->po_status === self::STATUS_PENDING;
    }

    public function markAsReceived(): void
    {
        $this->update(['po_status' => self::STATUS_RECEIVED]);
    }
    public function grnItems()
    {
        return $this->hasMany(GrnItem::class, 'po_detail_id');
    }
}