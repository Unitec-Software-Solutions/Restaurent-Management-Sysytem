<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::all();
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function store(Request $request)
    {
        $now = now();
        $terminatedAt = $now->copy()->addYear();

        $subscription = Subscription::create([
            'discount_id'        => $request->discount_id,
        ]);
    }

    public function edit($id)
    {
        // ...existing code...
    }

    public function update(Request $request, Subscription $subscription)
    {
        // ...existing code...
    }

    public function create()
    {
        // ...existing code...
    }
}
