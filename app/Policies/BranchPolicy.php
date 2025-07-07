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

    public function regenerateKey(Admin $admin, Branch $branch)
    {
        // Only super admins can regenerate activation keys
        return $admin->is_super_admin;
    }

    public function delete($user, $branch)
    {
        // Only super admins can delete branches
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            // Only allow deletion of inactive branches
            return !$branch->is_active;
        }
        
        // Organization admins and branch admins cannot delete branches
        return false;
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

    public function activate(Admin $admin, Branch $branch)
    {
        // Super admins can activate any branch
        if ($admin->is_super_admin) {
            return true;
        }
        
        // Organization admins can activate branches in their organization
        if ($admin->isOrganizationAdmin() && $admin->organization_id === $branch->organization_id) {
            return true;
        }
        
        // Branch admins can activate their own branch
        if ($admin->isBranchAdmin() && $admin->branch_id === $branch->id) {
            return true;
        }
        
        return false;
    }

    public function deactivate(Admin $admin, Branch $branch)
    {
        // Only super admins and org admins can deactivate branches
        if ($admin->is_super_admin) {
            return true;
        }
        
        if ($admin->isOrganizationAdmin() && $admin->organization_id === $branch->organization_id) {
            return true;
        }
        
        return false;
    }
}