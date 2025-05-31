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
    // List all submitted orders (admin index)
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
        return view('admin.orders.create-takeaway', [
            'branches' => Branch::all(),
            'items' => ItemMaster::where('is_menu_item', true)->get(),
        ]);
    }

    /**
     * Store takeaway order (admin)
     */
    public function storeAdminTakeaway(\App\Http\Requests\StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $validated['reservation_id'] = null;

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
     * Display the summary of an order for a reservation
     */
    public function summary(Reservation $reservation, Order $order)
    {
        return view('admin.orders.summary', [
            'reservation' => $reservation,
            'order' => $order,
            'orderItems' => $order->items()->with('menuItem')->get(),
            'editable' => false,
        ]);
    }
}
