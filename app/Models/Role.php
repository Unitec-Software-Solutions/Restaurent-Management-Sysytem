<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'scope',
        'is_system_role',
        'organization_id',
        'branch_id',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
    ];

    /**
     * Get the organization that owns the role
     */
    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    /**
     * Get the branch that owns the role
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    // Use Spatie's default permissions() relationship

    /**
     * Get admins assigned to this role
     */
    public function admins()
    {
        return $this->belongsToMany(
            \App\Models\Admin::class,
            'model_has_roles',
            'role_id',
            'model_id'
        )->where('model_type', \App\Models\Admin::class);
    }

    /**
     * Scope roles by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where(function ($q) use ($organizationId) {
            $q->whereNull('organization_id')
              ->orWhere('organization_id', $organizationId);
        });
    }

    /**
     * Scope roles by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->whereNull('branch_id')
              ->orWhere('branch_id', $branchId);
        });
    }

    /**
     * System roles that cannot be deleted
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Custom roles that can be modified
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system_role', false);
    }

    /**
     * Get role hierarchy level
     */
    public function getHierarchyLevel(): int
    {
        $levels = [
            'system' => 1,
            'organization' => 2,
            'branch' => 3,
            'personal' => 4,
        ];

        $scope = $this->getAttribute('scope');
        return $levels[$scope] ?? 5;
    }

    /**
     * Check if role can assign another role
     */
    public function canAssignRole(Role $targetRole): bool
    {
        return $this->getHierarchyLevel() <= $targetRole->getHierarchyLevel();
    }

    /**
     * Get default system roles - Only essential admin roles
     */
    public static function getSystemRoles(): array
    {
        return [
            'super_admin' => [
                'name' => 'Super Administrator',
                'scope' => 'system',
                'description' => 'Full system access across all organizations',
                'permissions' => ['*']
            ],
            'org_admin' => [
                'name' => 'Organization Administrator',
                'scope' => 'organization',
                'description' => 'Full access within organization',
                'permissions' => [
                    'manage_organization',
                    'manage_branches',
                    'manage_users',
                    'view_reports',
                    'manage_subscription'
                ]
            ],
            'branch_admin' => [
                'name' => 'Branch Administrator',
                'scope' => 'branch',
                'description' => 'Full access within branch',
                'permissions' => [
                    'manage_branch_operations',
                    'manage_branch_staff',
                    'manage_inventory',
                    'view_branch_reports',
                    'manage_orders'
                ]
            ]
        ];
    }

    /**
     * Sync permissions to this role using Spatie's method
     */
    public function syncPermissions(...$permissions)
    {
        // Flatten the permissions array if needed
        $permissions = is_array($permissions[0] ?? null) ? $permissions[0] : $permissions;

        // Convert permission names to permission models if needed
        if (is_array($permissions) && !empty($permissions) && is_string($permissions[0] ?? null)) {
            $permissionModels = \Spatie\Permission\Models\Permission::whereIn('name', $permissions)
                ->where('guard_name', $this->guard_name)
                ->get();

            return parent::syncPermissions($permissionModels);
        }

        return parent::syncPermissions(...$permissions);
    }

    /**
     * Give permission to this role using Spatie's method
     */
    public function givePermissionTo(...$permissions)
    {
        return parent::givePermissionTo(...$permissions);
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        $guardName = $guardName ?? $this->guard_name;
        return parent::hasPermissionTo($permission, $guardName);
    }
}
