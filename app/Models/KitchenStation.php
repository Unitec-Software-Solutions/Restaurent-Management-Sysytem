<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KitchenStation extends Model
{
    use HasFactory;    protected $fillable = [
        'name',
        'branch_id',
        'type',
        'is_active',
        'order_priority',
        'printer_config',
        'notes',
        'max_concurrent_orders',
        'current_load'
    ];    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent_orders' => 'integer',
        'current_load' => 'integer',
        'order_priority' => 'integer',
        'printer_config' => 'array'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function kots()
    {
        return $this->hasMany(Kot::class, 'station_id');
    }

    public function activeKots()
    {
        return $this->hasMany(Kot::class, 'station_id')
                    ->whereIn('status', [Kot::STATUS_PENDING, Kot::STATUS_PREPARING]);
    }

    // Helper methods
    public function isOverloaded()
    {
        return $this->current_load >= $this->max_concurrent_orders;
    }

    public function canAcceptOrder()
    {
        return $this->is_active && !$this->isOverloaded();
    }

    /**
     * Scope for active stations only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific station type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get stations ordered by priority
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('order_priority');
    }

    /**
     * Check if station can handle specific menu item type
     */
    public function canHandle(string $menuItemType): bool
    {
        $handlingMap = [
            'cooking' => ['main_course', 'appetizer'],
            'prep' => ['salad', 'appetizer', 'dessert'],
            'beverage' => ['drink', 'cocktail', 'coffee'],
            'bar' => ['alcoholic', 'cocktail', 'beer', 'wine'],
            'grill' => ['grilled', 'bbq', 'meat'],
            'fry' => ['fried', 'deep_fried'],
            'dessert' => ['dessert', 'ice_cream', 'pastry']
        ];

        return in_array($menuItemType, $handlingMap[$this->type] ?? []);
    }

    /**
     * Get default stations configuration for branch type
     */
    public static function getDefaultStationsForBranchType(string $branchType): array
    {
        $configurations = [
            'restaurant' => [
                ['name' => 'Grill Station', 'type' => 'grill', 'order_priority' => 1],
                ['name' => 'Fry Station', 'type' => 'fry', 'order_priority' => 2],
                ['name' => 'Prep Station', 'type' => 'prep', 'order_priority' => 3],
                ['name' => 'Dessert Station', 'type' => 'dessert', 'order_priority' => 4],
                ['name' => 'Beverage Station', 'type' => 'beverage', 'order_priority' => 5],
            ],
            'cafe' => [
                ['name' => 'Coffee Station', 'type' => 'beverage', 'order_priority' => 1],
                ['name' => 'Kitchen Station', 'type' => 'cooking', 'order_priority' => 2],
                ['name' => 'Pastry Station', 'type' => 'dessert', 'order_priority' => 3],
            ],
            'pub' => [
                ['name' => 'Bar Station', 'type' => 'bar', 'order_priority' => 1],
                ['name' => 'Kitchen Station', 'type' => 'cooking', 'order_priority' => 2],
                ['name' => 'Grill Station', 'type' => 'grill', 'order_priority' => 3],
            ],
            'bar' => [
                ['name' => 'Main Bar', 'type' => 'bar', 'order_priority' => 1],
                ['name' => 'Cocktail Station', 'type' => 'beverage', 'order_priority' => 2],
                ['name' => 'Snack Prep', 'type' => 'prep', 'order_priority' => 3],
            ],
            'bakery' => [
                ['name' => 'Baking Station', 'type' => 'cooking', 'order_priority' => 1],
                ['name' => 'Decorating Station', 'type' => 'dessert', 'order_priority' => 2],
                ['name' => 'Prep Station', 'type' => 'prep', 'order_priority' => 3],
            ]
        ];

        return $configurations[$branchType] ?? $configurations['restaurant'];
    }
}
