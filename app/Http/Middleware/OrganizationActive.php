<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OrganizationActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Super admins bypass organization checks
        if ($user && isset($user->is_super_admin) && $user->is_super_admin) {
            return $next($request);
        }

        $organization = $request->route('organization') ?? ($user ? $user->organization : null);
        
        if (!$organization || !$organization->is_active) {
            return redirect()->route('admin.dashboard')->withErrors(['message' => 'Organization subscription is inactive or expired']);
        }
        return $next($request);
    }
}
