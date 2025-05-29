<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'grn_items';
    protected $primaryKey = 'grn_item_id';

    protected $fillable = [
        'grn_id',
        'po_detail_id',
        'item_code',
        'batch_no',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'buying_price',
        'line_total',
        'manufacturing_date',
        'expiry_date',
        'rejection_reason',
        'notes'
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'buying_price' => 'decimal:4',
        'line_total' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date'
    ];

    // Relationships
    public function grn()
    {
        return $this->belongsTo(GrnMaster::class, 'grn_id');
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'po_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_code', 'item_code');
    }
}