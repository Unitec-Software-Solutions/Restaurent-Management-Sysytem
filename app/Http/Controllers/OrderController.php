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

class OrderController extends Controller
{
    // List all orders for a reservation (dine-in)
    public function index(Request $request)
    {
        $orders = Order::with(['reservation', 'items'])
            ->when($request->phone, function($query) use ($request) {
                return $query->where('customer_phone', $request->phone);
            })
            ->with(['orderItems', 'branch'])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
            'phone' => $request->phone
        ]);
    }

    // Show order creation form (dine-in, under reservation)
    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $menuItems = \App\Models\ItemMaster::where('is_menu_item', true)->get(); // <-- FIXED
        $branches = \App\Models\Branch::all();

        $reservation = null;
        if ($reservationId) {
            $reservation = \App\Models\Reservation::find($reservationId);
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
            // Only require customer_name if reservation_id is not present
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
            if (!$inventoryItem) {
                continue;
            }
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

        // Example: 10% tax, 0 discount
        $tax = $subtotal * 0.10;
        $discount = 0;
        $total = $subtotal + $tax - $discount;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ]);

        return redirect()->route('orders.index', ['phone' => $order->customer_phone])
            ->with('success', 'Order created successfully!');
    }

    // View order details
    public function show($id)
    {
        $order = Order::with(['reservation', 'orderItems.menuItem'])
               ->findOrFail($id);

        return view(('orders.summary'), compact('order'));
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
        // Remove old items
        $order->orderItems()->delete();
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $inventoryItem = ItemMaster::find($item['item_id']);
            if (!$inventoryItem) {
                continue;
            }
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
        $discount = 0;
        $total = $subtotal + $tax + $service - $discount;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $service,
            'discount' => $discount,
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
            // Redirect to order creation with reservation_id if exists
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

    public function createTakeaway()
    {
        $user = auth()->user();
        $isAdmin = $user ? $user->isAdmin() : false;

        $defaultBranch = $isAdmin ? $user->branch_id : null;
        $branches = $defaultBranch
            ? Branch::where('id', $defaultBranch)->get()
            : Branch::all();

        return view('orders.takeaway.create', [
            'branches' => $branches,
            'items' => ItemMaster::where('is_menu_item', true)->get(),
            'defaultBranch' => $defaultBranch,
            'isAdmin' => $isAdmin,
            'orderType' => $this->determineOrderType()
        ]);
    }

    protected function determineOrderType()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return request()->input('type', 'takeaway_walk_in_demand');
        }
        return 'takeaway_online_scheduled';
    }

    public function storeTakeaway(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user ? $user->isAdmin() : false;

        $data = $request->validate([
            'branch_id' => [
                'required',
                'exists:branches,id',
                Rule::requiredIf(!$isAdmin)
            ],
            'order_time' => [
                $isAdmin ? 'nullable' : 'required',
                'date',
                'after_or_equal:now'
            ],
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        // Auto-set values for admins
        $orderData = [
            'order_type' => $isAdmin ?
                ($request->input('order_type', 'takeaway_walk_in_demand')) :
                'takeaway_online_scheduled',
            'branch_id' => $isAdmin ? $user->branch_id : $data['branch_id'],
            'order_time' => $isAdmin ?
                ($data['order_time'] ?? now()) :
                $data['order_time'],
            'status' => 'active',
            'placed_by_admin' => $isAdmin
        ];

        // Create order
        $order = Order::create($orderData);

        // Add items and calculate totals
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

        // Calculate totals
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
        return auth()->guard('admin')->check()
            ? redirect()->route('admin.orders.index')
            : redirect()->route('orders.index', ['phone' => $order->customer_phone]);
    }

    // Edit takeaway order
    public function editTakeaway($id)
    {
        $order = Order::with('items.menuItem')->findOrFail($id);
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $branches = Branch::all();
        $items = $order->items; // Add this line to define $items for the view
        return view('orders.takeaway.edit', compact('order', 'menuItems', 'branches', 'items'));
    }

    // Submit takeaway order
    public function submitOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'submitted']);
        // Redirect to index page with phone filter and show success message
        return redirect()->route('orders.index', ['phone' => $order->customer_phone])
            ->with('success', 'Takeaway order submitted successfully!');
    }

    // Show all orders with optional filters (customer/staff)
    public function allOrders(Request $request)
    {
        $query = Order::with(['reservation', 'items', 'branch']);

        // Optional filters
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
