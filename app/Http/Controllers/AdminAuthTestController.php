<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdminAuthTestController extends Controller
{
    public function checkAuth(Request $request)
    {
        $authData = [
            'timestamp' => now()->toDateTimeString(),
            'request_url' => $request->fullUrl(),
            'session_id' => session()->getId(),
            'session_driver' => config('session.driver'),
            'auth_default_guard' => config('auth.defaults.guard'),
            'guards' => config('auth.guards'),
            
            // Check different authentication methods
            'auth_admin_check' => Auth::guard('admin')->check(),
            'auth_admin_user' => Auth::guard('admin')->user(),
            'auth_web_check' => Auth::guard('web')->check(),
            'auth_web_user' => Auth::guard('web')->user(),
            
            // Session data
            'session_all' => session()->all(),
            'session_has_admin_token' => Session::has('login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
            
            // Request headers related to authentication
            'cookies' => $request->cookies->all(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            
            // Route information
            'route_name' => $request->route() ? $request->route()->getName() : null,
            'route_middleware' => $request->route() ? $request->route()->gatherMiddleware() : [],
        ];

        return response()->json($authData, 200, [], JSON_PRETTY_PRINT);
    }
}
