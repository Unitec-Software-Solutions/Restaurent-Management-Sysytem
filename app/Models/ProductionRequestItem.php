<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_request_master_id',
        'item_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_produced',
        'quantity_distributed',
        'notes'
    ];

    public function productionRequestMaster()
    {
        return $this->belongsTo(ProductionRequestMaster::class, 'production_request_master_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
