<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        // Super admin can view all users
        if ($user->is_super_admin) {
            return true;
        }

        // Organization and branch admins can view users in their scope
        return $user->isOrganizationAdmin() || $user->isBranchAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, User $model): bool
    {
        return $user->is_super_admin || $user->organization_id === $model->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        // Super admin can create users anywhere
        if ($user->is_super_admin) {
            return true;
        }

        // Organization and branch admins can create users
        return $user->isOrganizationAdmin() || $user->isBranchAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, User $model): bool
    {
        return $user->is_super_admin || $user->organization_id === $model->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, User $model): bool
    {
        return $user->is_super_admin || $user->organization_id === $model->organization_id;
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
        // Super admin can assign any role
        if ($user->is_super_admin) {
            return true;
        }

        // Organization admin can assign roles within their organization
        if ($user->isOrganizationAdmin() && $user->organization_id === $model->organization_id) {
            return true;
        }

        // Branch admin can assign roles within their branch
        if ($user->isBranchAdmin() && $user->branch_id === $model->branch_id) {
            return true;
        }

        return false;
    }
}
