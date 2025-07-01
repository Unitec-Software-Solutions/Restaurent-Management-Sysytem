<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware should only be used for guest routes to redirect authenticated users.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // This middleware is for redirecting authenticated users away from guest routes (like login)
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($guard === 'admin') {
                    return redirect()->route('admin.dashboard');
                }
                return redirect('/home');
            }
        }

        // If not authenticated, continue to the requested route
        return $next($request);
    }
}