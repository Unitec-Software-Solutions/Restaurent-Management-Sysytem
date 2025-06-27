<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount(['organizations', 'activeSubscriptions'])
            ->orderBy('price')
            ->get();
        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        $modules = Module::active()->get();
        return view('admin.subscription-plans.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'modules' => 'required|array',
            'modules.*' => 'exists:modules,id',
            'description' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'trial_period_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array'
        ]);

        $subscriptionPlan = SubscriptionPlan::create([
            'name' => $request->name,
            'price' => $request->price,
            'modules' => $request->modules,
            'description' => $request->description,
            'currency' => $request->currency ?? 'USD',
            'max_branches' => $request->max_branches,
            'max_employees' => $request->max_employees,
            'trial_period_days' => $request->trial_period_days,
            'features' => $request->features ?? [],
            'is_trial' => $request->has('is_trial'),
            'is_active' => $request->has('is_active') ? true : true // Default to active
        ]);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully');
    }

    public function show($id)
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription-plans.show', compact('subscriptionPlan'));
    }

    public function edit($id)
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        $modules = Module::active()->get();
        return view('admin.subscription-plans.edit', compact('subscriptionPlan', 'modules'));
    }

    public function update(Request $request, $id)
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'modules' => 'required|array',
            'modules.*' => 'exists:modules,id',
            'description' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'max_branches' => 'nullable|integer|min:1',
            'max_employees' => 'nullable|integer|min:1',
            'trial_period_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array'
        ]);

        $subscriptionPlan->update([
            'name' => $request->name,
            'price' => $request->price,
            'modules' => $request->modules,
            'description' => $request->description,
            'currency' => $request->currency ?? 'USD',
            'max_branches' => $request->max_branches,
            'max_employees' => $request->max_employees,
            'trial_period_days' => $request->trial_period_days,
            'features' => $request->features ?? [],
            'is_trial' => $request->has('is_trial')
        ]);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully');
    }

    public function destroy($id)
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        $subscriptionPlan->delete();
        
        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan deleted successfully');
    }
}
