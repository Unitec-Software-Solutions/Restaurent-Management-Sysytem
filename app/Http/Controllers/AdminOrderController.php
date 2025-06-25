<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Http\Requests\StoreOrderRequest;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\MenuSafetyService;
use App\Services\EnhancedOrderService;
use App\Services\EnhancedMenuSchedulingService;
use Illuminate\Support\Facades\Broadcast;

class AdminOrderController extends Controller
{
    use Exportable;

    protected $menuSafetyService;
    protected $enhancedOrderService;
    protected $menuSchedulingService;

    public function __construct(
        MenuSafetyService $menuSafetyService,
        EnhancedOrderService $enhancedOrderService,
        EnhancedMenuSchedulingService $menuSchedulingService
    ) {
        $this->menuSafetyService = $menuSafetyService;
        $this->enhancedOrderService = $enhancedOrderService;
        $this->menuSchedulingService = $menuSchedulingService;
    }
    
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        
        $query = Order::with(['reservation', 'branch', 'orderItems.menuItem']);

        if ($admin->is_super_admin) {
            // Super admin can see all orders
        } elseif ($admin->branch_id) {
            $query->where('branch_id', $admin->branch_id);
        } elseif ($admin->organization_id) {
            $query->whereHas('branch', fn($q) => $q->where('organization_id', $admin->organization_id));
        } else {
            // Return empty result for users without proper permissions
            $orders = collect()->paginate(20);
            return view('admin.orders.index', compact('orders'));
        }

