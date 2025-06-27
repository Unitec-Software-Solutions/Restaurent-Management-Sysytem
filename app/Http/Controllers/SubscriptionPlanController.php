<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = SubscriptionPlan::with(['organizations', 'subscriptions'])
            ->withCount(['organizations', 'activeSubscriptions'])
            ->paginate(10);

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = Module::active()->get();
        return view('admin.subscription-plans.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans',
            'modules' => 'required|array',
            'modules.*.name' => 'required|string',
            'modules.*.tier' => 'required|in:basic,premium,enterprise',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_trial' => 'boolean',
            'trial_period_days' => 'nullable|integer|min:1',
        ]);

        try {
            $plan = DB::transaction(function () use ($validated) {
                $plan = SubscriptionPlan::create($validated);
                
                // Log plan creation
                if (function_exists('activity')) {
                    activity()
                        ->causedBy(auth('admin')->user())
                        ->performedOn($plan)
                        ->log('Created subscription plan');
                } else {
                    Log::info('Subscription plan created', [
                        'plan_id' => $plan->id,
                        'admin_id' => auth('admin')->id()
                    ]);
                }
                
                return $plan;
            });

            return redirect()
                ->route('admin.subscription-plans.index')
                ->with('success', 'Subscription plan created successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to create subscription plan', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create subscription plan. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->load([
            'organizations.branches',
            'subscriptions.organization',
            'activeSubscriptions'
        ]);

        $stats = [
            'total_revenue' => $subscriptionPlan->subscriptions()
                ->where('is_active', true)
                ->sum('amount_paid'),
            'active_organizations' => $subscriptionPlan->organizations()->count(),
            'trial_subscriptions' => $subscriptionPlan->subscriptions()
                ->where('is_trial', true)
                ->count(),
        ];

        return view('admin.subscription-plans.show', compact('subscriptionPlan', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $modules = Module::active()->get();
        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'modules' => 'required|array',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $subscriptionPlan) {
                $subscriptionPlan->update($validated);
                
                // Log plan update
                if (function_exists('activity')) {
                    activity()
                        ->causedBy(auth('admin')->user())
                        ->performedOn($subscriptionPlan)
                        ->log('Updated subscription plan');
                } else {
                    Log::info('Subscription plan updated', [
                        'plan_id' => $subscriptionPlan->id,
                        'admin_id' => auth('admin')->id()
                    ]);
                }
            });

            return redirect()
                ->route('admin.subscription-plans.show', $subscriptionPlan)
                ->with('success', 'Subscription plan updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to update subscription plan', [
                'plan_id' => $subscriptionPlan->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update subscription plan. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if ($subscriptionPlan->activeSubscriptions()->exists()) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        try {
            DB::transaction(function () use ($subscriptionPlan) {
                if (function_exists('activity')) {
                    activity()
                        ->causedBy(auth('admin')->user())
                        ->performedOn($subscriptionPlan)
                        ->log('Deleted subscription plan');
                } else {
                    Log::info('Subscription plan deleted', [
                        'plan_id' => $subscriptionPlan->id,
                        'admin_id' => auth('admin')->id()
                    ]);
                }
                
                $subscriptionPlan->delete();
            });

            return redirect()
                ->route('admin.subscription-plans.index')
                ->with('success', 'Subscription plan deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete subscription plan', [
                'plan_id' => $subscriptionPlan->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()->with('error', 'Failed to delete subscription plan. Please try again.');
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->update([
                'is_active' => !$subscriptionPlan->is_active
            ]);

            $status = $subscriptionPlan->is_active ? 'activated' : 'deactivated';
            
            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth('admin')->user())
                    ->performedOn($subscriptionPlan)
                    ->log("Plan {$status}");
            }
            
            return back()->with('success', "Plan {$status} successfully.");
            
        } catch (\Exception $e) {
            Log::error('Failed to toggle subscription plan status', [
                'plan_id' => $subscriptionPlan->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()->with('error', 'Failed to update plan status. Please try again.');
        }
    }
}
