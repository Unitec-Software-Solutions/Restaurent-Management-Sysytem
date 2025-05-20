<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemMaster;
use App\Models\Reservation;

class OrderController extends Controller
{
    // List all orders for a reservation (dine-in)
    public function index(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $orders = Order::with('orderItems')
            ->when($reservationId, fn($q) => $q->where('reservation_id', $reservationId))
            ->latest()->paginate(20);

        return view('orders.index', compact('orders', 'reservationId'));
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
            'customer_name' => 'required_without:reservation_id|string',
            'customer_phone' => 'required_without:reservation_id|string',
            'order_type' => 'required_without:reservation_id|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
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
            'status'         => 'active',
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

        return redirect()->route('orders.index', ['reservation_id' => $order->reservation_id])
            ->with('success', 'Order placed successfully.');
    }

    // View order details
    public function show($id)
    {
        $order = Order::with('orderItems.inventoryItem')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    // Edit order (dine-in, under reservation)
    public function edit($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        return view('orders.edit', compact('order', 'menuItems'));
    }

    // Update order (dine-in, under reservation)
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
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
        return redirect()->route('orders.index', ['reservation_id' => $order->reservation_id])
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
}
