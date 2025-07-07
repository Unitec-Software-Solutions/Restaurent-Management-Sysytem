<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class KotDish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'prep_time',
        'cook_time',
        'serving_size',
        'instructions',
        'image',
        'is_active',
        'is_menu_item',
        'allergen_info',
        'nutritional_info',
        'spice_level',
        'created_by',
        'updated_by',
        'organization_id',
        'branch_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_menu_item' => 'boolean',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'serving_size' => 'integer',
        'price' => 'decimal:2',
        'nutritional_info' => 'array',
        'allergen_info' => 'array'
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Recipe::class, 'kot_dish_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMenuItems($query)
    {
        return $query->where('is_menu_item', true);
    }

    public function scopeForOrganization($query, $orgId)
    {
        return $query->where('organization_id', $orgId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeSuperAdminAccess($query)
    {
        if (Auth::guard('admin')->user()?->is_super_admin) {
            return $query; // No filtering for super admin
        }
        
        return $query->where('organization_id', Auth::guard('admin')->user()?->organization_id);
    }

    // Accessor for total time
    public function getTotalTimeAttribute()
    {
        return $this->prep_time + $this->cook_time;
    }

    // Method to get total cost based on ingredients
    public function getTotalCostAttribute()
    {
        return $this->ingredients()->sum(function ($ingredient) {
            return $ingredient->quantity * optional($ingredient->itemMaster)->cost_price ?? 0;
        });
    }

    // Check if all ingredients are available for this dish
    public function checkIngredientAvailability(int $branchId, int $portionsNeeded = 1): bool
    {
        foreach ($this->ingredients as $ingredient) {
            if (!$ingredient->checkStockAvailability($branchId, $portionsNeeded)) {
                return false;
            }
        }
        return true;
    }

    // Get maximum portions possible based on ingredient availability
    public function getMaxPortionsPossible(int $branchId): int
    {
        $maxPortions = PHP_INT_MAX;
        
        foreach ($this->ingredients as $ingredient) {
            $portionsPossible = $ingredient->getMaxPortionsPossible($branchId);
            $maxPortions = min($maxPortions, $portionsPossible);
        }
        
        return $maxPortions === PHP_INT_MAX ? 0 : $maxPortions;
    }
}
