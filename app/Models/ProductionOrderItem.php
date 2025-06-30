<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'item_id',
        'quantity_to_produce',
        'quantity_produced',
        'quantity_wasted',
        'notes'
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
