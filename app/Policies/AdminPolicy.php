<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Grant all permissions to super admin before checking other methods.
     */
    public function before(Admin $admin, $ability)
    {
        if (isset($admin->is_super_admin) && $admin->is_super_admin) {
            return true;
        }
    }

    /**
     * Determine whether the admin can view any admins.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('admins.view');
    }

    /**
     * Determine whether the admin can view a specific admin.
     */
    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.view') && ($admin->organization_id === $model->organization_id || $admin->is_super_admin);
    }

    /**
     * Determine whether the admin can create admins.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('admins.create');
    }

    /**
     * Determine whether the admin can update a specific admin.
     */
    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.edit') && ($admin->organization_id === $model->organization_id || $admin->is_super_admin);
    }

    /**
     * Determine whether the admin can delete a specific admin.
     */
    public function delete(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('admins.delete') && ($admin->organization_id === $model->organization_id || $admin->is_super_admin);
    }
}
