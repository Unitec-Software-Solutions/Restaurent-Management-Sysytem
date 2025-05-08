<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inventory_category_id',
        'name',
        'sku',
        'unit_of_measurement',
        'reorder_level',
        'is_perishable',
        'shelf_life_days',
        'expiry_date',
        'show_in_menu',
        'is_inactive',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reorder_level' => 'decimal:3',
        'is_perishable' => 'boolean',
        'is_inactive' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the inventory item.
     */
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    /**
     * Get the stock records for the inventory item.
     */
    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get the transactions for the inventory item.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Check if item is low in stock at a specific branch.
     */
    public function isLowStock($branchId)
    {
        $stock = $this->stocks()->where('branch_id', $branchId)->first();
        return $stock && $stock->current_quantity <= $this->reorder_level;
    }

    /**
     * Check if item is out of stock at a specific branch.
     */
    public function isOutOfStock($branchId)
    {
        $stock = $this->stocks()->where('branch_id', $branchId)->first();
        return !$stock || $stock->current_quantity <= 0;
    }

    /**
     * Get the current stock level at a specific branch.
     */
    public function getStockLevel($branchId)
    {
        $stock = $this->stocks()->where('branch_id', $branchId)->first();
        return $stock ? $stock->current_quantity : 0;
    }

    /**
     * Get the last purchase unit price.
     */
    public function getLastPurchasePrice($branchId = null)
    {
        $query = $this->transactions()
            ->whereIn('transaction_type', ['purchase'])
            ->whereNotNull('unit_price')
            ->orderBy('created_at', 'desc');
            
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $transaction = $query->first();
        return $transaction ? $transaction->unit_price : 0;
    }
} 