<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class RestaurantRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null, string $role = null): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this resource.');
        }

        $user = Auth::user();

        // Get the employee record for the authenticated user
        $employee = Employee::where('email', $user->email)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Check specific permission if provided
        if ($permission && !$employee->can($permission)) {
            abort(403, "You don't have the required permission: {$permission}");
        }

        // Check specific role if provided
        if ($role && !$employee->hasRole($role)) {
            abort(403, "You don't have the required role: {$role}");
        }

        // If no specific permission or role is provided, just ensure the user has an employee record
        return $next($request);
    }

    /**
     * Check if employee has any of the specified roles
     */
    public static function hasAnyRole(Employee $employee, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($employee->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if employee has all of the specified permissions
     */
    public static function hasAllPermissions(Employee $employee, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$employee->can($permission)) {
                return false;
            }
        }
        return true;
    }
}
