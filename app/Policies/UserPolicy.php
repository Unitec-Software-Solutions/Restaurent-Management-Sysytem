<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Services\PermissionSystemService;

class UserPolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['users.view']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, User $model): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['users.view']) && $user->organization_id === $model->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['users.create']) && ($user->isOrganizationAdmin() || $user->isBranchAdmin());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, User $model): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['users.edit']) && $user->organization_id === $model->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, User $model): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['users.delete']) && $user->organization_id === $model->organization_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
    
    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assignRole(User|Admin $user, User $model): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        $canAssign = isset($availablePermissions['users.roles']);
        if ($canAssign && $user->isOrganizationAdmin() && $user->organization_id === $model->organization_id) {
            return true;
        }
        if ($canAssign && $user->isBranchAdmin() && $user->branch_id === $model->branch_id) {
            return true;
        }
        return false;
    }
}
