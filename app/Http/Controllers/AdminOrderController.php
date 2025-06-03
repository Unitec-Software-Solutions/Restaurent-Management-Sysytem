<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['branch', 'items'])
            ->where('status', 'submitted')
            ->latest()
            ->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', [
            'order' => $order,
            'statusOptions' => [
                Order::STATUS_SUBMITTED => 'Submitted',
                Order::STATUS_PREPARING => 'Preparing',
                Order::STATUS_READY => 'Ready',
                Order::STATUS_COMPLETED => 'Completed',
                Order::STATUS_CANCELLED => 'Cancelled'
            ]
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled'
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated!');
    }

    public function branchOrders(Branch $branch)
    {
        $orders = Order::with(['items'])
            ->where('branch_id', $branch->id)
            ->where('status', 'submitted')
            ->latest()
            ->paginate(10);

        return view('admin.orders.branch', compact('orders', 'branch'));
    }
}
