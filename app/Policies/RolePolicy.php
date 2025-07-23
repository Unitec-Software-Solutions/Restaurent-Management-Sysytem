<?php

namespace App\Policies;

use App\Models\{User, Role};
use App\Services\PermissionSystemService;

class RolePolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    public function manage(User $user, Role $role)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.manage']) && $user->organization_id === $role->organization_id;
    }
    public function viewAny($user)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.view']);
    }
    public function create($user)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.create']);
    }
    public function update($user, $role)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.edit']) && $user->organization_id === $role->organization_id;
    }
    public function view($user, $role)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.view']) && $user->organization_id === $role->organization_id;
    }
    public function delete($user, $role)
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['roles.delete']) && $user->organization_id === $role->organization_id;
    }
}
