<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function takeawayIndex()
    {
        $orders = Order::with(['branch', 'items'])
            ->whereIn('order_type', [
                'takeaway_online_scheduled',
                'takeaway_walk_in_demand',
                'takeaway_in_call_scheduled'
            ])
            ->latest()
            ->paginate(10);

        return view('admin.orders.takeaway-index', [
            'orders' => $orders,
            'branches' => \App\Models\Branch::all()
        ]);
    }

    public function takeawayBranch(Branch $branch)
    {
        $orders = Order::with(['items'])
            ->where('branch_id', $branch->id)
            ->whereIn('order_type', [
                'takeaway_online_scheduled',
                'takeaway_walk_in_demand',
                'takeaway_in_call_scheduled'
            ])
            ->latest()
            ->paginate(10);

        return view('admin.orders.takeaway-branch', [
            'orders' => $orders,
            'branch' => $branch
        ]);
    }
}
