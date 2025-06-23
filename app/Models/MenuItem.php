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
        'menu_category_id',
        'name',
        'description',
        'price',
        'image_path',
        'is_available',
        'requires_preparation',
        'preparation_time',
        'station',
        'is_vegetarian',
        'contains_alcohol',
        'allergens',
        'is_active',
        'item_master_id',
        'organization_id',
        'estimated_cost',
        'profit_margin',
        'max_daily_quantity',
        'current_daily_quantity',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'requires_preparation' => 'boolean',
        'is_vegetarian' => 'boolean',
        'contains_alcohol' => 'boolean',
        'is_active' => 'boolean',
        'allergens' => 'array',
        'price' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'max_daily_quantity' => 'integer',
        'current_daily_quantity' => 'integer',
    ];

    protected $appends = ['availability_status', 'stock_percentage', 'stock_indicator'];

    /**
     * MenuItem belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /**
     * MenuItem belongs to a branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * MenuItem belongs to an organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * MenuItem has many recipes (ingredients)
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * MenuItem has many order items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * MenuItem belongs to many menus
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_menu_items')
                    ->withPivot(['is_available', 'special_price', 'display_order'])
                    ->withTimestamps();
    }

    /**
     * MenuItem available menus
     */
    public function availableMenus()
    {
        return $this->menus()->wherePivot('is_available', true);
    }

    /**
     * Check if item is available in active menu for branch
     */
    public function isAvailableInActiveMenu(int $branchId): bool
    {
        $activeMenu = Menu::getActiveMenuForBranch($branchId);
        
        if (!$activeMenu) {
            return false;
        }
        
        return $this->availableMenus()
                    ->where('menu_id', $activeMenu->id)
                    ->exists();
    }

    /**
     * Get availability with menu context
     */
    public function getMenuAvailability(int $branchId, int $quantity = 1): array
    {
        $baseAvailability = $this->checkAvailability($branchId, $quantity);
        
        // Add menu availability check
        $menuAvailable = $this->isAvailableInActiveMenu($branchId);
        
        return array_merge($baseAvailability, [
            'menu_available' => $menuAvailable,
            'overall_available' => $baseAvailability['available'] && $menuAvailable
        ]);
    }

    /**
     * Check real-time availability based on ingredients stock
     */
    public function checkAvailability(int $branchId, int $quantity = 1): array
    {
        // If no recipes defined, assume it's available (simple item)
        if ($this->recipes()->count() === 0) {
            return [
                'available' => $this->is_available && $this->is_active,
                'max_quantity' => $this->max_daily_quantity - $this->current_daily_quantity,
                'limiting_factor' => null,
                'missing_ingredients' => []
            ];
        }

        $maxPossible = PHP_INT_MAX;
        $limitingFactor = null;
        $missingIngredients = [];

        foreach ($this->recipes()->active()->get() as $recipe) {
            $maxFromThisIngredient = $recipe->getMaxPortionsPossible($branchId);
            
            if ($maxFromThisIngredient < $quantity) {
                $missingIngredients[] = [
                    'ingredient' => $recipe->ingredient->name,
                    'needed' => $recipe->actual_quantity_needed * $quantity,
                    'available' => $recipe->getCurrentStock($branchId),
                    'unit' => $recipe->unit
                ];
            }

            if ($maxFromThisIngredient < $maxPossible) {
                $maxPossible = $maxFromThisIngredient;
                $limitingFactor = $recipe->ingredient->name;
            }
        }

        // Consider daily quantity limits
        $dailyLimit = $this->max_daily_quantity - $this->current_daily_quantity;
        if ($dailyLimit < $maxPossible) {
            $maxPossible = $dailyLimit;
            $limitingFactor = 'Daily limit reached';
        }

        return [
            'available' => $maxPossible >= $quantity && $this->is_available && $this->is_active,
            'max_quantity' => max(0, $maxPossible),
            'limiting_factor' => $limitingFactor,
            'missing_ingredients' => $missingIngredients
        ];
    }

    /**
     * Get availability status attribute
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        if (!$this->is_available) return 'unavailable';
        
        $user = \Illuminate\Support\Facades\Auth::user();
        $branchId = $user && isset($user->branch_id) ? $user->branch_id : 1;
        $availability = $this->checkAvailability($branchId);
        
        if (!$availability['available']) return 'out_of_stock';
        if ($availability['max_quantity'] <= 5) return 'low_stock';
        
        return 'available';
    }

    /**
     * Get stock percentage for UI indicators
     */
    public function getStockPercentageAttribute(): int
    {
        if (!$this->max_daily_quantity) return 100;
        
        $user = \Illuminate\Support\Facades\Auth::user();
        $branchId = $user && isset($user->branch_id) ? $user->branch_id : 1;
        $availability = $this->checkAvailability($branchId);
        
        if ($this->max_daily_quantity <= 0) return 0;
        
        return min(100, max(0, round(($availability['max_quantity'] / $this->max_daily_quantity) * 100)));
    }

    /**
     * Get real-time stock indicator for UI
     */
    public function getStockIndicatorAttribute(): string
    {
        $status = $this->availability_status;
        
        switch($status) {
            case 'available':
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>';
            case 'low_stock':
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>';
            case 'out_of_stock':
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>';
            case 'unavailable':
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unavailable</span>';
            default:
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
        }
    }

    /**
     * Reserve ingredients for an order
     */
    public function reserveIngredients(int $branchId, int $quantity): bool
    {
        if (!$this->checkAvailability($branchId, $quantity)['available']) {
            return false;
        }

        // Implementation would go here for actual reservation
        // This could involve creating temporary "reserved" transactions
        
        return true;
    }

    /**
     * Consume ingredients for confirmed order
     */
    public function consumeIngredients(int $branchId, int $quantity): void
    {
        foreach ($this->recipes()->active()->get() as $recipe) {
            $totalQuantityNeeded = $recipe->actual_quantity_needed * $quantity;
            
            // Create consumption transaction
            ItemTransaction::create([
                'organization_id' => $this->organization_id,
                'branch_id' => $branchId,
                'inventory_item_id' => $recipe->ingredient_item_id,
                'transaction_type' => 'menu_consumption',
                'quantity' => -$totalQuantityNeeded, // Negative for consumption
                'cost_price' => $recipe->ingredient->buying_price ?? 0,
                'unit_price' => 0,
                'total_value' => 0,
                'notes' => "Consumed for menu item: {$this->name} (Qty: {$quantity})",
                'reference_type' => 'MenuItem',
                'reference_id' => $this->id,
            ]);
        }

        // Update daily quantity
        $this->increment('current_daily_quantity', $quantity);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get associated ItemMaster
     */
    public function itemMaster()
    {
        return $this->belongsTo(ItemMaster::class, 'item_master_id');
    }
}