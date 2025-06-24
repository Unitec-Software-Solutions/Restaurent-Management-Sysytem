<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrderIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'ingredient_item_id',
        'planned_quantity',
        'issued_quantity',
        'consumed_quantity',
        'returned_quantity',
        'unit_of_measurement',
        'notes',
        'is_manually_added'
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(ItemMaster::class, 'ingredient_item_id');
    }

    /**
     * Get remaining quantity to issue
     */
    public function getRemainingToIssue()
    {
        return $this->planned_quantity - $this->issued_quantity;
    }

    /**
     * Get remaining quantity to consume
     */
    public function getRemainingToConsume()
    {
        return $this->issued_quantity - $this->consumed_quantity - $this->returned_quantity;
    }

    /**
     * Check if ingredient is fully issued
     */
    public function isFullyIssued()
    {
        return $this->issued_quantity >= $this->planned_quantity;
    }

    /**
     * Check if ingredient is fully consumed
     */
    public function isFullyConsumed()
    {
        return ($this->consumed_quantity + $this->returned_quantity) >= $this->issued_quantity;
    }
}