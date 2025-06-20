<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null, string $module = null): Response
    {
        $user = Auth::user();
        
        if (!$user || !$user->organization) {
            return redirect()->route('login')->with('error', 'Please log in to access this feature.');
        }

        $organization = $user->organization;
        $subscription = $organization->currentSubscription;

        if (!$subscription || !$subscription->is_active) {
            return redirect()->route('subscription.required')
                ->with('error', 'Active subscription required to access this feature.');
        }

        // Check feature access
        if ($feature && !$subscription->hasFeature($feature)) {
            return $this->handleFeatureRestriction($request, $feature, $subscription->plan->name);
        }

        // Check module access and tier
        if ($module) {
            if (!$subscription->hasModule($module)) {
                return $this->handleModuleRestriction($request, $module, $subscription->plan->name);
            }

            // Store module tier in request for controllers to use
            $moduleTier = $subscription->getModuleTier($module);
            $request->attributes->set('module_tier', $moduleTier);
        }

        // Check branch limitations
        if ($this->isExceedingBranchLimit($organization, $subscription)) {
            return $this->handleBranchLimitExceeded($request, $subscription->plan->max_branches);
        }

        // Check employee limitations
        if ($this->isExceedingEmployeeLimit($organization, $subscription)) {
            return $this->handleEmployeeLimitExceeded($request, $subscription->plan->max_employees);
        }

        return $next($request);
    }

    private function handleFeatureRestriction(Request $request, string $feature, string $planName): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "The '{$feature}' feature is not available in your {$planName} plan.",
                'upgrade_required' => true
            ], 403);
        }

        return redirect()->back()->with('error', 
            "The '{$feature}' feature is not available in your {$planName} plan. Please upgrade your subscription."
        );
    }

    private function handleModuleRestriction(Request $request, string $module, string $planName): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Module not available',
                'message' => "The '{$module}' module is not available in your {$planName} plan.",
                'upgrade_required' => true
            ], 403);
        }

        return redirect()->back()->with('error', 
            "The '{$module}' module is not available in your {$planName} plan. Please upgrade your subscription."
        );
    }

    private function handleBranchLimitExceeded(Request $request, int $maxBranches): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Branch limit exceeded',
                'message' => "Your plan allows a maximum of {$maxBranches} branches.",
                'upgrade_required' => true
            ], 403);
        }

        return redirect()->back()->with('error', 
            "Your plan allows a maximum of {$maxBranches} branches. Please upgrade to add more branches."
        );
    }

    private function handleEmployeeLimitExceeded(Request $request, int $maxEmployees): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Employee limit exceeded',
                'message' => "Your plan allows a maximum of {$maxEmployees} employees.",
                'upgrade_required' => true
            ], 403);
        }

        return redirect()->back()->with('error', 
            "Your plan allows a maximum of {$maxEmployees} employees. Please upgrade to add more employees."
        );
    }

    private function isExceedingBranchLimit($organization, $subscription): bool
    {
        $maxBranches = $subscription->plan->max_branches ?? 999;
        $currentBranches = $organization->branches()->count();
        
        return $currentBranches > $maxBranches;
    }

    private function isExceedingEmployeeLimit($organization, $subscription): bool
    {
        $maxEmployees = $subscription->plan->max_employees ?? 999;
        $currentEmployees = $organization->employees()->count();
        
        return $currentEmployees > $maxEmployees;
    }
}
