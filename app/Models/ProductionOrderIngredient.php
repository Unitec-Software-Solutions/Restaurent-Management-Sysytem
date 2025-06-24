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

    protected $casts = [
        'planned_quantity' => 'decimal:3',
        'issued_quantity' => 'decimal:3',
        'consumed_quantity' => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'is_manually_added' => 'boolean'
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(ItemMaster::class, 'ingredient_item_id');
    }

    // Helper methods
    public function getRemainingToIssue()
    {
        return max(0, $this->planned_quantity - $this->issued_quantity);
    }

    public function getRemainingToConsume()
    {
        return max(0, $this->issued_quantity - $this->consumed_quantity - $this->returned_quantity);
    }

    public function getIssuanceProgress()
    {
        return $this->planned_quantity > 0 ? ($this->issued_quantity / $this->planned_quantity) * 100 : 0;
    }

    public function getConsumptionProgress()
    {
        return $this->issued_quantity > 0 ? ($this->consumed_quantity / $this->issued_quantity) * 100 : 0;
    }

    public function isFullyIssued()
    {
        return $this->issued_quantity >= $this->planned_quantity;
    }

    public function isFullyConsumed()
    {
        return ($this->consumed_quantity + $this->returned_quantity) >= $this->issued_quantity;
    }
}