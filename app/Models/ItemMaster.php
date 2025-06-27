<?php

namespace App\Models;

use App\Models\Organization;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'additional_notes',
        'description',
        'attributes',
    ];

    protected $casts = [
        'attributes'      => 'array',
        'is_perishable'   => 'boolean',
        'is_menu_item'    => 'boolean',
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
     * Production request items relationship
     */
    public function productionRequestItems()
    {
        return $this->hasMany(ProductionRequestItem::class, 'item_id');
    }

    /**
     * Production order items relationship
     */
    public function productionOrderItems()
    {
        return $this->hasMany(ProductionOrderItem::class, 'item_id');
    }

    /**
     * Production recipes where this item is the production item
     */
    public function productionRecipes()
    {
        return $this->hasMany(Recipe::class, 'production_item_id');
    }

    /**
     * Production recipe details where this item is a raw material
     */
    public function rawMaterialRecipes()
    {
        return $this->hasMany(RecipeDetail::class, 'raw_material_item_id');
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

    /**
     * Scope to get production items only
     */
    public function scopeProductionItems($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('name', 'Production Items');
        });
    }

    // New scope for raw materials
    public function scopeRawMaterials($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('name', 'Raw Materials');
        });
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

}
