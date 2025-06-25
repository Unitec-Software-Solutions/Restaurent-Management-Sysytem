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

    
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'kitchen_station_id',
        'image_path',
        'is_active',
        'is_vegetarian',
        'is_vegan',
        'contains_alcohol',
        'requires_preparation',
        'preparation_time',
        'display_order',
        'is_featured',
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
        'allergen_info' => 'array',
        'nutritional_info' => 'array'
    ];

    /**
     * Relationships following UI/UX guidelines
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
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