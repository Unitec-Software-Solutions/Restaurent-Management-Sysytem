<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Payment;

class OrderController extends Controller
{
    // List all orders (admin/customer dashboard)
    public function index()
    {
        $orders = Order::with('orderItems')->latest()->paginate(20);
        return view('orders.index', compact('orders'));
    }

    // Show order creation form (customer/admin)
    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $menuItems = MenuItem::active()->get();
        return view('orders.create', compact('reservationId', 'menuItems'));
    }

    // Store new order (capture/entry)
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'reservation_id' => 'nullable|exists:reservations,id',
            'order_type' => 'required|string',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'reservation_id' => $data['reservation_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'order_type' => $data['order_type'],
            'status' => 'active',
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            $subtotal += $lineTotal;
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
            ]);
        }

        // Price calculation (add tax, service, discount as needed)
        $tax = $subtotal * 0.1; // Example 10% tax
        $service = $subtotal * 0.05; // Example 5% service
        $discount = 0; // Add logic for discounts
        $total = $subtotal + $tax + $service - $discount;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'service_charge' => $service,
            'discount' => $discount,
            'total' => $total,
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order placed successfully.');
    }

    // View order details
    public function show($id)
    {
        $order = Order::with('orderItems.menuItem')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    // Edit order (admin/customer if allowed)
    public function edit($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        $menuItems = MenuItem::active()->get();
        return view('orders.edit', compact('order', 'menuItems'));
    }

    // Update order (admin/customer if allowed)
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        // Similar validation and update logic as store()
        // Update order items, recalculate price, etc.
        // ...
        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order updated successfully.');
    }

    // Add to cart (AJAX or session-based, for customer)
    public function addToCart(Request $request)
    {
        $itemId = $request->input('menu_item_id');
        $quantity = $request->input('quantity', 1);
        // Store in session or DB as per your cart logic
        // ...
        return response()->json(['success' => true]);
    }

    // Cancel order (admin/customer)
    public function cancel($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'cancelled') {
            $order->status = 'cancelled';
            $order->save();
        }
        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order cancelled.');
    }

    // Proceed to payment
    public function proceedToPayment($id)
    {
        $order = Order::findOrFail($id);
        return view('orders.payment', compact('order'));
    }
}
