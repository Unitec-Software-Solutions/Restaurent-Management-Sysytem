<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryStock extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_stock';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'current_quantity',
        'committed_quantity',
        'available_quantity',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'current_quantity' => 'decimal:3',
        'committed_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch that owns the stock.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inventory item that owns the stock.
     */
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Check if item is low in stock
     */
    public function isLowStock()
    {
        return $this->item && $this->current_quantity <= $this->item->reorder_level;
    }

    /**
     * Check if item is out of stock
     */
    public function isOutOfStock()
    {
        return $this->current_quantity <= 0;
    }

    /**
     * Calculate the stock value
     */
    public function getStockValue()
    {
        $unitPrice = $this->item->getLastPurchasePrice($this->branch_id);
        return $this->current_quantity * $unitPrice;
    }

    /**
     * Update available quantity
     */
    public function updateAvailableQuantity()
    {
        $this->available_quantity = max(0, $this->current_quantity - $this->committed_quantity);
        $this->save();
        
        return $this;
    }

    /**
     * Commit stock for usage
     */
    public function commitStock($quantity)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Cannot commit more than available quantity.');
        }
        
        $this->committed_quantity += $quantity;
        $this->updateAvailableQuantity();
        
        return $this;
    }

    /**
     * Release committed stock
     */
    public function releaseCommittedStock($quantity)
    {
        if ($quantity > $this->committed_quantity) {
            throw new \Exception('Cannot release more than committed quantity.');
        }
        
        $this->committed_quantity -= $quantity;
        $this->updateAvailableQuantity();
        
        return $this;
    }

    /**
     * Add stock
     */
    public function addStock($quantity)
    {
        $this->current_quantity += $quantity;
        $this->updateAvailableQuantity();
        
        return $this;
    }

    /**
     * Deduct stock
     */
    public function deductStock($quantity)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Cannot deduct more than available quantity.');
        }
        
        $this->current_quantity -= $quantity;
        $this->updateAvailableQuantity();
        
        return $this;
    }
} 