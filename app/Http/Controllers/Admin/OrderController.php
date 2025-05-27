<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ItemMaster;

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

    public function createTakeaway()
    {
        return view('orders.takeaway.create', [
            'menu_items' => ItemMaster::where('is_menu_item', true)->get()
        ]);
    }

    public function storeTakeaway(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'customer_name' => 'required',
                'items' => 'required|array',
                // ...other fields...
            ]);
            $order = Order::create([
                'order_type' => 'takeaway',
                'status' => 'pending',
                // ...other fields...
            ]);
            foreach ($validated['items'] as $item) {
                $order->items()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                    'price' => ItemMaster::find($item['id'])->selling_price
                ]);
            }
            DB::commit();
            return redirect()->route('orders.summary', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Order failed: ' . $e->getMessage()]);
        }
    }
}
