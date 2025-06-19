<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OrganizationActive
{
    public function handle(Request $request, Closure $next)
    {
        $organization = $request->route('organization') ?? $request->user()->organization;
        if (!$organization || !$organization->is_active) {
            return redirect()->route('admin.dashboard')->withErrors(['message' => 'Organization subscription is inactive or expired']);
        }
        return $next($request);
    }
}
