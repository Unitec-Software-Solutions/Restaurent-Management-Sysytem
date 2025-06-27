<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthenticateDebug
{
    /**
     * Handle an incoming request to debug authentication issues.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only run debug in development environment
        if (config('app.debug') && $request->has('debug_auth')) {
            $debugInfo = [
                'url' => $request->fullUrl(),
                'route' => $request->route() ? $request->route()->getName() : 'No route',
                'method' => $request->method(),
                'session_id' => session()->getId(),
                'session_data' => session()->all(),
                'admin_guard_check' => Auth::guard('admin')->check(),
                'admin_user' => Auth::guard('admin')->user(),
                'web_guard_check' => Auth::guard('web')->check(),
                'web_user' => Auth::guard('web')->user(),
                'default_guard' => config('auth.defaults.guard'),
                'guards' => config('auth.guards'),
                'session_cookie' => $request->cookie(config('session.cookie')),
                'middleware' => $request->route() ? $request->route()->gatherMiddleware() : [],
            ];

            Log::info('Admin Authentication Debug', $debugInfo);
            
            if ($request->has('show_debug')) {
                return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
            }
        }

        return $next($request);
    }
}
