<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

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
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast following UI/UX data types.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'preferences' => 'array',
            'ui_settings' => 'array',
            'two_factor_recovery_codes' => 'encrypted:array',
            'password' => 'hashed',
        ];
    }

    /**
     * Guard name for Spatie permissions
     */
    protected $guard_name = 'admin';

    /**
     * Boot method to set default UI settings and handle role migration
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            // Set default UI settings following the UI/UX guidelines
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

            // Handle legacy role field
            if ($admin->role && !$admin->current_role_id) {
                $admin->migrateLegacyRole();
            }
        });

        static::updating(function ($admin) {
            // Handle legacy role field updates
            if ($admin->isDirty('role') && $admin->role && !$admin->current_role_id) {
                $admin->migrateLegacyRole();
            }
        });
    }

    /**
     * Migrate legacy role to Spatie role system
     */
    public function migrateLegacyRole(): void
    {
        if (!$this->role) return;

        // Map legacy roles to Spatie roles
        $roleMapping = [
            'admin' => 'Organization Admin',
            'super_admin' => 'Super Admin',
            'branch_admin' => 'Branch Admin',
            'manager' => 'Branch Manager',
            'staff' => 'Staff',
        ];

        $roleName = $roleMapping[$this->role] ?? $this->role;

        try {
            $spatieRole = \Spatie\Permission\Models\Role::where('name', $roleName)
                ->where('guard_name', 'admin')
                ->first();

            if ($spatieRole) {
                $this->current_role_id = $spatieRole->id;
                if (!$this->hasRole($spatieRole)) {
                    $this->assignRole($spatieRole);
                }
            }
        } catch (\Exception $e) {
            // Silently handle role migration errors
        }
    }

    /**
     * Relationships following UI/UX guidelines
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'current_role_id');
    }

    /**
     * Scopes for UI filtering following UI/UX patterns
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
     * Accessors for UI display following UI/UX guidelines
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?? $this->email,
        );
    }

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_super_admin) {
                    return [
                        'text' => 'Super Admin',
                        'class' => 'bg-purple-100 text-purple-800'
                    ];
                }

                return match($this->status) {
                    'active' => [
                        'text' => 'Active',
                        'class' => 'bg-green-100 text-green-800'
                    ],
                    'inactive' => [
                        'text' => 'Inactive',
                        'class' => 'bg-gray-100 text-gray-800'
                    ],
                    'suspended' => [
                        'text' => 'Suspended',
                        'class' => 'bg-red-100 text-red-800'
                    ],
                    'pending' => [
                        'text' => 'Pending',
                        'class' => 'bg-yellow-100 text-yellow-800'
                    ],
                    default => [
                        'text' => 'Unknown',
                        'class' => 'bg-gray-100 text-gray-800'
                    ]
                };
            }
        );
    }

    protected function roleBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                $primaryRole = $this->roles->first();
                if ($primaryRole) {
                    return [
                        'text' => $primaryRole->name,
                        'class' => 'bg-indigo-100 text-indigo-800'
                    ];
                }

                // Fallback to legacy role
                if ($this->role) {
                    return [
                        'text' => ucfirst(str_replace('_', ' ', $this->role)),
                        'class' => 'bg-blue-100 text-blue-800'
                    ];
                }

                return [
                    'text' => 'No Role',
                    'class' => 'bg-gray-100 text-gray-800'
                ];
            }
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->profile_image) {
                    return asset('storage/' . $this->profile_image);
                }

                // Generate avatar with initials following UI/UX guidelines
                $initials = collect(explode(' ', $this->name))
                    ->take(2)
                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                    ->join('');

                return "https://ui-avatars.com/api/?name={$initials}&background=6366f1&color=fff&size=128";
            }
        );
    }

    /**
     * Permission checking methods following UI/UX access control
     */
    public function canAccessOrganization(int $organizationId): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->organization_id === $organizationId;
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if ($this->branch_id === null) {
            // Organization admin can access all branches in their organization
            $branch = Branch::find($branchId);
            return $branch && $branch->organization_id === $this->organization_id;
        }

        return $this->branch_id === $branchId;
    }

    /**
     * Security methods following UI/UX guidelines
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        // Lock account after 5 failed attempts
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
     * UI preference methods following UI/UX customization
     */
    public function getUiPreference(string $key, $default = null)
    {
        return data_get($this->ui_settings, $key, $default);
    }

    public function setUiPreference(string $key, $value): void
    {
        $settings = $this->ui_settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['ui_settings' => $settings]);
    }

    /**
     * Check if admin is a super admin
     * Uses both the is_super_admin column and Spatie roles for flexibility
     */
    public function isSuperAdmin(): bool
    {
        // Check the direct column first (fastest)
        if ($this->is_super_admin) {
            return true;
        }

        // Check if they have the 'Super Admin' role through Spatie
        try {
            return $this->hasRole('Super Admin', 'admin');
        } catch (\Exception $e) {
            // Fallback if Spatie roles aren't set up
            return false;
        }
    }

    /**
     * Check if user is an admin
     * Since this is the Admin model, all instances are admins by definition
     */
    public function isAdmin(): bool
    {
        return true;
    }

    /**
     * Check if admin has organization-level access
     * Super admins have global access, regular admins need organization assignment
     */
    public function hasOrganizationAccess($organizationId = null): bool
    {
        // Super admins have access to all organizations
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Regular admins need organization assignment
        if (!$this->organization_id) {
            return false;
        }

        // If specific organization is requested, check match
        if ($organizationId !== null) {
            return $this->organization_id == $organizationId;
        }

        return true;
    }

    /**
     * Check if admin can manage other admins
     */
    public function canManageAdmins(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        try {
            return $this->hasPermissionTo('manage admins', 'admin');
        } catch (\Exception $e) {
            // If permission doesn't exist, fall back to role check
            return $this->hasRole(['Admin Manager', 'Super Admin'], 'admin');
        }
    }

    /**
     * Check if admin can access system-wide settings
     */
    public function canManageSystem(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        try {
            return $this->hasPermissionTo('manage system', 'admin');
        } catch (\Exception $e) {
            // If permission doesn't exist, only super admins can manage system
            return false;
        }
    }

    /**
     * Dashboard data methods following UI/UX metrics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_logins' => 0, // Would be tracked in audit logs
            'last_login' => $this->last_login_at?->diffForHumans() ?? 'Never',
            'account_status' => $this->status_badge['text'],
            'role_status' => $this->role_badge['text'],
            'permissions_count' => $this->getAllPermissions()->count(),
            'organization' => $this->organization?->name,
            'branch' => $this->branch?->name,
            'department' => $this->department,
            'job_title' => $this->job_title,
        ];
    }

    /**
     * Check if admin is an organization admin (has org but no branch)
     */
    public function isOrganizationAdmin()
    {
        return !$this->is_super_admin && $this->organization_id && is_null($this->branch_id);
    }

    /**
     * Check if admin is a branch admin (has both org and branch)
     */
    public function isBranchAdmin()
    {
        return !$this->is_super_admin && $this->organization_id && $this->branch_id;
    }
}
