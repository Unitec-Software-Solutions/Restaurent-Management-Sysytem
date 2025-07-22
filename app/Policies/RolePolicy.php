<?php

namespace App\Policies;

use App\Models\{Admin, Role};
use App\Services\PermissionSystemService;

class RolePolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    public function manage(Admin $user, Role $role)
    {
        if ($user->is_super_admin) return true;
        if (method_exists($role, 'trashed') && $role->trashed()) return false;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        // Check organization scope
        if (!$user->is_super_admin && $user->getAttribute('organization_id') !== $role->getAttribute('organization_id')) {
            return false;
        }

        // Check branch scope
        if ($user->isBranchAdmin() && $user->getAttribute('branch_id') !== $role->getAttribute('branch_id')) {
            return false;
        }

        return isset($availablePermissions['roles.manage']);
    }

    public function viewAny(Admin $user)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($user, $permissionDefinitions);
        return isset($availablePermissions['roles.view']);
    }

    public function create(Admin $user)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($user, $permissionDefinitions);
        return isset($availablePermissions['roles.create']);
    }

    public function update(Admin $user, Role $role)
    {
        if ($user->is_super_admin) return true;
        if (method_exists($role, 'trashed') && $role->trashed()) return false;

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($user, $permissionDefinitions);

        // Check organization scope
        if (!$user->is_super_admin && $user->getAttribute('organization_id') !== $role->getAttribute('organization_id')) {
            return false;
        }

        // Check branch scope
        if ($user->isBranchAdmin() && $user->getAttribute('branch_id') !== $role->getAttribute('branch_id')) {
            return false;
        }

        return isset($availablePermissions['roles.edit']);
    }

    public function view(Admin $user, Role $role)
    {
        if ($user->is_super_admin) return true;
        if (method_exists($role, 'trashed') && $role->trashed()) return false;

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($user, $permissionDefinitions);

        // Check organization scope
        if (!$user->is_super_admin && $user->getAttribute('organization_id') !== $role->getAttribute('organization_id')) {
            return false;
        }

        // Check branch scope
        if ($user->isBranchAdmin() && $user->getAttribute('branch_id') !== $role->getAttribute('branch_id')) {
            return false;
        }

        return isset($availablePermissions['roles.view']);
    }

    public function delete(Admin $user, Role $role)
    {
        if ($user->is_super_admin) return true;
        if (method_exists($role, 'trashed') && $role->trashed()) return false;

        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $availablePermissions = $this->permissionService->getAvailablePermissions($user, $permissionDefinitions);

        // Check organization scope
        if (!$user->is_super_admin && $user->getAttribute('organization_id') !== $role->getAttribute('organization_id')) {
            return false;
        }

        // Check branch scope
        if ($user->isBranchAdmin() && $user->getAttribute('branch_id') !== $role->getAttribute('branch_id')) {
            return false;
        }

        return isset($availablePermissions['roles.delete']);
    }
}
