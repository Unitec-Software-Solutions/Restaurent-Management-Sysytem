<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Organization;
use App\Services\PermissionSystemService;

class BranchPolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Grant all permissions to super admin before checking other methods.
     */
    public function before($user, $ability)
    {
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return true;
        }
    }

    public function view($user, Branch $branch)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.view']) && $user->organization_id === $branch->organization_id;
    }

    public function update(Admin $admin, Branch $branch)
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.edit']) && $admin->organization_id === $branch->organization_id;
    }

    public function regenerateKey(Admin $admin, Branch $branch)
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.regenerate_key']) && $admin->organization_id === $branch->organization_id;
    }

    public function delete($user, $branch)
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return !$branch->is_active;
        }
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.delete']) && !$branch->is_active && $user->organization_id === $branch->organization_id;
    }

    public function create($user, Organization $organization)
    {
        if (isset($user->is_super_admin) && $user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.create']) && $user->organization_id === $organization->id;
    }

    public function activate(Admin $admin, Branch $branch)
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        if (isset($availablePermissions['branches.activate'])) {
            if ($admin->isOrganizationAdmin() && $admin->organization_id === $branch->organization_id) {
                return true;
            }
            if ($admin->isBranchAdmin() && $admin->branch_id === $branch->id) {
                return true;
            }
        }
        return false;
    }

    public function deactivate(Admin $admin, Branch $branch)
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['branches.deactivate']) && $admin->isOrganizationAdmin() && $admin->organization_id === $branch->organization_id;
    }
}