<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */    public function handle(Request $request, Closure $next, string $feature = null, string $module = null): Response
    {
        $user = auth('admin')->user() ?? auth('web')->user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this feature.');
        }

        // Super admins bypass all subscription checks
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return $next($request);
        }

        if (!$user->organization) {
            return redirect()->route('login')->with('error', 'Please log in to access this feature.');
        }

        $organization = $user->organization;
        
        // Check if organization has an active subscription
        $subscription = $organization->currentSubscription;
        
        if (!$subscription || !$subscription->isActive()) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // Check specific feature access
        if ($feature && !$organization->hasFeature($feature)) {
            return redirect()->route('subscription.upgrade')
                ->with('error', "This feature requires a higher subscription tier. Please upgrade to access '{$feature}'.");
        }

        // Check module access
        if ($module && !$organization->hasModule($module)) {
            return redirect()->route('subscription.upgrade')
                ->with('error', "The '{$module}' module is not included in your current plan. Please upgrade to access this feature.");
        }

        return $next($request);
    }
}
