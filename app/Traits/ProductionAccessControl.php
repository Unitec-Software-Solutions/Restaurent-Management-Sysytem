<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait ProductionAccessControl
{
    /**
     * Check if current user is super admin
     */
    protected function isSuperAdmin($user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }

        return $user && isset($user->is_super_admin) && $user->is_super_admin;
    }

    /**
     * Apply organization filter to query if user is not super admin
     */
    protected function applyOrganizationFilter($query, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$this->isSuperAdmin($user)) {
            $query->where('organization_id', $user->organization_id);
        }

        return $query;
    }

    /**
     * Get organization ID for filtering (null for super admin = all organizations)
     */
    protected function getOrganizationId($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }

        return $this->isSuperAdmin($user) ? null : $user->organization_id;
    }

    /**
     * Check if user can access specific organization
     */
    protected function canAccessOrganization($organizationId, $user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->organization_id == $organizationId;
    }

    /**
     * Check if user can access specific branch
     */
    protected function canAccessBranch($branchId, $user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Check if branch belongs to user's organization
        $branch = \App\Models\Branch::find($branchId);
        if (!$branch || $branch->organization_id !== $user->organization_id) {
            return false;
        }

        // Organization admins (branch_id is null) can access all branches in their org
        if ($user->branch_id === null) {
            return true;
        }

        // Branch admins can only access their own branch
        return $user->branch_id === $branchId;
    }
}
