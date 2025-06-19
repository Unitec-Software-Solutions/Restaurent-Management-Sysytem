<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $role = $request->route('role');

        // Allow super admins to proceed
        if (method_exists($user, 'is_superadmin') && $user->is_superadmin()) {
            return $next($request);
        }

        // Organization admin: can only manage roles in their organization
        if (method_exists($user, 'is_org_admin') && $user->is_org_admin()) {
            if ($role->organization_id !== $user->organization_id) {
                abort(403, 'Unauthorized action: This role belongs to another organization');
            }
            return $next($request);
        }

        // Branch admin: can only manage branch roles in their branch
        if (method_exists($user, 'is_branch_admin') && $user->is_branch_admin()) {
            if ($role->scope !== 'branch') {
                abort(403, 'Branch admins can only manage branch-scoped roles');
            }
            if ($role->branch_id !== $user->branch_id) {
                abort(403, 'Unauthorized action: This role belongs to another branch');
            }
            return $next($request);
        }

        // Default: deny access
        abort(403, 'Unauthorized action');
    }
}
