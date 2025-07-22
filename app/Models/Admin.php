<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Branch;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Guard name for Spatie permissions
     */
    protected $guard_name = 'admin';

    /**
     * The model type used for role assignments
     */
    protected string $model_type = 'App\Models\Admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'organization_id',
        'branch_id',
        'role',
        'current_role_id',
        'department',
        'job_title',
        'status',
        'is_super_admin',
        'is_active',
        'phone',
        'profile_image',
        'last_login_at',
        'preferences',
        'ui_settings',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'ui_settings' => 'array',
        'preferences' => 'array',
        'failed_login_attempts' => 'integer',
        'organization_id' => 'integer',
        'branch_id' => 'integer',
        'current_role_id' => 'integer',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'is_super_admin' => false,
        'failed_login_attempts' => 0,
        'status' => 'active',
        'ui_settings' => '{}',
        'preferences' => '{}',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'display_name',
        'avatar_url',
        'status_badge',
        'role_badge',
    ];

    /**
     * Get all effective permissions for the admin, including those from super admin status and roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEffectivePermissions(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::where('guard_name', 'admin')->get();
        }
        return $this->getAllPermissions();
    }

    /**
     * Get formatted permissions array with source information
     *
     * @return array
     */
    public function getFormattedPermissions(): array
    {
        $permissions = $this->getEffectivePermissions();

        $formatted = [];
        foreach ($permissions as $permission) {
            $formatted[] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'source' => $this->getPermissionSource($permission)
            ];
        }

        return $formatted;
    }

    /**
     * Determine the source of a permission (Direct or via Role)
     *
     * @param Permission $permission
     * @return string
     */
    protected function getPermissionSource($permission): string
    {
        if ($this->hasDirectPermission($permission)) {
            return 'Direct';
        }

        $roles = $this->roles()->with('permissions')->get();
        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                return "Via Role: {$role->name}";
            }
        }

        return 'Unknown';
    }

    /**
     * Check if admin has any of the given permissions through any of their roles
     *
     * @param array|string $permissions
     * @return bool
     */
    public function hasAnyPermissionThroughRole($permissions): bool
    {
        return $this->hasAnyPermission($permissions);
    }

    /**
     * Define organization relationship
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Define branch relationship
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Current active role relationship
     */
    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    /**
     * Get all permissions assigned to the admin through roles
     */
    public function getAllEffectivePermissions()
    {
        return $this->getAllPermissions();
    }

    /**
     * Check if admin has super admin role or is_super_admin flag
     */
    public function isSuperAdmin(): bool
    {
        return (bool) ($this->attributes['is_super_admin'] ?? false) || $this->hasRole('Super Admin');
    }

    /**
     * Check if admin is an organization admin (has org but no branch)
     */
    public function isOrganizationAdmin(): bool
    {
        return !$this->isSuperAdmin()
            && !is_null($this->organization_id)
            && is_null($this->branch_id);
    }

    /**
     * Check if admin is a branch admin (has both org and branch)
     */
    public function isBranchAdmin(): bool
    {
        return !$this->isSuperAdmin()
            && !is_null($this->organization_id)
            && !is_null($this->branch_id);
    }

    /**
     * Get the admin's display name
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?? $this->getAttribute('email'),
        );
    }

    // /**
    //  * Get the admin's avatar URL
    //  */
    // protected function avatarUrl(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             if ($this->profile_image) {
    //                 return asset('storage/' . $this->profile_image);
    //             }

    //             $name = $this->name ?? $this->email;
    //             $initials = collect(explode(' ', $name))
    //                 ->map(fn($segment) => mb_substr($segment, 0, 1, 'UTF-8'))
    //                 ->take(2)
    //                 ->map(fn($initial) => strtoupper($initial))
    //                 ->join('');

    //             return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&color=FFFFFF&background=6366F1";
    //         }
    //     );
    // }

    /**
     * Get the admin's status badge
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                $status = $this->status ?? 'unknown';

                return match($status) {
                    'active' => ['text' => 'Active', 'class' => 'bg-green-100 text-green-800'],
                    'inactive' => ['text' => 'Inactive', 'class' => 'bg-gray-100 text-gray-800'],
                    'suspended' => ['text' => 'Suspended', 'class' => 'bg-red-100 text-red-800'],
                    'pending' => ['text' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800'],
                    default => ['text' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'],
                };
            }
        );
    }

    /**
     * Get the admin's role badge
     */
    protected function roleBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isSuperAdmin()) {
                    return ['text' => 'Super Admin', 'class' => 'bg-purple-100 text-purple-800'];
                }

                $role = $this->roles()->first();
                return [
                    'text' => $role ? $role->name : 'No Role',
                    'class' => $role ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                ];
            }
        );
    }

    /**
     * Scopes for UI filtering
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeSuperAdmin($query)
    {
        return $query->where('is_super_admin', true);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Security methods
     */
    public function isLocked(): bool
    {
        return $this->locked_until && \Illuminate\Support\Carbon::parse($this->locked_until)->gt(now());
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= 5) {
            $this->update([
                'locked_until' => now()->addMinutes(30),
                'status' => 'suspended'
            ]);
        }
    }

    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'status' => 'active'
        ]);
    }

    /**
     * Helper function to get formated admin data
     */
    public function toDetailedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getAttribute('name'),
            'email' => $this->getAttribute('email'),
            'organization' => $this->organization ? [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ] : null,
            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null,
            'roles' => $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]),
            'status' => $this->getAttribute('status'),
            'status_badge' => $this->getAccessorValue('status_badge'),
            'role_badge' => $this->getAccessorValue('role_badge'),
            'is_super_admin' => $this->getAttribute('is_super_admin'),
            'is_active' => $this->getAttribute('is_active'),
            'department' => $this->getAttribute('department'),
            'job_title' => $this->getAttribute('job_title'),
            'avatar_url' => $this->getAccessorValue('avatar_url'),
            'last_login_at' => $this->getAttribute('last_login_at'),
        ];
    }

    /**
     * Boot method to set default UI settings and handle role migration
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->ui_settings)) {
                $admin->ui_settings = [
                    'theme' => 'light',
                    'sidebar_collapsed' => false,
                    'dashboard_layout' => 'grid',
                    'notifications_enabled' => true,
                    'preferred_language' => 'en',
                    'cards_per_row' => 4,
                    'show_help_tips' => true,
                ];
            }

            if (empty($admin->preferences)) {
                $admin->preferences = [
                    'timezone' => 'Asia/Colombo',
                    'date_format' => 'Y-m-d',
                    'time_format' => '24h',
                    'currency' => 'LKR',
                ];
            }

            if ($admin->role && !$admin->current_role_id) {
                $admin->migrateLegacyRole();
            }
        });

        static::updating(function ($admin) {
            if ($admin->isDirty('role') && $admin->role && !$admin->current_role_id) {
                $admin->migrateLegacyRole();
            }
        });
    }

    /**
     * Override roles method to enforce admin guard
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_type', self::class)
            ->where('guard_name', 'admin');
    }

    /**
     * Permission checking methods
     */
    public function canAccessOrganization(int $organizationId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->organization_id === $organizationId;
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->branch_id && $this->organization_id) {
            return Branch::where('id', $branchId)
                ->where('organization_id', $this->organization_id)
                ->exists();
        }

        return $this->branch_id === $branchId;
    }

    /**
     * UI preference methods
     */
    public function getUiPreference(string $key, $default = null)
    {
        return data_get($this->ui_settings, $key, $default);
    }

    public function setUiPreference(string $key, $value): void
    {
        $settings = $this->ui_settings ?? [];
        data_set($settings, $key, $value);
        $this->ui_settings = $settings;
    }

    /**
     * Dashboard data methods
     */
    public function getDashboardStats(): array
    {
        return [
            'total_logins' => 0,
            'last_login' => optional($this->last_login_at)->diffForHumans() ?? 'Never',
            'account_status' => $this->getAccessorValue('status_badge')['text'],
            'role_status' => $this->getAccessorValue('role_badge')['text'],
            'permissions_count' => $this->getAllPermissions()->count(),
            'organization' => $this->organization?->name,
            'branch' => $this->branch?->name,
            'department' => $this->getAttribute('department'),
            'job_title' => $this->getAttribute('job_title'),
        ];
    }

    /**
     * Migrate legacy role to Spatie role system
     */
    protected function migrateLegacyRole(): void
    {
        if (!$this->getAttribute('role')) return;

        $roleMapping = [
            'admin' => 'Organization Admin',
            'super_admin' => 'Super Admin',
            'branch_admin' => 'Branch Admin',
            'manager' => 'Branch Manager',
            'staff' => 'Staff',
        ];

        $legacyRole = $this->getAttribute('role');
        $roleName = $roleMapping[$legacyRole] ?? $legacyRole;

        try {
            $spatieRole = Role::where('name', $roleName)
                ->where('guard_name', 'admin')
                ->first();

            if ($spatieRole) {
                $this->current_role_id = $spatieRole->id;
                $this->assignRole($spatieRole);
            }
        } catch (\Exception $e) {
            // Handle exception
        }
    }

    /**
     * Ensure status property always returns a value
     */
    public function getStatusAttribute($value)
    {
        return $value ?? 'inactive';
    }
}
