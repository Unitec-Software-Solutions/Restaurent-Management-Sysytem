<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionExpiryAlert
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && $user->organization) {
            $subscription = $user->organization->subscriptions()
                ->where('is_active', true)
                ->latest('end_date')
                ->first();

            if ($subscription) {
                $now = Carbon::now();
                $end = Carbon::parse($subscription->end_date);

                if ($subscription->is_trial) {
                    // Alert 7 days before trial ends
                    if ($end->diffInDays($now, false) <= 7 && $end->isFuture()) {
                        session()->flash('subscription_alert',
                            "Your trial will end on {$end->toFormattedDateString()}. Please upgrade to a paid plan to continue using the system.");
                    }
                } else {
                    // Alert 30 days before paid subscription ends
                    if ($end->diffInDays($now, false) <= 30 && $end->isFuture()) {
                        session()->flash('subscription_alert',
                            "Your subscription will end on {$end->toFormattedDateString()}. Please renew to avoid interruption.");
                    }
                }
            }
        }
        return $next($request);
    }
}
