<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = \App\Models\SubscriptionPlan::all();
        return view('admin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = \App\Models\Module::all();
        return view('admin.subscription-plans.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'currency'    => 'required|string|max:10',
            'description' => 'nullable|string',
            'is_trial'    => 'nullable|boolean',
            'trial_period_days' => 'nullable|integer|min:1|max:365',
        ]);

        // modules is now an array from checkboxes
        $validated['modules'] = json_encode($request->input('modules', []));
        $validated['is_trial'] = $request->has('is_trial') ? 1 : 0;
        $validated['trial_period_days'] = $request->input('trial_period_days', 30);

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.summary', compact('subscriptionPlan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        // Convert modules string to array
        $modules = array_map('trim', explode(',', $request->input('modules')));

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'currency'    => 'required|string|max:10',
            'description' => 'nullable|string',
            'is_trial'    => 'nullable|boolean',
            'trial_period_days' => 'nullable|integer|min:1|max:365',
        ]);

        $validated['modules'] = json_encode($modules);
        $validated['is_trial'] = $request->has('is_trial') ? 1 : 0;
        $validated['trial_period_days'] = $request->input('trial_period_days', 30);

        $subscriptionPlan->update($validated);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan deleted.');
    }
}
