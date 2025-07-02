<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenStation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Valid station types for PostgreSQL constraint
     */
    public const VALID_TYPES = [
        'cooking',
        'preparation',
        'prep',
        'beverage',
        'dessert',
        'grill',
        'grilling',
        'fry',
        'bar',
        'pastry',
        'salad',
        'cold_kitchen',
        'hot_kitchen',
        'expo',
        'service'
    ];

    protected $fillable = [
        'branch_id',
        'organization_id',
        'name',
        'code',
        'type',
        'station_type',
        'description',
        'is_active',
        'order_priority',
        'priority_level',
        'max_concurrent_orders',
        'current_orders',
        'max_capacity',
        'printer_config',
        'settings',
        'equipment_list',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent_orders' => 'integer',
        'current_orders' => 'integer',
        'order_priority' => 'integer',
        'priority_level' => 'integer',
        'max_capacity' => 'decimal:2',
        'settings' => 'array',
        'printer_config' => 'array',
        'equipment_list' => 'array'
    ];

    protected $attributes = [
        'is_active' => true,
        'max_concurrent_orders' => 5,
        'current_orders' => 0,
        'order_priority' => 1,
        'priority_level' => 1,
        'type' => 'cooking',
        'station_type' => 'standard'
    ];

    /**
     * Boot the model for Laravel + PostgreSQL + Tailwind CSS
     */
    protected static function boot()
    {
        parent::boot();
        
        // Validate type before saving
        static::saving(function ($station) {
            if (!in_array($station->type, self::VALID_TYPES)) {
                $station->type = 'cooking'; // Default to valid type
            }
            
            // Auto-generate code if not provided
            if (empty($station->code)) {
                $station->code = static::generateUniqueCode($station->type, $station->branch_id);
            }
        });
    }

    /**
     * Generate unique kitchen station code for PostgreSQL
     */
    public static function generateUniqueCode(string $type, int $branchId): string
    {
        $typePrefix = match($type) {
            'cooking' => 'COOK',
            'preparation' => 'PREP',
            'prep' => 'PREP',
            'beverage' => 'BEV',
            'dessert' => 'DESS',
            'grilling' => 'GRILL',
            'grill' => 'GRILL',
            'fry' => 'FRY',
            'bar' => 'BAR',
            'pastry' => 'PAST',
            'salad' => 'SALAD',
            'cold_kitchen' => 'COLD',
            'hot_kitchen' => 'HOT',
            'expo' => 'EXPO',
            'service' => 'SERV',
            default => 'MAIN'
        };

        $branchCode = str_pad($branchId, 3, '0', STR_PAD_LEFT);
        
        // Find next available sequence number
        $sequence = 1;
        do {
            $sequenceCode = str_pad($sequence, 2, '0', STR_PAD_LEFT);
            $code = $typePrefix . '_' . $branchCode . '_' . $sequenceCode;
            
            $exists = static::where('code', $code)->exists();
            $sequence++;
        } while ($exists && $sequence < 100);

        if ($exists) {
            // Fallback to timestamp-based code
            $code = $typePrefix . '_' . $branchCode . '_' . time();
        }

        return $code;
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function kots(): HasMany
    {
        return $this->hasMany(\App\Models\Kot::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
