<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'is_active',
        'is_head_office',
        'activation_key',
        'type',
        'status',
        'settings',
        'max_capacity',
        'features',
        'total_capacity',
        'reservation_fee',
        'cancellation_fee',
        'slug',
        'contact_person',
        'contact_person_designation',
        'contact_person_phone',
        'opened_at',
        'activated_at',
        'manager_name',
        'manager_phone',
        'operating_hours',
        'code'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_head_office' => 'boolean',
        'settings' => 'array',
        'features' => 'array',
        'operating_hours' => 'array',
        'opening_time' => 'string',
        'closing_time' => 'string',
        'max_capacity' => 'integer',
        'total_capacity' => 'integer',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'opened_at' => 'datetime',
        'activated_at' => 'datetime'
    ];

    /**
     * Get default kitchen stations configuration for PostgreSQL with valid constraint types
     */
    public function getDefaultKitchenStations(): array
    {
        return [
            [
                'name' => 'Hot Kitchen',
                'code' => $this->generateStationCode('HOT', 1),
                'type' => 'cooking', // Valid type
                'description' => 'Main cooking station for hot dishes',
                'is_active' => true,
                'max_concurrent_orders' => 8,
                'order_priority' => 1
            ],
            [
                'name' => 'Cold Kitchen',
                'code' => $this->generateStationCode('COLD', 2),
                'type' => 'preparation', // Changed from 'preparation' to valid type
                'description' => 'Cold preparations, salads, and appetizers',
                'is_active' => true,
                'max_concurrent_orders' => 6,
                'order_priority' => 2
            ],
            [
                'name' => 'Grill Station',
                'code' => $this->generateStationCode('GRILL', 3),
                'type' => 'grilling', // Valid type
                'description' => 'Grilled items and BBQ',
                'is_active' => true,
                'max_concurrent_orders' => 5,
                'order_priority' => 3
            ],
            [
                'name' => 'Beverage Station',
                'code' => $this->generateStationCode('BEV', 4),
                'type' => 'beverage', // Valid type
                'description' => 'Drinks, juices, and beverages',
                'is_active' => true,
                'max_concurrent_orders' => 10,
                'order_priority' => 4
            ],
            [
                'name' => 'Pastry Station',
                'code' => $this->generateStationCode('PASTRY', 5),
                'type' => 'dessert', // Valid type
                'description' => 'Desserts, pastries, and sweet items',
                'is_active' => true,
                'max_concurrent_orders' => 4,
                'order_priority' => 5
            ]
        ];
    }

    /**
     * Generate unique station code for PostgreSQL compatibility
     */
    private function generateStationCode(string $type, int $sequence): string
    {
        return strtoupper($type) . '_' . str_pad($this->id ?? 1, 3, '0', STR_PAD_LEFT) . '_' . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the is_active attribute with proper boolean conversion
     */
    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Set the is_active attribute with proper boolean conversion
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = (bool) $value;
    }

    /**
     * Check if branch can be activated
     */
    public function canBeActivated(): bool
    {
        return $this->organization && 
               $this->organization->is_active && 
               !empty($this->activation_key);
    }

    /**
     * Get formatted opening hours
     */
    public function getFormattedHoursAttribute()
    {
        if (!$this->opening_time || !$this->closing_time) {
            return 'Hours not set';
        }

        return $this->formatTime($this->opening_time) . ' - ' . $this->formatTime($this->closing_time);
    }

    /**
     * Format time to 12-hour format
     */
    private function formatTime($time)
    {
        try {
            return \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
        } catch (\Exception $e) {
            return $time;
        }
    }

    /**
     * Check if branch is currently open
     */
    public function isCurrentlyOpen()
    {
        if (!$this->is_active || !$this->opening_time || !$this->closing_time) {
            return false;
        }

        $now = now()->format('H:i');
        return $now >= $this->opening_time && $now <= $this->closing_time;
    }

    // Relationships
    /**
     * Get the organization that owns the branch
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the kitchen stations for the branch
     */
    public function kitchenStations(): HasMany
    {
        return $this->hasMany(\App\Models\KitchenStation::class);
    }

    /**
     * Get the roles for the branch
     */
    public function roles(): HasMany
    {
        return $this->hasMany(\App\Models\Role::class);
    }

    /**
     * Get the subscriptions for the branch
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    /**
     * Get the reservations for the branch
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(\App\Models\Reservation::class);
    }

    /**
     * Get the tables for the branch
     */
    public function tables(): HasMany
    {
        return $this->hasMany(\App\Models\Table::class);
    }

    /**
     * Get the users for the branch
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }

    /**
     * Get the menu categories for the branch
     */
    public function menuCategories(): HasMany
    {
        return $this->hasMany(\App\Models\MenuCategory::class);
    }

    /**
     * Get the menu items for the branch
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(\App\Models\MenuItem::class);
    }

    /**
     * Get the orders for the branch
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Scope for active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for branches by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope for head office branches
     */
    public function scopeHeadOffice($query)
    {
        return $query->where('is_head_office', true);
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branch) {
            Log::info('Creating new branch', [
                'name' => $branch->name,
                'organization_id' => $branch->organization_id
            ]);

            // Generate activation key if not provided
            if (empty($branch->activation_key)) {
                $branch->activation_key = \Illuminate\Support\Str::random(40);
            }

            // Generate slug if not provided
            if (empty($branch->slug)) {
                $branch->slug = \Illuminate\Support\Str::slug($branch->name);
            }

            // Ensure branch is inactive by default
            if (!isset($branch->is_active)) {
                $branch->is_active = false;
            }
        });

        static::updating(function ($branch) {
            // Prevent branch activation if organization is inactive
            if ($branch->isDirty('is_active') && $branch->is_active) {
                $organization = $branch->organization;
                if ($organization && !$organization->is_active) {
                    throw new \Exception('Cannot activate branch: Organization is inactive. Please activate the organization first.');
                }
            }
        });

        static::created(function ($branch) {
            Log::info('Branch created successfully', [
                'id' => $branch->id,
                'name' => $branch->name
            ]);
        });
    }
}