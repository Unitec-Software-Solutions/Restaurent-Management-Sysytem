<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_order_id',
        'inventory_item_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'total_price',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the inventory item associated with this purchase order item.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Calculate pending quantity to be received
     */
    public function getPendingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }

    /**
     * Check if the item is fully received
     */
    public function getIsFullyReceivedAttribute()
    {
        return $this->pending_quantity <= 0;
    }

    /**
     * Update total price based on quantity and unit price
     */
    public function updateTotalPrice()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
        
        return $this;
    }
}