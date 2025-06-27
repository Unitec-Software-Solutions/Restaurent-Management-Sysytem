<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Reservation;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\InventoryService;
use App\Services\ProductCatalogService;
use App\Services\OrderService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
{
    protected $inventoryService;
    protected $catalogService;
    protected $orderService;

    public function __construct(
        InventoryService $inventoryService,
        ProductCatalogService $catalogService,
        OrderService $orderService
    ) {
        $this->inventoryService = $inventoryService;
        $this->catalogService = $catalogService;
        $this->orderService = $orderService;
    }

    // List all orders for a reservation (dine-in)
    public function index(Request $request)
    {
        $phone = $request->input('phone');
        $reservationId = $request->input('reservation_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $branchId = $request->input('branch_id');
        $export = $request->input('export');

        // Date filter setup
        if (!$startDate && !$endDate) {
            $startDate = now()->startOfDay()->toDateString();
            $endDate = now()->endOfDay()->toDateString();
        }

        // Query for orders with filters
        $ordersQuery = Order::query()
            ->with(['items.menuItem', 'steward', 'reservation', 'branch'])
            ->when($startDate, fn($q) => $q->whereDate('order_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('order_date', '<=', $endDate))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($reservationId, fn($q) => $q->where('reservation_id', $reservationId))
            ->orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Export functionality
        if ($export) {
            return $this->exportOrders($ordersQuery->get(), $export);
        }

        $orders = $ordersQuery->paginate(15);

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
        $grandTotals = ['total' => 0];
        if ($reservationId) {
            $grandTotals['total'] = $orders->sum('total');
        }

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();

        // Get branches for filter
        $branches = Branch::where('is_active', true)->get();

        return view('orders.index', [
            'activeReservations' => $activeReservations,
            'pastReservations' => $pastReservations,
            'orders' => $orders,
            'reservationId' => $reservationId,
            'grandTotals' => $grandTotals,
            'phone' => $phone,
            'stewards' => $stewards,
            'branches' => $branches,
            'filters' => compact('startDate', 'endDate', 'status', 'branchId'),
        ]);
    }

    // Show order creation form (dine-in, under reservation)
    public function create(Request $request)
    {
        $reservationId = $request->query('reservation');
        $reservation = null;
        if ($reservationId) {
            $reservation = \App\Models\Reservation::find($reservationId);
        }
        
        $menuItems = \App\Models\ItemMaster::where('is_menu_item', true)
            ->with('category')
            ->get();

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();

        return view('orders.create', compact('reservation', 'menuItems', 'stewards'));
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
            'steward_id' => 'nullable|exists:employees,id',
        ]);

        return DB::transaction(function () use ($data) {
            $reservation = null;
            if (!empty($data['reservation_id'])) {
                $reservation = Reservation::with('branch.organization')->find($data['reservation_id']);
                
                // Enhanced: Validate reservation and branch/organization status
                if (!$reservation) {
                    throw new \Exception('Reservation not found');
                }
                
                if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
                    throw new \Exception('Orders can only be created for confirmed or checked-in reservations');
                }
                
                if (!$reservation->branch->is_active || !$reservation->branch->organization->is_active) {
                    throw new \Exception('Cannot create orders for inactive branch or organization');
                }
                
                // Check time constraints for reservation orders
                if ($reservation->date < now()->toDateString()) {
                    throw new \Exception('Cannot create orders for past reservations');
                }
            }

            // Stock validation - check all items before creating order
            $stockErrors = [];
            foreach ($data['items'] as $item) {
                $inventoryItem = ItemMaster::find($item['item_id']);
                if (!$inventoryItem) continue;
                
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $reservation->branch_id);
                if ($currentStock < $item['quantity']) {
                    $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            $order = Order::create([
                'reservation_id' => $reservation ? $reservation->id : null,
                'branch_id'      => $reservation ? $reservation->branch_id : null,
                'customer_name'  => $reservation ? $reservation->name : $data['customer_name'],
                'customer_phone' => $reservation ? $reservation->phone : $data['customer_phone'],
                'order_type'     => $reservation ? ($reservation->order_type ?? 'dine_in_online_scheduled') : ($data['order_type'] ?? 'dine_in_online_scheduled'),
                'status'         => Order::STATUS_SUBMITTED,
                'steward_id'     => $data['steward_id'] ?? null,
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

                // Deduct stock immediately upon order submission
                \App\Models\ItemTransaction::create([
                    'organization_id' => $reservation->organization_id ?? Auth::user()->organization_id,
                    'branch_id' => $reservation->branch_id,
                    'inventory_item_id' => $item['item_id'],
                    'transaction_type' => 'sales_order',
                    'quantity' => -$item['quantity'], // Negative for stock deduction
                    'cost_price' => $inventoryItem->buying_price,
                    'unit_price' => $inventoryItem->selling_price,
                    'source_id' => $order->id,
                    'source_type' => 'Order',
                    'created_by_user_id' => Auth::id(),
                    'notes' => "Stock deducted for Order #{$order->id}",
                    'is_active' => true,
                ]);
            }

            $tax = $subtotal * 0.13; // 13% VAT
            $serviceCharge = $subtotal * 0.10; // 10% service charge
            $discount = 0;
            $total = $subtotal + $tax + $serviceCharge - $discount;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'discount' => $discount,
                'total' => $total,
                'stock_deducted' => true,
            ]);

            // Generate KOT immediately
            $order->generateKOT();

            return redirect()->route('orders.index', [
                'phone' => $order->customer_phone,
                'reservation_id' => $order->reservation_id
            ])->with('success', 'Order created successfully! Stock deducted and KOT generated.');
        });
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

        return DB::transaction(function () use ($data, $order) {
            // First, reverse previous stock deductions if order was already processed
            if ($order->stock_deducted) {
                foreach ($order->orderItems as $orderItem) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $order->reservation->organization_id ?? Auth::user()->organization_id,
                        'branch_id' => $order->branch_id,
                        'inventory_item_id' => $orderItem->inventory_item_id,
                        'transaction_type' => 'order_adjustment',
                        'quantity' => $orderItem->quantity, // Positive to add back stock
                        'cost_price' => $orderItem->inventoryItem->buying_price ?? 0,
                        'unit_price' => $orderItem->unit_price,
                        'source_id' => $order->id,
                        'source_type' => 'Order',
                        'created_by_user_id' => Auth::id(),
                        'notes' => "Stock reversed for Order #{$order->id} update",
                        'is_active' => true,
                    ]);
                }
            }

            // Stock validation for new items
            $stockErrors = [];
            foreach ($data['items'] as $item) {
                $inventoryItem = ItemMaster::find($item['item_id']);
                if (!$inventoryItem) continue;
                
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $order->branch_id);
                if ($currentStock < $item['quantity']) {
                    $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Delete old order items
            $order->orderItems()->delete();
            $subtotal = 0;
            
            // Create new order items and deduct stock
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

                // Deduct stock for new quantities
                \App\Models\ItemTransaction::create([
                    'organization_id' => $order->reservation->organization_id ?? Auth::user()->organization_id,
                    'branch_id' => $order->branch_id,
                    'inventory_item_id' => $item['item_id'],
                    'transaction_type' => 'sales_order',
                    'quantity' => -$item['quantity'], // Negative for stock deduction
                    'cost_price' => $inventoryItem->buying_price,
                    'unit_price' => $inventoryItem->selling_price,
                    'source_id' => $order->id,
                    'source_type' => 'Order',
                    'created_by_user_id' => Auth::id(),
                    'notes' => "Stock deducted for updated Order #{$order->id}",
                    'is_active' => true,
                ]);
            }

            $tax = $subtotal * 0.13; // 13% VAT
            $service = $subtotal * 0.10; // 10% service charge
            $total = $subtotal + $tax + $service;
            
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $service,
                'total' => $total,
                'stock_deducted' => true,
            ]);

            return redirect()->route('orders.index', [
                'phone' => $order->customer_phone,
                'reservation_id' => $order->reservation_id
            ])->with('success', 'Order updated successfully. Stock adjusted.');
        });
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
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000'
        ]);

        return DB::transaction(function () use ($data) {
            try {
                // Validate branch is active
                $branch = Branch::find($data['branch_id']);
                if (!$branch || !$branch->is_active) {
                    throw new \Exception('Selected branch is not available');
                }

                // Stock validation - check all items before creating order
                $stockErrors = [];
                foreach ($data['items'] as $item) {
                    $inventoryItem = ItemMaster::find($item['item_id']);
                    if (!$inventoryItem || !$inventoryItem->is_active) {
                        $itemName = $inventoryItem ? $inventoryItem->name : 'Unknown';
                        $stockErrors[] = "Item {$itemName} is not available";
                        continue;
                    }
                    
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $data['branch_id']);
                    if ($currentStock < $item['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                    }

                    // Check for low stock warnings
                    if ($currentStock <= ($inventoryItem->reorder_level ?? 10)) {
                        Log::warning("Low stock alert for item {$inventoryItem->name} at branch {$branch->name}. Current: {$currentStock}");
                    }
                }

                if (!empty($stockErrors)) {
                    throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
                }

                // Create order
                $order = Order::create([
                    'order_type' => 'takeaway_online_scheduled',
                    'branch_id' => $data['branch_id'],
                    'order_time' => $data['order_time'],
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'special_instructions' => $data['special_instructions'],
                    'status' => 'draft', // Start as draft
                    'placed_by_admin' => false,
                    'takeaway_id' => 'TK' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT)
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
                        'total_price' => $lineTotal
                    ]);

                    // Reserve stock (don't deduct yet until submission)
                    if (!$this->inventoryService->reserveStockForOrder($order->id, $item['item_id'], $data['branch_id'], $item['quantity'])) {
                        throw new \Exception("Failed to reserve stock for {$inventoryItem->name}");
                    }
                }

                // Calculate totals
                $tax = $subtotal * 0.10;
                $serviceCharge = $subtotal * 0.05; // 5% service charge for takeaway
                $total = $subtotal + $tax + $serviceCharge;

                $order->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'service_charge' => $serviceCharge,
                    'total' => $total,
                    'stock_reserved' => true
                ]);

                return redirect()->route('orders.takeaway.summary', ['order' => $order->id])
                    ->with('success', 'Takeaway order created! ID: ' . $order->takeaway_id);
                    
            } catch (\Exception $e) {
                Log::error('Takeaway order creation failed: ' . $e->getMessage());
                throw $e;
            }
        });
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
        $items = ItemMaster::where('is_menu_item', true)->get();
        $branches = Branch::all();

        // Prepare cart data for pre-filling
        $cart = [
            'items' => [],
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'total' => $order->total
        ];

        foreach ($order->items as $item) {
            $cart['items'][] = [
                'item_id' => $item->menu_item_id,
                'quantity' => $item->quantity
            ];
        }

        return view('orders.takeaway.edit', compact('order', 'items', 'branches', 'cart'));
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

    // Update takeaway order (customer)
    public function updateTakeaway(Request $request, Order $order)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date|after_or_equal:now',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Remove old items
        $order->items()->delete();
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
            'branch_id' => $data['branch_id'],
            'order_time' => $data['order_time'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
        return redirect()->route('orders.takeaway.summary', $order->id)
            ->with('success', 'Takeaway order updated successfully!');
    }

    // Submit takeaway order (customer)
    public function submitTakeaway(Request $request, Order $order)
    {
        $order->update(['status' => 'submitted']);
        return redirect()->route('orders.takeaway.show', $order->id)
            ->with('success', 'Takeaway order submitted successfully!');
    }

    // Show a single takeaway order (customer)
    public function showTakeaway(Order $order)
    {
        return view('orders.takeaway.show', compact('order'));
    }

    // Delete takeaway order
    public function destroyTakeaway($id)
    {
        $order = Order::findOrFail($id);
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Takeaway order deleted successfully.');
    }

    /**
     * Generate and print KOT (Kitchen Order Ticket)
     */
    public function printKOT(Order $order)
    {
        // Update order to mark KOT as generated
        $order->update(['kot_generated' => true]);
        
        return view('orders.kot-print', compact('order'));
    }

    /**
     * Generate and print bill
     */
    public function printBill(Order $order)
    {
        // Mark order as completed and bill generated
        $order->update([
            'bill_generated' => true,
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
        
        return view('orders.bill-print', compact('order'));
    }

    /**
     * Mark order as preparing and generate KOT
     */
    public function markAsPreparing(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->markAsPreparing();
            
            // Generate KOT if not already generated
            if (!$order->kot_generated) {
                $order->generateKOT();
            }
            
            return redirect()->back()->with('success', 'Order marked as preparing and KOT generated.');
        });
    }

    /**
     * Mark order as ready
     */
    public function markAsReady(Order $order)
    {
        $order->markAsReady();
        return redirect()->back()->with('success', 'Order marked as ready.');
    }

    /**
     * Complete order and generate bill
     */
    public function completeOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->markAsCompleted();
            
            return redirect()->route('orders.print-bill', $order)
                ->with('success', 'Order completed. Bill generated.');
        });
    }

    /**
     * Get stock alert for order items
     */
    public function checkStock(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity');
        $branchId = $request->input('branch_id');

        $currentStock = \App\Models\ItemTransaction::stockOnHand($itemId, $branchId);
        $item = ItemMaster::find($itemId);
        
        $response = [
            'item_name' => $item->name,
            'current_stock' => $currentStock,
            'requested_quantity' => $quantity,
            'available' => $currentStock >= $quantity,
            'is_low_stock' => $currentStock <= $item->reorder_level,
            'reorder_level' => $item->reorder_level
        ];

        if ($currentStock < $quantity) {
            $response['error'] = "Insufficient stock. Available: {$currentStock}, Required: {$quantity}";
        } elseif ($currentStock <= $item->reorder_level) {
            $response['warning'] = "Low stock alert. Current: {$currentStock}, Reorder level: {$item->reorder_level}";
        }

        return response()->json($response);
    }

    /**
     * Export orders to CSV or Excel
     */
    protected function exportOrders($orders, $format)
    {
        $filename = 'orders_' . now()->format('Y-m-d_H-i-s');
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'Order ID', 'Date', 'Customer Name', 'Phone', 'Branch', 
                    'Order Type', 'Status', 'Steward', 'Items Count', 
                    'Subtotal', 'Tax', 'Service Charge', 'Total'
                ]);

                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->order_date->format('Y-m-d H:i:s'),
                        $order->customer_name,
                        $order->customer_phone,
                        $order->branch->name ?? '',
                        str_replace('_', ' ', $order->order_type),
                        ucfirst($order->status),
                        $order->steward ? $order->steward->first_name . ' ' . $order->steward->last_name : '',
                        $order->items->count(),
                        number_format($order->subtotal, 2),
                        number_format($order->tax, 2),
                        number_format($order->service_charge, 2),
                        number_format($order->total, 2),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // For Excel format (if needed in future)
        return response()->json(['error' => 'Excel export not yet implemented'], 501);
    }

    // List takeaway orders (customer)
    public function indexTakeaway(Request $request)
    {
        $phone = $request->input('phone');
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query for takeaway orders
        $ordersQuery = Order::query()
            ->where('order_type', 'takeaway_online_scheduled')
            ->with(['items.menuItem', 'branch'])
            ->when($phone, fn($q) => $q->where('customer_phone', $phone))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $totalOrders = $ordersQuery->count();
        $totalAmount = $ordersQuery->sum('total');
        
        return view('orders.takeaway.index', compact(
            'orders', 
            'branches', 
            'phone', 
            'branchId', 
            'status', 
            'startDate', 
            'endDate',
            'totalOrders',
            'totalAmount'
        ));
    }

    /**
     * Add another order for the same reservation/customer
     */
    public function addAnother(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $phone = $request->input('phone');
        
        if ($reservationId) {
            return redirect()->route('orders.create', ['reservation_id' => $reservationId]);
        }
        
        if ($phone) {
            return redirect()->route('orders.takeaway.create', ['phone' => $phone]);
        }
        
        return redirect()->route('orders.create');
    }

    /**
     * Get available menu items for ordering with stock and KOT status
     */
    public function getAvailableMenuItems(Request $request)
    {
        $branchId = $request->input('branch_id', Auth::user()->branch_id);
        $categoryId = $request->input('category_id');
        $type = $request->input('type'); // 'buy_sell', 'kot', 'all'
        
        $query = ItemMaster::with(['category', 'branch'])
            ->where('branch_id', $branchId)
            ->where('is_active', true);
            
        // Filter by category if specified
        if ($categoryId) {
            $query->where('menu_category_id', $categoryId);
        }
        
        // Filter by type if specified
        if ($type && $type !== 'all') {
            if ($type === 'buy_sell') {
                $query->where('item_type', 'Buy & Sell');
            } elseif ($type === 'kot') {
                $query->where('item_type', 'KOT');
            }
        }
        
        $menuItems = $query->get()->filter(function ($item) {
            // Validate buy/sell prices for all items
            if (empty($item->buying_price) || empty($item->selling_price) || $item->selling_price <= 0) {
                return false;
            }
            
            // Check menu attributes for menu items
            if ($item->is_menu_item) {
                $attributes = is_array($item->attributes) ? $item->attributes : [];
                $requiredAttrs = ['cuisine_type', 'prep_time_minutes'];
                
                foreach ($requiredAttrs as $attr) {
                    if (empty($attributes[$attr])) {
                        return false;
                    }
                }
            }
            
            // Additional stock validation for Buy & Sell items
            if ($item->item_type === 'Buy & Sell' && $item->current_stock <= 0) {
                return false;
            }
            
            return true;
        })->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->item_name,
                'price' => $item->selling_price,
                'category' => $item->category->category_name ?? 'Uncategorized',
                'type' => $item->item_type,
                'stock_quantity' => $item->item_type === 'Buy & Sell' ? $item->current_stock : null,
                'is_available' => $item->item_type === 'KOT' ? true : ($item->current_stock > 0),
                'description' => $item->description,
                'image_url' => $item->image_path,
                'display_stock' => $item->item_type === 'Buy & Sell',
                'display_kot_badge' => $item->item_type === 'KOT',
                'buying_price' => $item->buying_price,
                'has_valid_prices' => !empty($item->buying_price) && !empty($item->selling_price) && $item->selling_price > 0
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $menuItems,
            'total' => $menuItems->count()
        ]);
    }

    /**
     * Get menu items filtered by specific type
     */
    public function getMenuItemsByType(Request $request)
    {
        $type = $request->input('type', 'all'); // 'buy_sell', 'kot', 'all'
        $branchId = $request->input('branch_id', Auth::user()->branch_id);
        $search = $request->input('search');
        
        $query = ItemMaster::with(['category'])
            ->where('branch_id', $branchId)
            ->where('is_active', true);
            
        // Apply type filter
        if ($type !== 'all') {
            if ($type === 'buy_sell') {
                $query->where('item_type', 'Buy & Sell')
                      ->where('current_stock', '>', 0); // Only show items with stock
            } elseif ($type === 'kot') {
                $query->where('item_type', 'KOT');
            }
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $items = $query->orderBy('item_name')->get();
        
        $formattedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->item_name,
                'price' => $item->selling_price,
                'type' => $item->item_type,
                'category' => $item->category->category_name ?? 'Uncategorized',
                'stock_quantity' => $item->item_type === 'Buy & Sell' ? $item->current_stock : null,
                'is_kot_item' => $item->item_type === 'KOT',
                'is_buy_sell_item' => $item->item_type === 'Buy & Sell',
                'is_available' => $item->item_type === 'KOT' || $item->current_stock > 0,
                'stock_status' => $this->getStockStatus($item),
                'description' => $item->description
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedItems,
            'type_filter' => $type,
            'total' => $formattedItems->count()
        ]);
    }

    /**
     * Helper method to get stock status for display
     */
    private function getStockStatus($item)
    {
        if ($item->item_type === 'KOT') {
            return 'available'; // KOT items are always available
        }
        
        if ($item->item_type === 'Buy & Sell') {
            if ($item->current_stock <= 0) {
                return 'out_of_stock';
            } elseif ($item->current_stock <= 5) {
                return 'low_stock';
            } else {
                return 'in_stock';
            }
        }
        
        return 'unknown';
    }
}