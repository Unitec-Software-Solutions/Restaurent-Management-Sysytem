<?php

namespace App\Models;

use App\Models\Organizations;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMaster extends Model
{
    use SoftDeletes;

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
        return $this->belongsTo(Organizations::class, 'organization_id');
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
        return asset('https://placehold.co/200x200/png');
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

}
