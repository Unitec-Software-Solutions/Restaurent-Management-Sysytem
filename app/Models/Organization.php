<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
     * Boot method to generate activation key
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->activation_key)) {
                $organization->activation_key = Str::uuid();
            }
        });
    }

    /**
     * Relationships following UI/UX guidelines
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
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
