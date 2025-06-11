<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Organization;

class OrganizationPolicy
{
    public function viewAny(User|Admin $user): bool
    {
        return $user->is_superadmin;
    }

    public function create(User|Admin $user): bool
    {
        return $user instanceof Admin && (bool) $user->is_superadmin;
    }

    public function update(User|Admin $user, Organization $organization): bool
    {
        return $user->is_superadmin || 
               ($user->organization_id === $organization->id && $user->hasPermission('manage_organization'));
    }

    public function deactivate(User|Admin $user, Organization $organization): bool
    {
        return $user->is_superadmin;
    }
}
