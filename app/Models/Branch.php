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
        'name',
        'slug',
        'organization_id',
        'address',
        'phone',
        'opening_time',
        'closing_time',
        'total_capacity',
        'reservation_fee',
        'cancellation_fee',
        'contact_person',
        'contact_person_designation',
        'contact_person_phone',
        'is_active',
        'activation_key',
        'activated_at',
        'is_head_office',
        'type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_head_office' => 'boolean',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
    ];

    /**
     * Boot method to set defaults and handle activation constraints
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($branch) {
            // Default status: Branch must default to "inactive"
            if (!isset($branch->is_active)) {
                $branch->is_active = false;
            }
        });

        static::updating(function ($branch) {
            // Activation constraint: Branch cannot be active if parent Organization is inactive
            if ($branch->is_active && $branch->isDirty('is_active')) {
                $organization = $branch->organization;
                if (!$organization || !$organization->is_active) {
                    throw new \Exception('Branch cannot be activated while parent organization is inactive');
                }
            }
        });

        static::updated(function ($branch) {
            // Log status changes for audit
            if ($branch->isDirty('is_active')) {
                Log::info('Branch status changed', [
                    'branch_id' => $branch->id,
                    'organization_id' => $branch->organization_id,
                    'old_status' => $branch->getOriginal('is_active') ? 'active' : 'inactive',
                    'new_status' => $branch->is_active ? 'active' : 'inactive'
                ]);
            }
        });
    }

    /**
     * Accessor: Ensure consistent status checking with organization validation
     */
    public function getIsActiveAttribute($value)
    {
        // Branch can only be active if organization is also active
        if ($value && $this->organization && !$this->organization->is_active) {
            return false;
        }
        return (bool) $value;
    }

    /**
     * Mutator: Ensure boolean conversion and validation
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = (bool) $value;
    }

    /**
     * Check if branch can be activated based on organization status
     */
    public function canBeActivated(): bool
    {
        return $this->organization && $this->organization->is_active;
    }

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function kitchenStations(): HasMany
    {
        return $this->hasMany(KitchenStation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableCapacity($date, $startTime, $endTime)
    {
        $reservedCapacity = $this->reservations()
            ->where('date', $date)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->sum('number_of_people');

        return $this->total_capacity - $reservedCapacity;
    }

    public function getAvailableTables($date, $startTime, $endTime, $requiredCapacity)
    {
        return $this->tables()
            ->available($date, $startTime, $endTime)
            ->withCapacity($requiredCapacity)
            ->get();
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Activation Key Logic
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function isSystemActive()
    {
        return $this->organization->is_active && $this->is_active;
    }

    /**
     * Helper methods for branch type management
     */
    public function isHeadOffice(): bool
    {
        return $this->is_head_office;
    }

    public function getDefaultKitchenStations(): array
    {
        $defaultStations = [
            'Hot Kitchen' => ['type' => 'cooking', 'priority' => 1],
            'Cold Kitchen' => ['type' => 'prep', 'priority' => 2],
            'Grill Station' => ['type' => 'grill', 'priority' => 3],
            'Fry Station' => ['type' => 'fry', 'priority' => 4],
            'Dessert Station' => ['type' => 'dessert', 'priority' => 5],
        ];

        // Add beverage/bar stations for appropriate types
        if (in_array($this->type, ['bar', 'pub', 'restaurant'])) {
            $defaultStations['Bar Station'] = ['type' => 'bar', 'priority' => 6];
            $defaultStations['Beverage Station'] = ['type' => 'beverage', 'priority' => 7];
        }

        return $defaultStations;
    }

    /**
     * Manually trigger the automated setup for this branch
     * 
     * @return void
     */
    public function setupAutomation(): void
    {
        $automationService = app(\App\Services\BranchAutomationService::class);
        $automationService->setupNewBranch($this);
    }
}