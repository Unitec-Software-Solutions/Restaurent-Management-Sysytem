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
    public function handle(Request $request, Closure $next, $module): Response
    {
        $user = $request->user();
        if (!$user || !$user->role || !$user->role->modules->contains('name', $module)) {
            abort(403, 'Unauthorized access to this module');
        }
        return $next($request);
    }
}
