<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceivedNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_received_note_id',
        'purchase_order_item_id',
        'inventory_item_id',
        'expected_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'rejection_reason',
        'unit_price',
        'total_price',
        'manufacturing_date',
        'expiry_date',
        'batch_number',
        'quality_checked',
        'quality_check_notes',
        'is_active',
    ];

    // Relationships
    public function goodReceivedNote()
    {
        return $this->belongsTo(GoodReceivedNote::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
