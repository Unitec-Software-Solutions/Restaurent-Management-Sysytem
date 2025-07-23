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
        return $user->hasPermissionTo('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, User $model): bool
    {
        return $user->hasPermissionTo('users.view') && data_get($user, 'organization_id') === data_get($model, 'organization_id');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, User $model): bool
    {
        return $user->hasPermissionTo('users.edit') && data_get($user, 'organization_id') === data_get($model, 'organization_id');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, User $model): bool
    {
        return $user->hasPermissionTo('users.delete') && data_get($user, 'organization_id') === data_get($model, 'organization_id');
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
        return $user->hasPermissionTo('users.roles') && data_get($user, 'organization_id') === data_get($model, 'organization_id');
    }
}