        // Apply search and filters
        $query = $this->applyFiltersToQuery($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'orders_export.xlsx', [
                'ID', 'Customer Name', 'Phone', 'Branch', 'Status', 'Total Amount', 'Order Date', 'Created At'
            ]);
        }

        $orders = $query->latest()->paginate(20);
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();

        return view('admin.orders.index', compact('orders', 'branches'));
    }

    /**
     * Get searchable columns for orders
     */
    protected function getSearchableColumns(): array
    {
        return ['customer_name', 'customer_phone', 'id'];
    }

    // Edit order (admin)
    public function edit(Order $order)
    {
        $statusOptions = [
            'submitted' => 'Submitted',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        return view('admin.orders.edit', compact('order', 'statusOptions'));
    }

    // Update order status (admin)
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled',
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date',
            'customer_phone' => 'required|string|min:10|max:15'
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully!');
    }

    public function branchOrders(Branch $branch)
    {
        $orders = $branch->orders()
            ->with(['orderItems.menuItem', 'reservation'])
            ->where('status', 'submitted')
            ->latest()
            ->paginate(10);
            
        return view('admin.orders.index', compact('orders'));
    }

    public function createForReservation(Reservation $reservation)
    {
        // Use eager loading and specific queries to optimize performance
        $data = [
            'reservation' => $reservation,
            'branches' => Branch::select('id', 'name')->get(),
            'stewards' => \App\Models\Employee::select('id', 'name')
                ->whereIn('role', ['steward', 'waiter'])
                ->get(),
            'menuItems' => ItemMaster::select('id', 'name', 'price')
                ->where('is_menu_item', true)
                ->get(),
            'prefill' => [
                'customer_name' => $reservation->name,
                'customer_phone' => $reservation->phone,
                'branch_id' => $reservation->branch_id,
                'date' => $reservation->date?->format('Y-m-d') ?? '',
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'number_of_people' => $reservation->number_of_people,
                'reservation_id' => $reservation->id
            ]
        ];
        
        return view('admin.orders.create', $data);
    }

    /**
     * Store order for a reservation
     */
    public function storeForReservation(Request $request, Reservation $reservation)
    {
        // Debugging: Log reservation details
        Log::debug('Storing order for reservation', [
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
        Log::debug('Order created', [
            'order_id' => $order->id,
            'reservation_id' => $order->reservation_id
        ]);

        // Create order items with optimized queries
        $itemIds = collect($data['items'])->pluck('item_id')->unique();
        $menuItems = ItemMaster::whereIn('id', $itemIds)->get()->keyBy('id');
        
        $orderItems = collect($data['items'])->map(function ($item) use ($menuItems, $order) {
            $menuItem = $menuItems[$item['item_id']];
            $lineTotal = $menuItem->selling_price * $item['quantity'];
            
            return [
                'order_id' => $order->id,
                'menu_item_id' => $item['item_id'],
                'inventory_item_id' => $item['item_id'], 
                'quantity' => $item['quantity'],
                'unit_price' => $menuItem->selling_price,
                'total_price' => $lineTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
        
        OrderItem::insert($orderItems->toArray());
        $subtotal = $orderItems->sum('total_price');

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
     * Show the form for creating a new order
     */
    public function create()
    {
        $admin = auth('admin')->user();
        
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();
        
        $menus = Menu::with(['menuItems.category'])
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->branch_id, 
                fn($q) => $q->where('branch_id', $admin->branch_id)
            )
            ->get();
        
        return view('admin.orders.create', compact('branches', 'menus'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'branch_id' => 'required|exists:branches,id',
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'special_instructions' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.special_instructions' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'branch_id' => $validated['branch_id'],
                'order_type' => $validated['order_type'],
                'special_instructions' => $validated['special_instructions'],
                'status' => 'pending',
                'total_amount' => 0,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'admin_id' => auth('admin')->id()
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $subtotal = $menuItem->price * $item['quantity'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $subtotal,
                    'special_instructions' => $item['special_instructions'] ?? null
                ]);
                
                $totalAmount += $subtotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to create order. Please try again.');
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['orderItems.menuItem.category', 'branch', 'reservation']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order)
    {
        try {
            if ($order->status === 'completed') {
                return back()->with('error', 'Cannot delete completed orders.');
            }

            $order->orderItems()->delete();
            $order->delete();

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Order deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete order.');
        }
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
        $order->load('items.menuItem'); // Eager load items with their menu items

        $branches = Branch::all();
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $statusOptions = [
            'submitted' => 'Submitted',  
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
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
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled',
            'order_type' => 'required|in:dine-in,takeaway,delivery'
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

        $subtotal = $order->items->sum(function ($item) {
            return $item->menuItem->selling_price * $item->quantity;
        });
        $tax = $subtotal * 0.10;
        $total = $subtotal + $tax;

        $cart = [
            'items' => $order->items->map(function ($item) {
                return [
                    'item_id' => $item->menuItem->id,
                    'quantity' => $item->quantity,
                ];
            })->toArray(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];

        return view('orders.takeaway.edit', compact('order', 'items', 'branches', 'cart'));
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
        // Get the admin's branch ID
        $branchId = \Illuminate\Support\Facades\Auth::user()->branch_id;

        // Fetch orders for the admin's branch
        $orders = \App\Models\Order::with(['reservation', 'branch'])
            ->where('branch_id', $branchId)
            ->latest()
            ->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Get available menu items from active menus for a branch
     */
    public function getAvailableMenuItems(Request $request)
    {
        $branchId = $request->get('branch_id');
        $menuType = $request->get('menu_type', null); // optional filter by menu type
        
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        }

        // Get active menu for the branch
        $activeMenu = Menu::getActiveMenuForBranch($branchId);
        
        if (!$activeMenu) {
            return response()->json([
                'menu' => null,
                'items' => [],
                'message' => 'No active menu found for this branch'
            ]);
        }

        // Get menu items with availability checks
        $menuItems = $activeMenu->menuItems()
            ->with(['category'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name,
                    'current_stock' => $item->current_stock,
                    'is_available' => $item->getMenuAvailability(),
                    'prep_time' => $item->prep_time,
                    'image_url' => $item->image_url,
                ];
            });

        return response()->json([
            'menu' => [
                'id' => $activeMenu->id,
                'name' => $activeMenu->name,
                'type' => $activeMenu->type,
                'description' => $activeMenu->description,
            ],
            'items' => $menuItems,
            'message' => 'Menu items loaded successfully'
        ]);
    }

    /**
     * Enhanced create order form with menu integration
     */
    public function enhancedCreate(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Get branches based on admin permissions
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();

        // Get active menus for all branches
        $activeMenus = Menu::active()
            ->with(['branch', 'menuItems'])
            ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->whereHas('branch', fn($subQ) => $subQ->where('organization_id', $admin->organization_id));
            })
            ->get()
            ->groupBy('branch_id');

        return view('admin.orders.enhanced-create', compact('branches', 'activeMenus'));
    }

    /**
     * Store enhanced order with menu context and safety validation
     */
    public function enhancedStore(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'menu_id' => 'required|exists:menus,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Safety validation
        $itemIds = collect($validated['items'])->pluck('item_id')->toArray();
        $safetyCheck = $this->menuSafetyService->validateMenuItemsAvailability($itemIds, $validated['menu_id']);
        
        if (!empty($safetyCheck['errors'])) {
            return back()->withErrors($safetyCheck['errors'])->withInput();
        }
        
        if (!empty($safetyCheck['warnings'])) {
            session()->flash('warnings', $safetyCheck['warnings']);
        }

        // Verify menu is active and available
        $menu = Menu::find($validated['menu_id']);
        if (!$menu->shouldBeActiveNow()) {
            return back()->withErrors(['menu_id' => 'Selected menu is not currently active.']);
        }

        // Verify all items belong to the selected menu
        $menuItemIds = $menu->menuItems->pluck('id')->toArray();
        $requestedItemIds = collect($validated['items'])->pluck('item_id')->toArray();
        
        if (array_diff($requestedItemIds, $menuItemIds)) {
            return back()->withErrors(['items' => 'Some selected items are not available in the chosen menu.']);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'branch_id' => $validated['branch_id'],
                'menu_id' => $validated['menu_id'], 
                'order_type' => $validated['order_type'],
                'reservation_id' => $validated['reservation_id'] ?? null,
                'status' => 'pending',
                'special_instructions' => $validated['special_instructions'],
                'order_time' => now(),
            ]);

            // Create order items
            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                
                // Check item availability
                if (!$menuItem->getMenuAvailability()) {
                    throw new \Exception("Item '{$menuItem->name}' is no longer available.");
                }

                $lineTotal = $menuItem->price * $itemData['quantity'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['menu_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
            }

            // Calculate totals
            $tax = $subtotal * 0.10; // 10% tax
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $subtotal + $tax,
            ]);

            DB::commit();

            return redirect()->route('admin.orders.summary', $order)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enhanced order creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate order safety before processing
     */
    public function validateOrderSafety(Order $order)
    {
        $issues = $this->menuSafetyService->preventInactiveItemOrders($order);
        
        if (!empty($issues)) {
            return response()->json([
                'valid' => false,
                'issues' => $issues
            ], 422);
        }
        
        return response()->json(['valid' => true]);
    }

    /**
     * Get menu safety status for dashboard
     */
    public function getMenuSafetyStatus(Request $request)
    {
        $branchId = $request->get('branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }
        
        $status = $this->menuSafetyService->getMenuSafetyStatus($branchId);
        return response()->json($status);
    }

    /**
     * Archive old menus (Admin action)
     */
    public function archiveOldMenus(Request $request)
    {
        $daysOld = $request->get('days_old', 30);
        
        $result = $this->menuSafetyService->archiveOldMenus($daysOld);
        
        $message = "Archived {$result['archived_count']} old menus";
        if (!empty($result['errors'])) {
            $message .= ". Errors: " . implode(', ', $result['errors']);
        }
        
        return redirect()->back()->with('success', $message);
    }
}
