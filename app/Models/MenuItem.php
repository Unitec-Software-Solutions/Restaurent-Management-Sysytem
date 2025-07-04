<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    // Menu Item Types
    const TYPE_BUY_SELL = 1;  
    const TYPE_KOT = 2;      
    
    // Spice Levels
    const SPICE_MILD = 'mild';
    const SPICE_MEDIUM = 'medium';
    const SPICE_HOT = 'hot';
    const SPICE_VERY_HOT = 'very_hot';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'menu_category_id',
        'item_master_id',
        'name',
        'unicode_name',
        'description',
        'item_code',
        'price',
        'cost_price',
        'currency',
        'promotion_price',
        'promotion_start',
        'promotion_end',
        'image_path',
        'image_url',
        'display_order',
        'sort_order',
        'is_available',
        'is_active',
        'is_featured',
        'requires_preparation',
        'preparation_time',
        'station',
        'kitchen_station_id',
        'is_vegetarian',
        'is_vegan',
        'is_spicy',
        'spice_level',
        'contains_alcohol',
        'calories',
        'allergens',
        'allergen_info',
        'nutritional_info',
        'ingredients',
        'type',
        'special_instructions',
        'customization_options',
        'notes'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'promotion_price' => 'decimal:2',
        'promotion_start' => 'datetime',
        'promotion_end' => 'datetime',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_spicy' => 'boolean',
        'contains_alcohol' => 'boolean',
        'requires_preparation' => 'boolean',
        'is_featured' => 'boolean',
        'preparation_time' => 'integer',
        'display_order' => 'integer',
        'sort_order' => 'integer',
        'calories' => 'integer',
        'type' => 'integer',
        'allergens' => 'array',
        'allergen_info' => 'array',
        'nutritional_info' => 'array',
        'customization_options' => 'array'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'is_active' => true,
        'is_available' => true,
        'is_featured' => false,
        'requires_preparation' => true,
        'type' => self::TYPE_KOT,
        'currency' => 'LKR',
        'spice_level' => self::SPICE_MILD
    ];

    /**
     * Relationships
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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

    public function kotItems(): HasMany
    {
        return $this->hasMany(KotItem::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_menu_items')
                    ->withPivot(['is_available', 'override_price', 'sort_order', 'special_notes', 'available_from', 'available_until'])
                    ->withTimestamps();
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Scopes for filtering
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }

    public function scopeVegan($query)
    {
        return $query->where('is_vegan', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId === null || $categoryId === 'null') {
            return $query->whereNull('menu_category_id');
        }
        return $query->where('menu_category_id', $categoryId);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRequiresPreparation($query)
    {
        return $query->where('requires_preparation', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeKotItems($query)
    {
        return $query->where('type', self::TYPE_KOT);
    }

    public function scopeBuyAndSellItems($query)
    {
        return $query->where('type', self::TYPE_BUY_SELL);
    }

    public function scopeUncategorized($query)
    {
        return $query->whereNull('menu_category_id');
    }

    /**
     * Accessor for current price (considering promotions)
     */
    public function getCurrentPriceAttribute()
    {
        if ($this->promotion_price && 
            $this->promotion_start && 
            $this->promotion_end &&
            now()->between($this->promotion_start, $this->promotion_end)) {
            return $this->promotion_price;
        }
        
        return $this->price;
    }

    /**
     * Check if item is on promotion
     */
    public function getIsOnPromotionAttribute()
    {
        return $this->promotion_price && 
               $this->promotion_start && 
               $this->promotion_end &&
               now()->between($this->promotion_start, $this->promotion_end);
    }

    /**
     * Get spice level options
     */
    public static function getSpiceLevels()
    {
        return [
            self::SPICE_MILD => 'Mild',
            self::SPICE_MEDIUM => 'Medium',
            self::SPICE_HOT => 'Hot',
            self::SPICE_VERY_HOT => 'Very Hot'
        ];
    }

    /**
     * Get type options
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BUY_SELL => 'Buy & Sell (Direct)',
            self::TYPE_KOT => 'Kitchen Order Ticket'
        ];
    }
}