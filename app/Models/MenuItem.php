<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    
    const TYPE_BUY_SELL = 1;  
    const TYPE_KOT = 2;       

    protected $fillable = [
        'organization_id',
        'branch_id',
        'menu_category_id',
        'item_master_id',
        'name',
        'description',
        'price',
        'promotion_price',
        'promotion_start',
        'promotion_end',
        'image_path',
        'display_order',
        'is_available',
        'is_featured',
        'requires_preparation',
        'preparation_time',
        'station',
        'is_vegetarian',
        'is_spicy',
        'contains_alcohol',
        'allergens',
        'calories',
        'ingredients',
        'is_active',
        'kitchen_station_id',
        'is_vegan',
        'allergen_info',
        'nutritional_info'
    ];

    /**
     * Get the attributes that should be cast following UI/UX data types.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'contains_alcohol' => 'boolean',
        'requires_preparation' => 'boolean',
        'is_featured' => 'boolean',
        'preparation_time' => 'integer',
        'display_order' => 'integer',
        'allergens' => 'array',
        'allergen_info' => 'array',
        'nutritional_info' => 'array'
    ];

    /**
     * Relationships following UI/UX guidelines
     */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'item_master_id');
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scopes for UI filtering following UI/UX patterns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }
}