<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Organization;

class BranchPolicy
{
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
        return $user->organization_id === $branch->organization_id;
    }

    public function update(Admin $admin, Branch $branch)
    {
        return $admin->organization_id === $branch->organization_id;
    }

    public function delete($user, $branch)
    {
        // Allow super admin
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        // Allow org admin for their own branches
        return $user->organization_id === $branch->organization_id;
    }

    public function create($user, Organization $organization)
    {
        // Allow super admin
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return true;
        }
        // Allow org admin for their own org
        return $user->organization_id === $organization->id;
    }
}