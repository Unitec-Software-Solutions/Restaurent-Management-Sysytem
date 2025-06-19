<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOrganizationStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->organization && !$user->organization->is_active) {
            return response()->json(['message' => 'Organization subscription expired'], 403);
        }
        return $next($request);
    }
}
