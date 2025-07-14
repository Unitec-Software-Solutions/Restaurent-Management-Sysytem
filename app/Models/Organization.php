<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'contact_person_designation',
        'contact_person_phone',
        'business_type',
        'is_active',
        'subscription_plan_id',
        'plan_snapshot',
        'discount_percentage',
        'activation_key',
        'activated_at',
        'password'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'plan_snapshot' => 'array',
        'discount_percentage' => 'decimal:2'
    ];

    /**
     * Boot method to set defaults and generate activation key
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            // Default status: Organization must default to "inactive"
            if (!isset($organization->is_active)) {
                $organization->is_active = false;
            }

            if (empty($organization->activation_key)) {
                $organization->activation_key = Str::uuid();
            }
        });

        static::updating(function ($organization) {
            // If organization becomes inactive, deactivate all branches
            if (!$organization->is_active && $organization->isDirty('is_active')) {
                $organization->branches()->update(['is_active' => false]);

                Log::info('Organization deactivated - all branches deactivated', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'branches_count' => $organization->branches()->count()
                ]);
            }

            // When organization becomes active, log but don't auto-activate branches
            // Branches should be activated individually for better control
            if ($organization->is_active && $organization->isDirty('is_active')) {
                Log::info('Organization activated - branches can now be activated individually', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'inactive_branches_count' => $organization->branches()->where('is_active', false)->count()
                ]);
            }
        });

        static::updated(function ($organization) {
            // Additional logging for status changes
            if ($organization->isDirty('is_active')) {
                Log::info('Organization status changed', [
                    'organization_id' => $organization->id,
                    'old_status' => $organization->getOriginal('is_active') ? 'active' : 'inactive',
                    'new_status' => $organization->is_active ? 'active' : 'inactive'
                ]);
            }
        });
    }

    /**
     * Accessor: Ensure consistent status checking
     */
    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Mutator: Ensure boolean conversion for status
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = (bool) $value;
    }

    /**
     * Relationships following UI/UX guidelines
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the head office branch id for the organization.
     *
     * @return int|null
     */
    public function getHeadOfficeBranchId()
    {
        $headOffice = $this->branches()->where('is_head_office', true)->first();
        return $headOffice ? $headOffice->id : null;
    }

    /**
     * Get active branches for the organization.
     */
    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Alias for subscriptionPlan relationship for compatibility
     * Used by OrganizationController@summary
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function itemMasters()
    {
        return $this->hasMany(ItemMaster::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function kitchenStations()
    {
        return $this->hasManyThrough(KitchenStation::class, Branch::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scopes for common queries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeActivated($query)
    {
        return $query->whereNotNull('activated_at');
    }

    public function scopeWithSubscription($query)
    {
        return $query->whereNotNull('subscription_plan_id');
    }

    /**
     * Get organization status badge for UI display
     */
    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800';
        }

        if (!$this->activated_at) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return 'bg-green-100 text-green-800';
    }

    /**
     * Get organization status text
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if (!$this->activated_at) {
            return 'Pending Activation';
        }

        return 'Active';
    }

    /**
     * Check if organization has specific module access
     */
    public function hasModuleAccess(string $moduleName): bool
    {
        if (!$this->subscriptionPlan) {
            return false;
        }

        $modules = $this->subscriptionPlan->modules ?? [];
        return collect($modules)->contains('name', $moduleName);
    }

    /**
     * Get active subscription
     */
    public function getActiveSubscription()
    {
        return $this->subscriptions()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    /**
     * Get organization metrics for dashboard
     */
    public function getDashboardMetrics(): array
    {
        return [
            'total_branches' => $this->branches()->count(),
            'active_branches' => $this->branches()->where('is_active', true)->count(),
            'total_menu_items' => $this->menuItems()->count(),
            'active_menu_items' => $this->menuItems()->where('is_active', true)->count(),
            'total_orders' => $this->orders()->count(),
            'pending_orders' => $this->orders()->where('status', 'pending')->count(),
            'total_admins' => $this->admins()->count(),
            'active_admins' => $this->admins()->where('is_active', true)->count(),
        ];
    }

    /**
     * Generate unique activation key
     */
    public function generateActivationKey(): string
    {
        $this->activation_key = Str::uuid();
        $this->save();

        return $this->activation_key;
    }

    /**
     * Activate organization
     */
    public function activate(): bool
    {
        $this->activated_at = now();
        $this->is_active = true;

        return $this->save();
    }

    /**
     * Get subscription tier
     */
    public function getSubscriptionTier(): string
    {
        return $this->subscriptionPlan?->name ?? 'No Subscription';
    }

    /**
     * Check if organization can create more branches
     */
    public function canCreateMoreBranches(): bool
    {
        $plan = $this->subscriptionPlan;
        if (!$plan) {
            return false;
        }

        $maxBranches = $plan->max_branches ?? 1;
        $currentBranches = $this->branches()->count();

        return $currentBranches < $maxBranches;
    }
}
