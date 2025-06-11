<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OrganizationActive
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->organization->is_active) {
            abort(403, 'Organization is inactive.');
        }
        return $next($request);
    }
}
