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
        return $user->is_super_admin || $user->is_admin;
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
        return $user->is_super_admin || $user->is_admin;
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
        // Only org admin (created by super admin) can assign roles in their org
        return $user->is_admin && $user->organization_id === $model->organization_id;
    }
}
