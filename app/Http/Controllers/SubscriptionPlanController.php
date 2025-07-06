<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Module;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('organizations')
            ->orderBy('price', 'asc')
            ->paginate(15);

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new subscription plan
     */
    public function create()
    {
        // For create/edit forms - fetch available modules
        $modules = Module::active()->get();

        return view('admin.subscription-plans.create', compact('modules'));
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
            'currency' => 'required|string|size:3',
            'modules' => 'nullable|array',
            'modules.*' => 'integer|exists:modules,id',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'is_trial' => 'boolean',
            'trial_period_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'boolean',
        ]);

        try {
            $plan = SubscriptionPlan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price, // Store price as entered
                'currency' => $request->currency,
                'modules' => $request->modules,
                'max_branches' => $request->max_branches,
                'max_employees' => $request->max_employees,
                'is_trial' => $request->boolean('is_trial', false),
                'trial_period_days' => $request->trial_period_days,
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
            'total_revenue' => $subscriptionPlan->organizations()->where('is_active', true)->count() * $subscriptionPlan->price,
        ];

        return view('admin.subscription-plans.summary', compact('subscriptionPlan', 'stats'));
    }

    /**
     * Show the form for editing the specified subscription plan
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        // For create/edit forms - fetch available modules
        $modules = Module::active()->get();

        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'modules'));
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
            'currency' => 'required|string|size:3',
            'modules' => 'nullable|array',
            'modules.*' => 'integer|exists:modules,id',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'is_trial' => 'boolean',
            'trial_period_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'boolean',
        ]);

        try {
            $subscriptionPlan->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price, // Store price as entered
                'currency' => $request->currency,
                'modules' => $request->modules,
                'max_branches' => $request->max_branches,
                'max_employees' => $request->max_employees,
                'is_trial' => $request->boolean('is_trial', false),
                'trial_period_days' => $request->trial_period_days,
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
