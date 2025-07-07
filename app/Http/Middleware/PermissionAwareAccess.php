<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PermissionAwareAccess
{
    /**
     * Handle an incoming request.
     * Shows permission denied page for organizational/branch admins instead of hiding functions
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admins bypass all permission checks
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has the required permission
        if (!$this->hasPermission($admin, $permission)) {
            // Instead of blocking, show a permission denied page with information
            return response()->view('admin.errors.permission-denied', [
                'errorTitle' => 'Access Restricted',
                'errorCode' => '403',
                'errorHeading' => 'Permission Required',
                'errorMessage' => $this->getPermissionMessage($permission, $admin),
                'headerClass' => 'bg-gradient-warning',
                'errorIcon' => 'fas fa-shield-alt',
                'mainIcon' => 'fas fa-lock',
                'iconBgClass' => 'bg-orange-100',
                'iconColor' => 'text-orange-500',
                'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
                'permission' => $permission,
                'adminLevel' => $this->getAdminLevel($admin),
                'functionName' => $this->getFunctionName($permission),
                'contactInfo' => $this->getContactInfo($admin),
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if admin has permission
     */
    private function hasPermission($admin, string $permission): bool
    {
        try {
            // Try Spatie permissions first
            if (method_exists($admin, 'hasPermissionTo')) {
                return $admin->hasPermissionTo($permission);
            }

            // Try custom hasPermission method
            if (method_exists($admin, 'hasPermission')) {
                return $admin->hasPermission($permission);
            }

            // Fallback: basic organizational/branch permission logic
            return $this->checkBasicPermissions($admin, $permission);
        } catch (\Exception $e) {
            Log::error('Permission check failed', [
                'permission' => $permission,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Basic permission logic for organizational/branch admins
     */
    private function checkBasicPermissions($admin, string $permission): bool
    {
        $orgAdminPermissions = [
            'inventory.view', 'inventory.manage', 'suppliers.view', 'suppliers.manage',
            'production.view', 'production.manage', 'branches.view', 'branches.create',
            'menus.view', 'menus.create', 'orders.view', 'reservations.view',
            'users.view', 'users.create', 'reports.view', 'kitchen.view', 'settings.view'
        ];

        $branchAdminPermissions = [
            'inventory.view', 'orders.view', 'reservations.view', 'menus.view',
            'kitchen.view', 'reports.view', 'users.view'
        ];

        // Organization admin permissions
        if ($admin->organization_id && !$admin->branch_id) {
            return in_array($permission, $orgAdminPermissions);
        }

        // Branch admin permissions
        if ($admin->branch_id) {
            return in_array($permission, $branchAdminPermissions);
        }

        return false;
    }

    /**
     * Get appropriate permission message based on admin level
     */
    private function getPermissionMessage(string $permission, $admin): string
    {
        $adminLevel = $this->getAdminLevel($admin);
        $functionName = $this->getFunctionName($permission);

        $messages = [
            'org_admin' => "As an Organization Administrator, you can see this function ({$functionName}) but need additional permissions to use it. Contact the Super Administrator to request access.",
            'branch_admin' => "As a Branch Administrator, you can see this function ({$functionName}) but it's restricted to higher access levels. Contact your Organization Administrator to request access.",
            'staff' => "This function ({$functionName}) requires administrative privileges. Contact your supervisor or administrator for assistance."
        ];

        return $messages[$adminLevel] ?? "You don't have permission to access this function ({$functionName}). Contact your administrator for access.";
    }

    /**
     * Get admin level
     */
    private function getAdminLevel($admin): string
    {
        if ($admin->organization_id && !$admin->branch_id) {
            return 'org_admin';
        }

        if ($admin->branch_id) {
            return 'branch_admin';
        }

        return 'staff';
    }

    /**
     * Get human-readable function name from permission
     */
    private function getFunctionName(string $permission): string
    {
        $functionNames = [
            'organizations.view' => 'Organization Management',
            'organizations.create' => 'Create Organizations',
            'branches.view' => 'Branch Management',
            'branches.create' => 'Create Branches',
            'users.view' => 'User Management',
            'users.create' => 'Create Users',
            'inventory.view' => 'Inventory Overview',
            'inventory.manage' => 'Inventory Management',
            'suppliers.view' => 'Supplier Management',
            'production.view' => 'Production Management',
            'menus.view' => 'Menu Management',
            'orders.view' => 'Order Management',
            'reservations.view' => 'Reservation Management',
            'reports.view' => 'Reports & Analytics',
            'kitchen.view' => 'Kitchen Operations',
            'settings.view' => 'System Settings',
            'roles.view' => 'Roles & Permissions',
            'subscription.view' => 'Subscription Management',
        ];

        return $functionNames[$permission] ?? ucfirst(str_replace(['_', '.'], ' ', $permission));
    }

    /**
     * Get contact information based on admin level
     */
    private function getContactInfo($admin): array
    {
        $adminLevel = $this->getAdminLevel($admin);

        return match($adminLevel) {
            'org_admin' => [
                'contact_person' => 'Super Administrator',
                'contact_method' => 'System Support',
                'escalation_note' => 'You can request permission upgrades through the system support team.'
            ],
            'branch_admin' => [
                'contact_person' => 'Organization Administrator',
                'contact_method' => 'Internal Request',
                'escalation_note' => 'Contact your organization administrator to discuss access requirements.'
            ],
            'staff' => [
                'contact_person' => 'Branch Manager or Administrator',
                'contact_method' => 'Direct Supervisor',
                'escalation_note' => 'Speak with your direct supervisor about accessing administrative functions.'
            ],
            default => [
                'contact_person' => 'System Administrator',
                'contact_method' => 'Support Team',
                'escalation_note' => 'Contact support for assistance with permissions.'
            ]
        };
    }
}
