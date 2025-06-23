<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EnhancedAdminAuth
{
    /**
     * Handle an incoming request with enhanced admin authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {        // Debug logging if enabled
        if (config('app.debug')) {
            Log::info('Enhanced Admin Auth Check', [
                'url' => $request->fullUrl(),
                'session_id' => session()->getId(),
                'admin_check' => Auth::guard('admin')->check(),
                'session_has_admin' => Session::has('login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
            ]);
        }

        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            // Clear any stale session data
            Session::forget('login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d');
            
            // Log the failed authentication attempt
            Log::warning('Admin authentication failed', [
                'url' => $request->fullUrl(),
                'session_id' => session()->getId(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin area.');
        }

        // Verify session integrity
        $sessionKey = 'login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d';
        if (!Session::has($sessionKey)) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->with('error', 'Your session has expired. Please log in again.');
        }

        return $next($request);
    }
}
