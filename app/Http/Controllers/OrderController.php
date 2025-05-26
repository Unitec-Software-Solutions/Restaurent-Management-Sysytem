<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemMaster;
use App\Models\Reservation;
use App\Models\Branch;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
{
    // List all orders for a reservation (dine-in)
    public function index(Request $request)
{
    $phone = $request->input('phone');
    $reservationId = $request->input('reservation_id');

    // Active reservations (reservation time is in the future)
    $activeReservations = Reservation::when($phone, function ($query) use ($phone) {
            return $query->where('phone', $phone);
        })
        ->whereRaw('(date > ? OR (date = ? AND end_time >= ?))', [
            now()->toDateString(),
            now()->toDateString(),
            now()->toTimeString()
        ])
        ->with(['orders' => function($query) {
            $query->where('status', '!=', 'completed')->latest();
        }])
        ->latest()
        ->get();

    // Past reservations (reservation time is in the past)
    $pastReservations = Reservation::when($phone, function ($query) use ($phone) {
            return $query->where('phone', $phone);
        })
        ->whereRaw('(date < ? OR (date = ? AND end_time < ?))', [
            now()->toDateString(),
            now()->toDateString(),
            now()->toTimeString()
        ])
        ->with(['orders' => function($query) {
            $query->where('status', 'completed')->latest();
        }])
        ->latest()
        ->get();

    // Fetch orders for a specific reservation if reservation_id is provided
    $orders = collect();
    $grandTotals = ['total' => 0];
    if ($reservationId) {
        $orders = \App\Models\Order::where('reservation_id', $reservationId)->with('items')->latest()->paginate(10);
        $grandTotals['total'] = $orders->sum('total');
    } else {
        // Return an empty paginator if no reservation_id
        $orders = new LengthAwarePaginator([], 0, 10);
    }

    return view('orders.index', [
        'activeReservations' => $activeReservations,
        'pastReservations' => $pastReservations,
        'orders' => $orders,
        'reservationId' => $reservationId,
        'grandTotals' => $grandTotals,
        'phone' => $phone
    ]);
}

    // Show order creation form (dine-in, under reservation)
    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $branches = Branch::all();

        $reservation = null;
        if ($reservationId) {
            $reservation = Reservation::find($reservationId);
        }

        return view('orders.create', compact('reservationId', 'menuItems', 'branches', 'reservation'));
    }

    // Store new order (dine-in, under reservation)
    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required_without:reservation_id|nullable|string|max:255',
            'customer_phone' => 'required_without:reservation_id|nullable|string|max:20',
        ]);

        $reservation = null;
        if (!empty($data['reservation_id'])) {
            $reservation = Reservation::find($data['reservation_id']);
        }

        $order = Order::create([
            'reservation_id' => $reservation ? $reservation->id : null,
            'branch_id'      => $reservation ? $reservation->branch_id : null,
            'customer_name'  => $reservation ? $reservation->name : $data['customer_name'],
            'customer_phone' => $reservation ? $reservation->phone : $data['customer_phone'],
            'order_type'     => $reservation ? ($reservation->order_type ?? 'dine_in_online_scheduled') : ($data['order_type'] ?? 'dine_in_online_scheduled'),
            'status'         => Order::STATUS_ACTIVE,
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $inventoryItem = ItemMaster::find($item['item_id']);
            if (!$inventoryItem) continue;
            
            $lineTotal = $inventoryItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $inventoryItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }

        $tax = $subtotal * 0.10;
        $discount = 0;
        $total = $subtotal + $tax - $discount;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ]);

        return redirect()->route('orders.index', [
            'phone' => $order->customer_phone,
            'reservation_id' => $order->reservation_id
        ])->with('success', 'Order created successfully!');
    }

    // View order details
    public function show($id)
    {
        $order = Order::with(['reservation', 'orderItems.menuItem'])
               ->findOrFail($id);

        return view('orders.summary', compact('order'));
    }

    // Edit order (dine-in, under reservation)
    public function edit($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        return view('orders.edit', compact('order', 'menuItems'));
    }

    // Update order (dine-in, under reservation)
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order->orderItems()->delete();
        $subtotal = 0;
        
        foreach ($data['items'] as $item) {
            $inventoryItem = ItemMaster::find($item['item_id']);
            if (!$inventoryItem) continue;
            
            $lineTotal = $inventoryItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $inventoryItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }

        $tax = $subtotal * 0.1;
        $service = $subtotal * 0.05;
        $total = $subtotal + $tax + $service;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $service,
            'total' => $total,
        ]);

        return redirect()->route('orders.index', ['phone' => $order->customer_phone])
            ->with('success', 'Order updated successfully.');
    }

    // Delete order (dine-in, under reservation)
    public function destroy($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $reservationId = $request->input('reservation_id', $order->reservation_id);
        $order->orderItems()->delete();
        $order->delete();
        return redirect()->route('orders.index', ['reservation_id' => $reservationId])
            ->with('success', 'Order deleted successfully.');
    }

    // Show payment or repeat order options
    public function paymentOrRepeat($order_id)
    {
        $order = Order::findOrFail($order_id);
        return view('orders.payment_or_repeat', compact('order'));
    }

    // Handle user choice: proceed to payment or place another order
    public function handleChoice(Request $request, $order_id)
    {
        $request->validate(['action' => 'required|in:payment,repeat']);

        if ($request->action === 'payment') {
            return redirect()->route('payments.create', ['order_id' => $order_id]);
        } else {
            $order = Order::findOrFail($order_id);
            return redirect()->route('orders.create', [
                'reservation_id' => $order->reservation_id
            ])->with('success', 'Order placed. Add another item below.');
        }
    }

    public function payment(Order $order)
    {
        return view('orders.payment', compact('order'));
    }

    // Takeaway order functions
    public function createTakeaway()
    {
        return view('orders.takeaway.create', [
            'branches' => Branch::all(),
            'items' => ItemMaster::where('is_menu_item', true)->get(),
            'defaultBranch' => null,
            'orderType' => 'takeaway_online_scheduled'
        ]);
    }

    public function storeTakeaway(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date|after_or_equal:now',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $order = Order::create([
            'order_type' => 'takeaway_online_scheduled',
            'branch_id' => $data['branch_id'],
            'order_time' => $data['order_time'],
            'status' => 'active',
            'placed_by_admin' => false
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            $total = $menuItem->selling_price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $total
            ]);

            $subtotal += $total;
        }

        $tax = $subtotal * 0.10;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax
        ]);

        return redirect()->route('orders.takeaway.summary', ['order' => $order->id])
            ->with('success', 'Takeaway order created! ID: ' . $order->takeaway_id);
    }

    public function summary(Order $order)
    {
        return view('orders.takeaway.summary', [
            'order' => $order->load('items.menuItem'),
            'editable' => $order->status === 'draft'
        ]);
    }

    public function submit(Request $request, Order $order)
    {
        $order->update(['status' => 'submitted']);
        return redirect()->route('orders.index', ['phone' => $order->customer_phone]);
    }

    // Edit takeaway order
    public function editTakeaway($id)
    {
        $order = Order::with('items.menuItem')->findOrFail($id);
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $branches = Branch::all();
        return view('orders.takeaway.edit', compact('order', 'menuItems', 'branches'));
    }

    // Submit takeaway order
    public function submitOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'submitted']);
        return redirect()->route('orders.index', ['phone' => $order->customer_phone])
            ->with('success', 'Takeaway order submitted successfully!');
    }

    // Show all orders with optional filters
    public function allOrders(Request $request)
    {
        $query = Order::with(['reservation', 'items', 'branch']);

        if ($request->filled('phone')) {
            $query->where('customer_phone', $request->phone);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20);
        $branches = Branch::all();
        return view('orders.all', compact('orders', 'branches'));
    }
}