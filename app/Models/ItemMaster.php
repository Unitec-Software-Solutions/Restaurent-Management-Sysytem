<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\PurchaseOrderItem;
use App\Models\GoodReceivedNoteItem;

class ItemMaster extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'type',
        'reorder_level',
        'organization_id',
        'branch_id',
        'attributes',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'reorder_level' => 'integer'
    ];

    /**
     * Get the organization associated with the item.
     */
    public function organization()
    {
        return $this->belongsTo(Organizations::class);
    }

    /**
     * Get the branch associated with the item.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the stock records for the item.
     */
    public function stocks()
    {
        return $this->hasMany(InventoryStock::class, 'item_id');
    }

    /**
     * Get the transactions for the item.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    /**
     * Get the purchase order items for this item.
     */
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }

    /**
     * Get the GRN items for this item.
     */
    public function grnItems()
    {
        return $this->hasMany(GoodReceivedNoteItem::class, 'item_id');
    }

    /**
     * Get the category of the item.
     */
    public function getCategory()
    {
        return $this->attributes['attributes']['category'] ?? null;
    }

    /**
     * Get the supplier ID of the item.
     */
    public function getSupplierId()
    {
        return $this->attributes['attributes']['supplier_id'] ?? null;
    }

    /**
     * Get the unit of measurement for the item.
     */
    public function getUnit()
    {
        return $this->attributes['attributes']['unit'] ?? 'unit';
    }

    /**
     * Get the buy price of the item.
     */
    public function getBuyPrice()
    {
        return $this->attributes['attributes']['buy_price'] ?? 0;
    }

    /**
     * Get the sell price of the item.
     */
    public function getSellPrice()
    {
        return $this->attributes['attributes']['sell_price'] ?? 0;
    }

    /**
     * Get the name in a specific language.
     */
    public function getTranslatedName($language = 'en')
    {
        if ($language === 'en') {
            return $this->name;
        }
        
        return $this->attributes['attributes']['name_translations'][$language] ?? $this->name;
    }

    /**
     * Check if item is a food item.
     */
    public function isFood()
    {
        return $this->type === 'food';
    }

    /**
     * Check if item is an inventory item.
     */
    public function isInventory()
    {
        return $this->type === 'inventory';
    }

    /**
     * Check if item is perishable.
     */
    public function isPerishable()
    {
        return $this->isFood() && isset($this->attributes['attributes']['shelf_life']);
    }

    /**
     * Check if item is a prepared item.
     */
    public function isPrepared()
    {
        return $this->isFood() && 
               ($this->attributes['attributes']['is_prepared'] ?? false);
    }

    /**
     * Check if item is a beverage.
     */
    public function isBeverage()
    {
        return $this->isFood() && 
               ($this->attributes['attributes']['is_beverage'] ?? false);
    }

    /**
     * Check if item is an ingredient.
     */
    public function isIngredient()
    {
        return $this->isFood() && 
               ($this->attributes['attributes']['is_ingredient'] ?? false);
    }

    /**
     * Get the shelf life of the item.
     */
    public function getShelfLife()
    {
        return $this->attributes['attributes']['shelf_life'] ?? null;
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
     * Get the last purchase price for this item at a specific branch.
     */
    public function getLastPurchasePrice($branchId)
    {
        $transaction = $this->transactions()
            ->where('branch_id', $branchId)
            ->where('transaction_type', 'purchase')
            ->latest()
            ->first();

        return $transaction ? $transaction->unit_price : $this->getBuyPrice();
    }

    /**
     * Scope a query to only include food items.
     */
    public function scopeFood($query)
    {
        return $query->where('type', 'food');
    }

    /**
     * Scope a query to only include inventory items.
     */
    public function scopeInventory($query)
    {
        return $query->where('type', 'inventory');
    }

    /**
     * Scope a query to only include other items.
     */
    public function scopeOther($query)
    {
        return $query->where('type', 'other');
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include items for a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope a query to only include items for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}