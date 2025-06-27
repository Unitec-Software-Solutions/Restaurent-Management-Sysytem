<?php

namespace App\Models;

use App\Models\Organization;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMaster extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'item_master';

    protected $fillable = [
        'name',
        'unicode_name',
        'item_category_id',
        'item_code',
        'unit_of_measurement',
        'reorder_level',
        'is_perishable',
        'shelf_life_in_days',
        'branch_id',
        'organization_id',
        'buying_price',
        'selling_price',
        'is_menu_item',
        'is_active',
        'additional_notes',
        'description',
        'attributes',
    ];

    protected $casts = [
        'attributes'      => 'array',
        'is_perishable'   => 'boolean',
        'is_menu_item'    => 'boolean',
        'is_active'       => 'boolean',
        'buying_price'    => 'decimal:2',
        'selling_price'   => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Define the transactions relationship
     */
    public function transactions()
    {
        return $this->hasMany(ItemTransaction::class, 'inventory_item_id');
    }

    /**
     * Accessor Example: Get Ingredients if available in attributes
     */
    public function getIngredientsAttribute()
    {
        return $this->attributes['attributes']['ingredients'] ?? null;
    }

    /**
     * Accessor Example: Get Image if available in attributes
     */
    public function getImageUrlAttribute()
    {
        if (isset($this->attributes['attributes']['img'])) {
            return asset('storage/'.$this->attributes['attributes']['img']);
        }
        return asset('storage/default.png'); 
    }


    /**
     * Scope: Active Items Only (not soft deleted)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // New scope for menu items
    public function scopeMenuItem($query)
    {
        return $query->where('is_menu_item', true);
    }

    // New scope for perishable items
    public function scopePerishable($query)
    {
        return $query->where('is_perishable', true);
    }

    // In ItemMaster model
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }

    public function latestPurchaseOrderItem()
    {
        return $this->hasOne(PurchaseOrderItem::class, 'item_id')
            ->latest();
    }

    /**
     * Get current stock level for this item at a specific branch
     */
    public function getStockLevel(int $branchId): float
    {
        return $this->transactions()
            ->where('branch_id', $branchId)
            ->sum('quantity');
    }

    /**
     * Check if item is low stock
     */
    public function isLowStock(int $branchId): bool
    {
        $currentStock = $this->getStockLevel($branchId);
        return $currentStock <= $this->reorder_level;
    }

    /**
     * Check if item is out of stock
     */
    public function isOutOfStock(int $branchId): bool
    {
        $currentStock = $this->getStockLevel($branchId);
        return $currentStock <= 0;
    }

    /**
     * Get stock status for display
     */
    public function getStockStatus(int $branchId): string
    {
        $currentStock = $this->getStockLevel($branchId);
        
        if ($currentStock <= 0) return 'out_of_stock';
        if ($currentStock <= $this->reorder_level) return 'low_stock';
        if ($currentStock <= $this->reorder_level * 2) return 'medium_stock';
        return 'good_stock';
    }

    /**
     * Get stock percentage for UI indicators
     */
    public function getStockPercentage(int $branchId): int
    {
        $currentStock = $this->getStockLevel($branchId);
        $maxStock = $this->reorder_level * 5; // Assume max stock is 5x reorder level
        
        if ($maxStock <= 0) return 0;
        
        return min(100, max(0, round(($currentStock / $maxStock) * 100)));
    }

}
