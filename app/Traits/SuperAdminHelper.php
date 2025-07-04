<?php
// filepath: d:\unitec\Restaurent-Management-Sysytem\app\Traits\SuperAdminHelper.php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait SuperAdminHelper
{
    /**
     * Check if the current admin is a super admin
     */
    protected function isSuperAdmin($admin = null): bool
    {
        if (!$admin) {
            $admin = Auth::guard('admin')->user();
        }
        
        if (!$admin) {
            return false;
        }
        
        // Primary check: is_super_admin column
        if (isset($admin->is_super_admin) && $admin->is_super_admin) {
            return true;
        }
        
        // Secondary check: isSuperAdmin method if available
        if (method_exists($admin, 'isSuperAdmin')) {
            try {
                return $admin->isSuperAdmin();
            } catch (\Exception $e) {
                Log::warning('isSuperAdmin method failed', ['error' => $e->getMessage()]);
            }
        }
        
        return false;
    }

    /**
     * Check if admin has permission (with super admin bypass)
     */
    protected function hasPermissionSafe($permission, $admin = null): bool
    {
        if (!$admin) {
            $admin = Auth::guard('admin')->user();
        }
        
        if (!$admin) {
            return false;
        }
        
        // Super admins bypass all permission checks
        if ($this->isSuperAdmin($admin)) {
            return true;
        }
        
        try {
            // Check using Spatie permissions if available
            if (method_exists($admin, 'hasPermissionTo')) {
                return $admin->hasPermissionTo($permission, 'admin');
            }
            
            // Fallback to basic permission check
            if (method_exists($admin, 'hasPermission')) {
                return $admin->hasPermission($permission);
            }
            
            return false;
        } catch (\Exception $e) {
            Log::warning('Permission check failed', [
                'permission' => $permission,
                'admin_id' => $admin->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get organization ID for current admin (null for super admin = access all)
     */
    protected function getOrganizationIdSafe($admin = null)
    {
        if (!$admin) {
            $admin = Auth::guard('admin')->user();
        }
        
        if (!$admin) {
            return null;
        }
        
        // Super admins can access all organizations
        if ($this->isSuperAdmin($admin)) {
            return null;
        }
        
        return $admin->organization_id;
    }

    /**
     * Check if admin can access specific organization
     */
    protected function canAccessOrganization($organizationId, $admin = null): bool
    {
        if (!$admin) {
            $admin = Auth::guard('admin')->user();
        }
        
        if (!$admin) {
            return false;
        }
        
        // Super admins can access all organizations
        if ($this->isSuperAdmin($admin)) {
            return true;
        }
        
        return $admin->organization_id == $organizationId;
    }

    /**
     * Apply organization filter to query
     */
    protected function applyOrganizationFilter($query, $admin = null)
    {
        $orgId = $this->getOrganizationIdSafe($admin);
        
        // If orgId is null (super admin), don't filter
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }
        
        return $query;
    }
}