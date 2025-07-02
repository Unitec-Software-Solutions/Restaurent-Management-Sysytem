<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('organizations')
            ->orderBy('price', 'asc')
            ->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new subscription plan
     */
    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    /**
     * Store a newly created subscription plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_branches' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $plan = SubscriptionPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'billing_cycle' => $request->billing_cycle,
                'max_branches' => $request->max_branches,
                'max_users' => $request->max_users,
                'features' => $request->features ? json_encode($request->features) : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            Log::info('Subscription plan created', [
                'plan_id' => $plan->id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.subscription-plans.index')
                ->with('success', 'Subscription plan created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create subscription plan', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified subscription plan
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->load('organizations');
        
        $stats = [
            'total_organizations' => $subscriptionPlan->organizations()->count(),
            'active_organizations' => $subscriptionPlan->organizations()->where('is_active', true)->count(),
            'monthly_revenue' => $subscriptionPlan->billing_cycle === 'monthly' ? 
                $subscriptionPlan->organizations()->where('is_active', true)->count() * $subscriptionPlan->price : 0,
            'yearly_revenue' => $subscriptionPlan->billing_cycle === 'yearly' ? 
                $subscriptionPlan->organizations()->where('is_active', true)->count() * $subscriptionPlan->price : 0,
        ];

        return view('admin.subscription-plans.summary', compact('subscriptionPlan', 'stats'));
    }

    /**
     * Show the form for editing the specified subscription plan
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    /**
     * Update the specified subscription plan
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_branches' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $subscriptionPlan->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'billing_cycle' => $request->billing_cycle,
                'max_branches' => $request->max_branches,
                'max_users' => $request->max_users,
                'features' => $request->features ? json_encode($request->features) : null,
                'is_active' => $request->boolean('is_active'),
            ]);

            Log::info('Subscription plan updated', [
                'plan_id' => $subscriptionPlan->id,
                'updated_by' => Auth::id(),
                'changes' => $subscriptionPlan->getChanges()
            ]);

            return redirect()->route('admin.subscription-plans.index')
                ->with('success', 'Subscription plan updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update subscription plan', [
                'plan_id' => $subscriptionPlan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subscription plan
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        try {
            // Check if plan has active organizations
            $organizationCount = $subscriptionPlan->organizations()->count();
            
            if ($organizationCount > 0) {
                return redirect()->back()
                    ->with('error', "Cannot delete subscription plan with {$organizationCount} organizations. Please reassign them first.");
            }

            $planName = $subscriptionPlan->name;
            $subscriptionPlan->delete();

            Log::info('Subscription plan deleted', [
                'plan_name' => $planName,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('admin.subscription-plans.index')
                ->with('success', 'Subscription plan deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete subscription plan', [
                'plan_id' => $subscriptionPlan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete subscription plan: ' . $e->getMessage());
        }
    }
}
