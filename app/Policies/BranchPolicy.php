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
        if (!$user->hasPermissionTo('branches.view')) return false;
        return data_get($user, 'organization_id') === data_get($branch, 'organization_id');
    }

    public function update(Admin $admin, Branch $branch)
    {
        if (!$admin->hasPermissionTo('branches.edit')) return false;
        return data_get($admin, 'organization_id') === data_get($branch, 'organization_id');
    }

    public function regenerateKey(Admin $admin, Branch $branch)
    {
        if (!$admin->hasPermissionTo('branches.regenerate-key')) return false;
        return data_get($admin, 'organization_id') === data_get($branch, 'organization_id');
    }

    public function delete($user, $branch)
    {
        if (!$user->hasPermissionTo('branches.delete')) return false;
        return $user->organization_id === $branch->organization_id;
    }

    public function create($user, Organization $organization)
    {
        if (!$user->hasPermissionTo('branches.create')) return false;
        return $user->organization_id === $organization->getKey();
    }

    public function activate(Admin $admin, Branch $branch)
    {
        if (!$admin->hasPermissionTo('branches.activate')) return false;
        return data_get($admin, 'organization_id') === data_get($branch, 'organization_id');
    }

    public function deactivate(Admin $admin, Branch $branch)
    {
        if (!$admin->hasPermissionTo('branches.deactivate')) return false;
        return data_get($admin, 'organization_id') === data_get($branch, 'organization_id');
    }
}
