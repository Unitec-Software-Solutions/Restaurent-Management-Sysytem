<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable following UI/UX guidelines.
     */
    protected $fillable = [
        'menu_category_id',
        'organization_id',
        'branch_id',
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
    ];

    /**
     * Get the attributes that should be cast following UI/UX data types.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotion_price' => 'decimal:2',
            'promotion_start' => 'datetime',
            'promotion_end' => 'datetime',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'requires_preparation' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_spicy' => 'boolean',
            'contains_alcohol' => 'boolean',
            'is_active' => 'boolean',
            'allergens' => 'array',
            'preparation_time' => 'integer',
            'display_order' => 'integer',
            'calories' => 'integer',
        ];
    }

    /**
     * Relationships following UI/UX guidelines
     */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class);
    }

    /**
     * Scopes for UI filtering following UI/UX patterns
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
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

    public function scopeOrderedForDisplay($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    public function scopeOnPromotion($query)
    {
        return $query->whereNotNull('promotion_price')
                    ->where('promotion_start', '<=', now())
                    ->where('promotion_end', '>=', now());
    }

    /**
     * Accessors for UI display following UI/UX guidelines
     */
    protected function displayPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Check if item is on promotion
                if ($this->isOnPromotion()) {
                    return $this->promotion_price;
                }
                return $this->price;
            }
        );
    }

    protected function originalPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price
        );
    }

    protected function hasPromotion(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->isOnPromotion()
        );
    }

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->is_active) {
                    return [
                        'text' => 'Inactive',
                        'class' => 'bg-gray-100 text-gray-800'
                    ];
                }
                
                if (!$this->is_available) {
                    return [
                        'text' => 'Unavailable',
                        'class' => 'bg-red-100 text-red-800'
                    ];
                }
                
                if ($this->is_featured) {
                    return [
                        'text' => 'Featured',
                        'class' => 'bg-purple-100 text-purple-800'
                    ];
                }
                
                if ($this->isOnPromotion()) {
                    return [
                        'text' => 'On Sale',
                        'class' => 'bg-yellow-100 text-yellow-800'
                    ];
                }
                
                return [
                    'text' => 'Available',
                    'class' => 'bg-green-100 text-green-800'
                ];
            }
        );
    }

    protected function dietaryTags(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tags = [];
                
                if ($this->is_vegetarian) {
                    $tags[] = [
                        'text' => 'Vegetarian',
                        'class' => 'bg-green-100 text-green-700',
                        'icon' => 'fa-leaf'
                    ];
                }
                
                if ($this->is_spicy) {
                    $tags[] = [
                        'text' => 'Spicy',
                        'class' => 'bg-red-100 text-red-700',
                        'icon' => 'fa-pepper-hot'
                    ];
                }
                
                if ($this->contains_alcohol) {
                    $tags[] = [
                        'text' => 'Contains Alcohol',
                        'class' => 'bg-orange-100 text-orange-700',
                        'icon' => 'fa-wine-glass'
                    ];
                }
                
                return $tags;
            }
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image_path) {
                    return asset('storage/' . $this->image_path);
                }
                
                // Generate placeholder image following UI/UX guidelines
                $categoryName = $this->menuCategory?->name ?? 'Menu Item';
                return "https://via.placeholder.com/400x300/6366f1/ffffff?text=" . urlencode($categoryName);
            }
        );
    }

    /**
     * Business logic methods following UI/UX guidelines
     */
    public function isOnPromotion(): bool
    {
        if (!$this->promotion_price || !$this->promotion_start || !$this->promotion_end) {
            return false;
        }
        
        $now = now();
        return $now->between($this->promotion_start, $this->promotion_end);
    }

    public function getDiscountPercentage(): float
    {
        if (!$this->isOnPromotion()) {
            return 0;
        }
        
        return round((($this->price - $this->promotion_price) / $this->price) * 100, 1);
    }

    public function canBeOrdered(): bool
    {
        return $this->is_active && $this->is_available;
    }

    public function getAllergensList(): array
    {
        return $this->allergens ?? [];
    }

    public function hasAllergen(string $allergen): bool
    {
        return in_array($allergen, $this->getAllergensList());
    }

    /**
     * UI helper methods following UI/UX guidelines
     */
    public function getFormattedPreparationTime(): string
    {
        if (!$this->preparation_time) {
            return 'N/A';
        }
        
        if ($this->preparation_time < 60) {
            return $this->preparation_time . ' min';
        }
        
        $hours = intval($this->preparation_time / 60);
        $minutes = $this->preparation_time % 60;
        
        if ($minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $hours . 'h';
    }

    public function getCaloriesDisplay(): string
    {
        if (!$this->calories) {
            return 'N/A';
        }
        
        return number_format($this->calories) . ' cal';
    }

    /**
     * Kitchen workflow methods
     */
    public function getKitchenStation(): string
    {
        return $this->station ?? 'kitchen';
    }

    public function requiresPreparation(): bool
    {
        return $this->requires_preparation ?? true;
    }
}