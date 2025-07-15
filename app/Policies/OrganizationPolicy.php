<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Organization;
use App\Services\PermissionSystemService;

class OrganizationPolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    public function viewAny(User|Admin $user): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.view']);
    }

    public function create(User|Admin $user): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.create']);
    }

    public function update(User|Admin $user, Organization $organization): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.edit']) && $user->organization_id === $organization->id;
    }

    public function activate(User|Admin $user, Organization $organization): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.activate']) && $user->organization_id === $organization->id;
    }

    public function deactivate(User|Admin $user, Organization $organization): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.deactivate']) && $user->organization_id === $organization->id;
    }
    
    public function delete($user, Organization $organization)
    {
        if ($user->is_super_admin) {
            return !$organization->is_active;
        }
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.delete']) && !$organization->is_active && $user->organization_id === $organization->id;
    }

    public function regenerateKey(User|Admin $user, Organization $organization): bool
    {
        if ($user->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($user, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['organizations.regenerate_key']) && $user->organization_id === $organization->id;
    }
}
