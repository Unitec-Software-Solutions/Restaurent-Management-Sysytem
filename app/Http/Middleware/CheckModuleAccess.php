<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $moduleSlug): Response
    {
        $user = $request->user();
        
        // Skip check for super admin or if no user
        if (!$user) {
            abort(403, 'Unauthorized access to this module');
        }
        
        // Check if user has permission for the module
        // Using Spatie permission system
        if ($user->can($moduleSlug) || $user->hasRole('super_admin')) {
            return $next($request);
        }
        
        // Fallback: Check role-based modules if they exist
        $role = $user->role ?? $user->userRole;
        if ($role) {
            $modulesData = $role->modules ?? [];
            
            // Handle different data types for modules
            if (is_string($modulesData)) {
                $modulesData = json_decode($modulesData, true) ?: [];
            } elseif (!is_array($modulesData)) {
                $modulesData = [];
            }
            
            $modules = collect($modulesData);
            if ($modules->contains('slug', $moduleSlug)) {
                return $next($request);
            }
        }
        
        abort(403, 'Unauthorized access to this module');
    }
}
