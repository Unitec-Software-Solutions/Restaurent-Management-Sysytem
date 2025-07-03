<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2: Scope-Based Permission Middleware
 * Implements organization/branch/staff level access controls
 */
class ScopeBasedPermission
{
    /**
     * Handle scope-limited access controls
     */
    public function handle(Request $request, Closure $next, string $permission, string $scope = 'auto')
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Authentication required');
        }

        // Super admins bypass all scope restrictions
        if ($admin->is_super_admin) {
            return $next($request);
        }

        // Determine scope automatically if not specified
        if ($scope === 'auto') {
            $scope = $this->determineScope($admin);
        }

        // Apply scope-based permission checks
        if (!$this->hasPermissionInScope($admin, $permission, $scope, $request)) {
            abort(403, 'Insufficient permissions for this scope');
        }

        return $next($request);
    }

    /**
     * Determine user scope based on their role and assignments
     */
    private function determineScope($admin): string
    {
        if ($admin->branch_id && !$admin->organization_id) {
            return 'branch';
        }
        
        if ($admin->organization_id && !$admin->branch_id) {
            return 'organization';
        }
        
        // Check role-based scope
        if ($admin->hasRole('Branch Admin') || $admin->hasRole('Branch Manager')) {
            return 'branch';
        }
        
        if ($admin->hasRole('Admin') || $admin->hasRole('Organization Admin')) {
            return 'organization';
        }
        
        return 'staff';
    }

    /**
     * Check if admin has permission within their scope
     */
    private function hasPermissionInScope($admin, string $permission, string $scope, Request $request): bool
    {
        switch ($scope) {
            case 'organization':
                return $this->checkOrganizationScope($admin, $permission, $request);
            
            case 'branch':
                return $this->checkBranchScope($admin, $permission, $request);
            
            case 'staff':
                return $this->checkStaffScope($admin, $permission, $request);
            
            default:
                return false;
        }
    }

    /**
     * Organization Admin: Organization-only access
     */
    private function checkOrganizationScope($admin, string $permission, Request $request): bool
    {
        // Must have organization assignment
        if (!$admin->organization_id) {
            return false;
        }

        // Check basic permission
        if (!$admin->hasPermission($permission)) {
            return false;
        }

        // Validate resource belongs to their organization
        return $this->validateOrganizationResource($admin, $request);
    }

    /**
     * Branch Admin: Branch-only access
     */
    private function checkBranchScope($admin, string $permission, Request $request): bool
    {
        // Must have branch assignment
        if (!$admin->branch_id) {
            return false;
        }

        // Check basic permission
        if (!$admin->hasPermission($permission)) {
            return false;
        }

        // Validate resource belongs to their branch
        return $this->validateBranchResource($admin, $request);
    }

    /**
     * Staff: Task-specific permissions only
     */
    private function checkStaffScope($admin, string $permission, Request $request): bool
    {
        // Staff can only access specific task-related permissions
        $allowedStaffPermissions = [
            'view_menu',
            'create_orders',
            'view_assigned_orders',
            'update_order_status',
            'view_kitchen_orders',
            'manage_assigned_tasks',
            'view_public_menu',
            'create_guest_orders'
        ];

        if (!in_array($permission, $allowedStaffPermissions)) {
            return false;
        }

        return $admin->hasPermission($permission);
    }

    /**
     * Validate that requested resource belongs to admin's organization
     */
    private function validateOrganizationResource($admin, Request $request): bool
    {
        // Get resource IDs from route parameters
        $resourceIds = $this->extractResourceIds($request);
        
        foreach ($resourceIds as $type => $id) {
            if (!$this->resourceBelongsToOrganization($type, $id, $admin->organization_id)) {
                Log::warning('Organization scope violation', [
                    'admin_id' => $admin->id,
                    'resource_type' => $type,
                    'resource_id' => $id,
                    'organization_id' => $admin->organization_id
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that requested resource belongs to admin's branch
     */
    private function validateBranchResource($admin, Request $request): bool
    {
        // Get resource IDs from route parameters
        $resourceIds = $this->extractResourceIds($request);
        
        foreach ($resourceIds as $type => $id) {
            if (!$this->resourceBelongsToBranch($type, $id, $admin->branch_id)) {
                Log::warning('Branch scope violation', [
                    'admin_id' => $admin->id,
                    'rereference_type' => $type,
                    'resource_id' => $id,
                    'branch_id' => $admin->branch_id
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Extract resource IDs from route parameters
     */
    private function extractResourceIds(Request $request): array
    {
        $route = $request->route();
        $parameters = $route ? $route->parameters() : [];
        $resourceIds = [];

        // Map route parameters to resource types
        $mappings = [
            'organization' => 'organization',
            'branch' => 'branch',
            'order' => 'order',
            'reservation' => 'reservation',
            'menu' => 'menu',
            'user' => 'user',
            'role' => 'role'
        ];

        foreach ($mappings as $param => $type) {
            if (isset($parameters[$param])) {
                $resource = $parameters[$param];
                $resourceIds[$type] = is_object($resource) ? $resource->id : $resource;
            }
        }

        return $resourceIds;
    }

    /**
     * Check if resource belongs to organization
     */
    private function resourceBelongsToOrganization(string $type, $id, int $organizationId): bool
    {
        switch ($type) {
            case 'organization':
                return $id == $organizationId;
            
            case 'branch':
                $branch = \App\Models\Branch::find($id);
                return $branch && $branch->organization_id == $organizationId;
            
            case 'order':
                $order = \App\Models\Order::find($id);
                return $order && $order->branch && $order->branch->organization_id == $organizationId;
            
            case 'reservation':
                $reservation = \App\Models\Reservation::find($id);
                return $reservation && $reservation->branch && $reservation->branch->organization_id == $organizationId;
            
            case 'menu':
                $menu = \App\Models\Menu::find($id);
                return $menu && $menu->branch && $menu->branch->organization_id == $organizationId;
            
            case 'user':
                $user = \App\Models\Admin::find($id);
                return $user && $user->organization_id == $organizationId;
            
            case 'role':
                $role = \App\Models\Role::find($id);
                return $role && $role->organization_id == $organizationId;
            
            default:
                return false;
        }
    }

    /**
     * Check if resource belongs to branch
     */
    private function resourceBelongsToBranch(string $type, $id, int $branchId): bool
    {
        switch ($type) {
            case 'branch':
                return $id == $branchId;
            
            case 'order':
                $order = \App\Models\Order::find($id);
                return $order && $order->branch_id == $branchId;
            
            case 'reservation':
                $reservation = \App\Models\Reservation::find($id);
                return $reservation && $reservation->branch_id == $branchId;
            
            case 'menu':
                $menu = \App\Models\Menu::find($id);
                return $menu && $menu->branch_id == $branchId;
            
            case 'user':
                $user = \App\Models\Admin::find($id);
                return $user && $user->branch_id == $branchId;
            
            case 'role':
                $role = \App\Models\Role::find($id);
                return $role && $role->branch_id == $branchId;
            
            default:
                return false;
        }
    }
}
