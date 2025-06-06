<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\OrderItem;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // List all submitted orders (admin)
    public function index()
    {
        $orders = Order::with(['branch', 'items'])
            ->where('status', 'submitted')
            ->latest()
            ->paginate(10);
        $branches = Branch::all();
        return view('admin.orders.index', compact('orders', 'branches'));
    }

    // Edit order (admin)
    public function edit(Order $order)
    {
        $statusOptions = [
            Order::STATUS_SUBMITTED => 'Submitted',
            Order::STATUS_PREPARING => 'Preparing',
            Order::STATUS_READY => 'Ready',
            Order::STATUS_COMPLETED => 'Completed',
            Order::STATUS_CANCELLED => 'Cancelled'
        ];
        return view('admin.orders.edit', compact('order', 'statusOptions'));
    }

    // Update order status (admin)
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled'
        ]);
        $order->update($validated);
        return redirect()->route('admin.orders.index')->with('success', 'Order status updated!');
    }

    // List orders for a specific branch (admin)
    public function branchOrders(Branch $branch)
    {
        $orders = Order::with(['items'])
            ->where('branch_id', $branch->id)
            ->where('status', 'submitted')
            ->latest()
            ->paginate(10);
        return view('admin.orders.branch', compact('orders', 'branch'));
    }

    /**
     * Show form to create order for a reservation
     */
    public function createForReservation(Reservation $reservation)
    {
        $branches = Branch::all();
        $stewards = \App\Models\Employee::whereIn('role', ['steward', 'waiter'])->get();
        $menuItems = ItemMaster::where('is_menu_item', true)->get();

        return view('admin.orders.create', [
            'reservation' => $reservation,
            'branches' => $branches,
            'stewards' => $stewards,
            'menuItems' => $menuItems,
            'prefill' => [
                'customer_name' => $reservation->name,
                'customer_phone' => $reservation->phone,
                'branch_id' => $reservation->branch_id,
                'date' => $reservation->date ? $reservation->date->format('Y-m-d') : '',
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'number_of_people' => $reservation->number_of_people,
                'reservation_id' => $reservation->id
            ]
        ]);
    }

    /**
     * Store order for a reservation
     */
    public function storeForReservation(Request $request, Reservation $reservation)
    {
        // Debugging: Log reservation details
        \Log::debug('Storing order for reservation', [
            'reservation_id' => $reservation->id,
            'branch_id' => $reservation->branch_id,
            'name' => $reservation->name,
            'phone' => $reservation->phone
        ]);

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Create order with reservation data
        $order = Order::create([
            'reservation_id' => $reservation->id,
            'branch_id' => $reservation->branch_id,
            'customer_name' => $reservation->name,
            'customer_phone' => $reservation->phone,
            'order_type' => 'dine_in_admin',
            'status' => 'active',
        ]);

        // Debugging: Log created order
        \Log::debug('Order created', [
            'order_id' => $order->id,
            'reservation_id' => $order->reservation_id
        ]);

        // Create order items
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'], 
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }

        // Calculate totals
        $tax = $subtotal * 0.10;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);

        return redirect()->route('admin.orders.reservations.summary', [
            'reservation' => $reservation->id,
            'order' => $order->id
        ]);
    }

    /**
     * Show form to create a takeaway order (admin)
     */
    public function createTakeaway()
    {
        $branches = \App\Models\Branch::all();
        $items = \App\Models\ItemMaster::where('is_menu_item', true)->get();
        $defaultBranch = $branches->first()->id ?? null;
        return view('admin.orders.takeaway.create', compact('branches', 'items', 'defaultBranch'));
    }

    /**
     * Store takeaway order (admin)
     */
    public function storeTakeaway(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date',
            'customer_name' => 'nullable|string', // allow blank
            'customer_phone' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        $validated['reservation_id'] = null;
        // Set default if blank
        if (empty($validated['customer_name']) || trim($validated['customer_name']) === '') {
            $validated['customer_name'] = 'Not Provided';
        }
        $order = Order::create($validated);
        $this->createOrderItems($order, $validated['items']);
        return redirect()->route('admin.orders.takeaway.summary', $order->id);
    }

    private function createOrderItems(Order $order, array $items)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $menuItem = \App\Models\ItemMaster::find($item['item_id']);
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'], // Use the menu item's own ID
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }

        $tax = $subtotal * 0.10;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }

    /**
     * Display the summary of an order for a reservation (admin)
     */
    public function summary(Reservation $reservation, Order $order)
    {
        // Validate order belongs to this reservation
        if ($order->reservation_id !== $reservation->id) {
            abort(404, 'Order does not belong to this reservation');
        }

        return view('admin.orders.summary', [
            'reservation' => $reservation,
            'order' => $order,
            'orderItems' => $order->items()->with('menuItem')->get(),
            'editable' => false,
        ]);
    }

    public function dashboard()
    {
        return view('admin.orders.dashboard');
    }

    public function reservationIndex(Request $request)
    {
        $query = \App\Models\Order::with(['reservation', 'branch'])
            ->where('order_type', 'dine_in_admin');

        // Filter by reservation_id if provided
        if ($request->filled('reservation_id')) {
            $query->where('reservation_id', $request->input('reservation_id'));
        }

        $orders = $query->latest()->paginate(10);
        $branches = \App\Models\Branch::all();
        return view('admin.orders.index', compact('orders', 'branches'));
    }

    public function takeawayIndex()
    {
        $orders = \App\Models\Order::with(['branch'])
            ->where('order_type', 'like', 'takeaway%')
            ->latest()
            ->paginate(10);

        return view('admin.orders.takeaway.index', compact('orders'));
    }

    /**
     * AJAX: Update cart for admin order creation/edit
     */
    public function updateCart(Request $request)
    {
        $items = $request->input('items', []);
        $cart = [
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0
        ];

        foreach ($items as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            if (!$menuItem) continue;

            $quantity = (int)$item['quantity'];
            $lineTotal = $menuItem->selling_price * $quantity;

            $cart['items'][] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $menuItem->selling_price,
                'quantity' => $quantity,
                'total' => $lineTotal
            ];

            $cart['subtotal'] += $lineTotal;
        }

        // Calculate tax (10%) and total
        $cart['tax'] = $cart['subtotal'] * 0.10;
        $cart['total'] = $cart['subtotal'] + $cart['tax'];

        return response()->json($cart);
    }

    /**
     * Edit a reservation order (admin)
     */
    public function editReservationOrder(Reservation $reservation, Order $order)
    {
        $branches = Branch::all();
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $statusOptions = [
            Order::STATUS_SUBMITTED => 'Submitted',
            Order::STATUS_PREPARING => 'Preparing',
            Order::STATUS_READY => 'Ready',
            Order::STATUS_COMPLETED => 'Completed',
            Order::STATUS_CANCELLED => 'Cancelled'
        ];
        return view('admin.orders.edit', compact('order', 'reservation', 'branches', 'menuItems', 'statusOptions'));
    }

    /**
     * Update a reservation order (admin)
     */
    public function updateReservationOrder(Request $request, Reservation $reservation, Order $order)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled'
        ]);
        // Remove old items
        $order->orderItems()->delete();
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }
        $tax = $subtotal * 0.10;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => $data['status'],
        ]);
        return redirect()->route('admin.orders.reservations.summary', ['reservation' => $reservation->id, 'order' => $order->id])
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Show the form for editing a takeaway order (admin)
     */
    public function editTakeaway($id)
    {
        $order = Order::with('items.menuItem')->findOrFail($id);
        $items = ItemMaster::where('is_menu_item', true)->get();
        $branches = Branch::all();
        return view('orders.takeaway.edit', compact('order', 'items', 'branches'));
    }

    /**
     * Update a takeaway order (admin)
     */
    public function updateTakeaway(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date',
            'customer_name' => 'nullable|string', // allow blank
            'customer_phone' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:active,submitted,preparing,ready,completed,cancelled'
        ]);
        // Set default if blank
        if (empty($data['customer_name']) || trim($data['customer_name']) === '') {
            $data['customer_name'] = 'Not Provided';
        }
        // Remove old items
        $order->orderItems()->delete();
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }
        $tax = $subtotal * 0.10;
        $order->update([
            'order_type' => $data['order_type'],
            'branch_id' => $data['branch_id'],
            'order_time' => $data['order_time'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => $data['status'],
        ]);
        return redirect()->route('admin.orders.takeaway.index')->with('success', 'Takeaway order updated successfully.');
    }

    /**
     * Display the summary of a takeaway order (admin)
     */
    public function takeawaySummary(Order $order)
    {
        // Load related items and menu items
        $order->load(['items.menuItem', 'branch']);
        return view('admin.orders.takeaway.summary', [
            'order' => $order,
        ]);
    }

    // Delete takeaway order (admin)
    public function destroyTakeaway(Order $order)
    {
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('admin.orders.takeaway.index')
     
        ->with('success', 'Takeaway order deleted successfully.');
    }

       public function adminIndex()
    {
        $orders = Order::with('reservation')->latest()->paginate(10); // Eager load reservations with pagination
        return view('admin.orders.index', compact('orders'));
    }
}
