<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && $user->organization && !$user->organization->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'account' => 'Your organization\'s subscription or trial has expired. Please contact support.'
            ]);
        }
        return $next($request);
    }
}
