<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function summary()
    {
        // TODO: Implement summary logic
        return view('admin.summary');
    }

    /**
     * Dashboard view for orders
     */
    public function dashboard()
    {
        return view('admin.orders.dashboard');
    }

    /**
     * Show takeaway orders
     */
    public function takeaway()
    {
        return view('admin.orders.takeaway');
    }

    /**
     * Show the reservation orders page (reuse order blades)
     */
    public function reservations()
    {
        // You can pass the same data as for index, or filter for reservation orders
        $orders = $this->getReservationOrders(); // Implement this method as needed
        $branches = $this->getBranches(); // Implement as needed
        // Paginate the orders for compatibility with the view
        if ($orders instanceof \Illuminate\Database\Eloquent\Collection) {
            $orders = $orders->paginate(20);
        }
        return view('admin.orders.index', compact('orders', 'branches'));
    }

    /**
     * Archive old menus
     */
    public function archiveOldMenus()
    {
        return response()->json(['message' => 'Old menus archived']);
    }

    /**
     * Get menu safety status
     */
    public function menuSafetyStatus()
    {
        return response()->json(['status' => 'safe']);
    }

    /**
     * Update cart
     */
    public function updateCart(Request $request)
    {
        return response()->json(['message' => 'Cart updated']);
    }

    /**
     * Show the form for creating a new takeaway order
     */
    public function createTakeaway()
    {
        try {
            $branches = Branch::where('is_active', true)->get();
            $menuCategories = MenuCategory::with(['menuItems' => function($query) {
                $query->where('is_active', true);
            }])->where('is_active', true)->get();
            
            $menuItems = MenuItem::where('is_active', true)
                ->with(['category', 'itemMaster'])
                ->get();

            return view('admin.orders.takeaway.create', [
                'branches' => $branches,
                'menuCategories' => $menuCategories,
                'menuItems' => $menuItems,
                'orderType' => 'takeaway_admin',
                'defaultBranch' => $branches->first()
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to load takeaway order form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created takeaway order
     */
    public function storeTakeaway(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_time' => 'required|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Get branch to determine organization_id
            $branch = Branch::findOrFail($validated['branch_id']);

            // Create the order
            $order = Order::create([
                'order_number' => 'TO' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                'order_type' => 'takeaway',
                'branch_id' => $validated['branch_id'],
                'organization_id' => $branch->organization_id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => null,
                'pickup_time' => $validated['pickup_time'],
                'order_date' => now(),
                'subtotal' => $validated['total_amount'] - ($validated['tax_amount'] ?? 0) + ($validated['discount_amount'] ?? 0),
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'confirmed',
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
                'placed_by_admin' => true,
                'created_at' => now(),
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $menuItem->price * $item['quantity'],
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.orders.takeaway.show', $order->id)
                ->with('success', 'Takeaway order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create takeaway order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified takeaway order
     */
    public function showTakeaway(Order $order)
    {
        $order->load(['orderItems.menuItem', 'branch']);
        
        return view('admin.orders.takeaway.show', compact('order'));
    }

    /**
     * Show the form for editing the specified takeaway order
     */
    public function editTakeaway(Order $order)
    {
        $branches = Branch::where('is_active', true)->get();
        $menuCategories = MenuCategory::with(['menuItems' => function($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();
        
        $menuItems = MenuItem::where('is_active', true)
            ->with(['category', 'itemMaster'])
            ->get();

        $order->load('orderItems.menuItem');

        return view('admin.orders.takeaway.edit', [
            'order' => $order,
            'branches' => $branches,
            'menuCategories' => $menuCategories,
            'menuItems' => $menuItems,
        ]);
    }

    /**
     * Update the specified takeaway order
     */
    public function updateTakeaway(Request $request, Order $order)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_time' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'order_status' => 'required|in:confirmed,preparing,ready,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Update the order
            $order->update([
                'branch_id' => $validated['branch_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_time' => $validated['pickup_time'],
                'subtotal' => $validated['total_amount'] - ($validated['tax_amount'] ?? 0) + ($validated['discount_amount'] ?? 0),
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'order_status' => $validated['order_status'],
                'notes' => $validated['notes'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // Delete existing order items and create new ones
            $order->orderItems()->delete();
            
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $menuItem->price * $item['quantity'],
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.orders.takeaway.show', $order->id)
                ->with('success', 'Takeaway order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update takeaway order: ' . $e->getMessage());
        }
    }


    public function orders()
    {
        try {
            $orders = \App\Models\Order::with(['customer', 'orderItems', 'branch'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            return view('admin.orders.orders', compact('orders'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load orders: ' . $e->getMessage());
        }
    }

    /**
     * Get reservation orders for the current admin (simple version)
     */
    protected function getReservationOrders()
    {
        // You can filter by order_type or join with reservations as needed
        return \App\Models\Order::whereNotNull('reservation_id')->latest()->get();
    }

    /**
     * Get all branches for the current admin's organization (or all if super admin)
     */
    protected function getBranches()
    {
        $admin = auth('admin')->user();
        if ($admin && $admin->is_super_admin) {
            return \App\Models\Branch::where('is_active', true)->get();
        } elseif ($admin && $admin->organization_id) {
            return \App\Models\Branch::where('organization_id', $admin->organization_id)
                ->where('is_active', true)->get();
        } elseif ($admin && $admin->branch_id) {
            return \App\Models\Branch::where('id', $admin->branch_id)
                ->where('is_active', true)->get();
        }
        return collect();
    }
}
