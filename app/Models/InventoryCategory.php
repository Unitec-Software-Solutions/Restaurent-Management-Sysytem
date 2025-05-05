<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the inventory items for the category.
     */
    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'inventory_category_id');
    }

    /**
     * Get the total stock value for this category.
     */
    public function getTotalStockValue($branchId)
    {
        $totalValue = 0;
        
        foreach ($this->items as $item) {
            $stock = $item->getStockLevel($branchId);
            $price = $item->getLastPurchasePrice($branchId);
            $totalValue += $stock * $price;
        }
        
        return $totalValue;
    }
} 