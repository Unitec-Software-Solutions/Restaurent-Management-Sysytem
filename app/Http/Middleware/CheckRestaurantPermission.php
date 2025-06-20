<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRestaurantPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has the specific permission
        if (!$user->can($permission)) {
            // Check if user is an employee with restaurant role
            if ($user instanceof \App\Models\Employee) {
                // Additional restaurant-specific permission checks
                if (!$this->hasRestaurantPermission($user, $permission)) {
                    abort(403, 'You do not have permission to perform this action.');
                }
            } else {
                abort(403, 'You do not have permission to perform this action.');
            }
        }

        return $next($request);
    }

    /**
     * Check restaurant-specific permissions
     */
    private function hasRestaurantPermission($employee, $permission): bool
    {
        // Map permissions to restaurant role capabilities
        $rolePermissions = [
            'host/hostess' => [
                'manage-reservations',
                'view-restaurant-layout',
                'view-table-status',
                'customer-service',
                'view-waitlist'
            ],
            'servers' => [
                'take-orders',
                'modify-orders',
                'view-menu',
                'process-payments',
                'customer-service',
                'view-table-assignments'
            ],
            'bartenders' => [
                'manage-bar-inventory',
                'prepare-beverages',
                'view-bar-orders',
                'cash-handling',
                'view-menu'
            ],
            'cashiers' => [
                'process-payments',
                'handle-refunds',
                'cash-handling',
                'print-receipts',
                'view-sales-reports'
            ],
            'chefs' => [
                'view-kitchen-orders',
                'update-order-status',
                'manage-kitchen-inventory',
                'view-recipes',
                'kitchen-operations'
            ],
            'dishwashers' => [
                'kitchen-support',
                'equipment-maintenance',
                'view-cleaning-schedule'
            ],
            'kitchen-managers' => [
                'manage-kitchen-staff',
                'kitchen-operations',
                'manage-kitchen-inventory',
                'view-kitchen-reports',
                'approve-menu-changes',
                'schedule-management'
            ]
        ];

        // Get employee's restaurant role
        $employeeRole = $employee->employeeRole?->name;
        
        if (!$employeeRole || !isset($rolePermissions[$employeeRole])) {
            return false;
        }

        return in_array($permission, $rolePermissions[$employeeRole]);
    }
}
