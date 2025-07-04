<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Authenticatable;

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
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            Log::warning('Admin authentication failed', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'Please log in to access this resource'
                ], 401);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin area.');
        }

        /** @var Authenticatable $admin */
        $admin = Auth::guard('admin')->user();

        // Validate admin account
        if (!$admin instanceof \App\Models\Admin) {
            Auth::guard('admin')->logout();
            Log::error('Invalid admin type detected');

            return redirect()->route('admin.login')
                ->with('error', 'Invalid authentication. Please log in again.');
        }

        // Check if admin is active
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            Log::warning('Inactive admin attempted access', ['admin_id' => $admin->id]);

            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated. Please contact support.');
        }        // Check organization assignment (but allow super admins without organization)
        $isSuperAdmin = $admin->isSuperAdmin();
        if (!$admin->organization_id && !$isSuperAdmin) {
            Auth::guard('admin')->logout();
            Log::warning('Admin without organization attempted access', ['admin_id' => $admin->id]);

            return redirect()->route('admin.login')
                ->with('error', 'Account setup incomplete. Please contact support.');
        }

        // Store admin info in request for easy access
        $request->merge(['authenticated_admin' => $admin]);

        return $next($request);
    }
}
