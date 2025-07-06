<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Organization;

class OrganizationPolicy
{
    public function viewAny(User|Admin $user): bool
    {
        return $user->is_super_admin;
    }

    public function create(User|Admin $user): bool
    {
        return $user instanceof Admin && (bool) $user->is_super_admin;
    }

    public function update(User|Admin $user, Organization $organization): bool
    {
        return $user->is_super_admin || ($user->organization_id === $organization->id && $user->hasPermission('manage_organization'));
    }

    public function activate(User|Admin $user, Organization $organization): bool
    {
        // Super admins can activate any organization
        if ($user->is_super_admin) {
            return true;
        }
        
        // Organization admins can activate their own organization
        if ($user->organization_id === $organization->id) {
            return true;
        }
        
        return false;
    }

    public function deactivate(User|Admin $user, Organization $organization): bool
    {
        return $user->is_super_admin;
    }
    
    public function delete($user, Organization $organization)
    {
        // Only super admins can delete organizations
        if ($user->is_super_admin) {
            // Only allow deletion of inactive organizations
            return !$organization->is_active;
        }
        
        return false;
    }

    public function regenerateKey(User|Admin $user, Organization $organization): bool
    {
        // Only super admins can regenerate organization activation keys
        return $user->is_super_admin;
    }
}
