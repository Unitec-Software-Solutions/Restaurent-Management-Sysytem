<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsTransferItem extends Model
{
    protected $table = 'gtn_items';
    protected $primaryKey = 'gtn_item_id';

    protected $fillable = [
        'gtn_id',
        'item_id',
        'item_code',
        'item_name',
        'batch_no',
        'expiry_date',
        'transfer_quantity',
        'received_quantity',
        'damaged_quantity',
        'transfer_price',
        'line_total',
        'notes',
    ];

    // Relationships

    public function goodsTransferNote()
    {
        return $this->belongsTo(GoodsTransferNote::class, 'gtn_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
