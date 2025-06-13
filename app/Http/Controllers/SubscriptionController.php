<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        // Return a view or data for subscriptions
        return view('subscriptions.index');
    }

    public function store(Request $request)
    {
        $now = now();
        $terminatedAt = $now->copy()->addYear();

        $subscription = Subscription::create([
            'subscription_id'    => Str::uuid(),
            'organization_id'    => $request->organization_id,
            'branch_id'          => $request->branch_id ?? null,
            'plan_id'            => $request->plan_id,
            'status'             => 'active',
            'start_date'         => $now,
            'activated_at'       => $now,
            'terminated_at'      => $terminatedAt,
            'next_billing_date'  => $now->copy()->addMonth(),
            'payment_method_id'  => $request->payment_method_id,
            'gateway_sub_id'     => $request->gateway_sub_id,
            'currency'           => $request->currency,
            'amount'             => $request->amount,
            'discount_id'        => $request->discount_id,
        ]);

        
    }

   
}
