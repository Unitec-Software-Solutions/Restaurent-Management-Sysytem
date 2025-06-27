<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_item_id',
        'ingredient_item_id',
        'quantity_needed',
        'unit',
        'waste_percentage',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'quantity_needed' => 'decimal:3',
        'waste_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Recipe belongs to a menu item
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Recipe uses an ingredient from inventory
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'ingredient_item_id');
    }

    /**
     * Calculate actual quantity needed including waste
     */
    public function getActualQuantityNeededAttribute(): float
    {
        $baseQuantity = $this->quantity_needed;
        $wasteMultiplier = 1 + ($this->waste_percentage / 100);
        
        return round($baseQuantity * $wasteMultiplier, 3);
    }

    /**
     * Check if enough stock is available for this recipe component
     */
    public function checkStockAvailability(int $branchId, int $portionsNeeded = 1): bool
    {
        $stock = $this->ingredient->getStockLevel($branchId);
        $requiredQuantity = $this->actual_quantity_needed * $portionsNeeded;
        
        return $stock >= $requiredQuantity;
    }

    /**
     * Get current stock level for this recipe component
     */
    public function getCurrentStock(int $branchId): float
    {
        return $this->ingredient->getStockLevel($branchId);
    }

    /**
     * Calculate how many portions can be made with current stock
     */
    public function getMaxPortionsPossible(int $branchId): int
    {
        $currentStock = $this->getCurrentStock($branchId);
        
        if ($this->actual_quantity_needed <= 0) {
            return 0;
        }
        
        return floor($currentStock / $this->actual_quantity_needed);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForMenuItem($query, int $menuItemId)
    {
        return $query->where('menu_item_id', $menuItemId);
    }

    public function scopeWithStock($query, int $branchId)
    {
        return $query->with(['ingredient' => function($q) use ($branchId) {
            $q->with(['transactions' => function($t) use ($branchId) {
                $t->where('branch_id', $branchId);
            }]);
        }]);
    }
}
