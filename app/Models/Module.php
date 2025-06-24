<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Module extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable following UI/UX guidelines.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast following UI/UX data types.
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scopes for UI filtering following UI/UX patterns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeWithPermission($query, string $permission)
    {
        return $query->whereJsonContains('permissions', $permission);
    }

    /**
     * Accessors for UI display following UI/UX guidelines
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                return [
                    'text' => $this->is_active ? 'Active' : 'Inactive',
                    'class' => $this->is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                ];
            }
        );
    }

    protected function permissionCount(): Attribute
    {
        return Attribute::make(
            get: fn() => count($this->permissions ?? [])
        );
    }

    protected function formattedSlug(): Attribute
    {
        return Attribute::make(
            get: fn() => ucwords(str_replace(['-', '_'], ' ', $this->slug))
        );
    }

    /**
     * Business logic methods following UI/UX guidelines
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $filteredPermissions = array_filter($permissions, fn($p) => $p !== $permission);
        
        $this->update(['permissions' => array_values($filteredPermissions)]);
    }

    public function getPermissionsByCategory(): array
    {
        $permissions = $this->permissions ?? [];
        $categorized = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission);
            $action = $parts[1] ?? 'general';
            $category = ucfirst($action);
            
            if (!isset($categorized[$category])) {
                $categorized[$category] = [];
            }
            
            $categorized[$category][] = $permission;
        }
        
        return $categorized;
    }

    /**
     * UI helper methods following UI/UX guidelines
     */
    public function getDisplayName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description ?? 'No description available';
    }

    public function getIconClass(): string
    {
        $iconMap = [
            'dashboard' => 'fa-tachometer-alt',
            'inventory' => 'fa-boxes',
            'reservations' => 'fa-calendar-check',
            'orders' => 'fa-shopping-cart',
            'kitchen' => 'fa-fire',
            'reports' => 'fa-chart-bar',
            'customers' => 'fa-users',
            'staff' => 'fa-user-tie',
            'suppliers' => 'fa-truck',
            'menu' => 'fa-utensils',
            'users' => 'fa-user-cog',
            'organizations' => 'fa-building',
            'branches' => 'fa-code-branch',
            'subscriptions' => 'fa-credit-card',
            'finance' => 'fa-dollar-sign',
            'roles' => 'fa-user-shield',
            'tables' => 'fa-chair',
            'pos' => 'fa-cash-register',
            'settings' => 'fa-cog',
            'modules' => 'fa-puzzle-piece',
        ];
        
        return $iconMap[$this->slug] ?? 'fa-cube';
    }

    public function getRoutePrefix(): string
    {
        return $this->slug;
    }

    /**
     * Dashboard helper methods
     */
    public function getDashboardMetrics(): array
    {
        // This would be implemented based on the specific module
        return [
            'total_permissions' => $this->permission_count,
            'status' => $this->is_active ? 'Active' : 'Inactive',
            'last_updated' => $this->updated_at->diffForHumans(),
        ];
    }

    /**
     * Validation methods
     */
    public function isRequiredModule(): bool
    {
        $requiredModules = ['dashboard', 'users', 'roles', 'settings'];
        return in_array($this->slug, $requiredModules);
    }

    public function canBeDeactivated(): bool
    {
        return !$this->isRequiredModule();
    }

    public function canBeDeleted(): bool
    {
        return !$this->isRequiredModule() && !$this->is_active;
    }

    /**
     * Relationships
     */
    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscription_plan_modules');
    }

    /**
     * Static helper methods
     */
    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getActiveModules(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('name')->get();
    }

    public static function getCoreModules(): \Illuminate\Database\Eloquent\Collection
    {
        $coreModuleSlugs = ['dashboard', 'inventory', 'orders', 'kitchen', 'reservations'];
        return static::whereIn('slug', $coreModuleSlugs)->get();
    }
}
