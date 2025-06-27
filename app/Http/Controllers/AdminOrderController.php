<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\ItemCategory;
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
        $admin = auth('admin')->user();
        
        // Get branches
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();
        
        // Get menu items
        $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
            ->where('is_menu_item', true)
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->get();
        
        // Get categories
        $categories = \App\Models\ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->active()
            ->get();
        
        // Get menus
        $menus = Menu::with(['menuItems.category'])
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->branch_id, 
                fn($q) => $q->where('branch_id', $admin->branch_id)
            )
            ->get();
        
        // Stock summary
        $stockSummary = [
            'available_count' => $menuItems->count(),
            'low_stock_count' => 0,
            'out_of_stock_count' => 0
        ];
        
        return view('admin.orders.create', compact('reservation', 'branches', 'menuItems', 'categories', 'menus', 'stockSummary'));
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
        
        // Get menu items directly, similar to what enhanced-create expects
        $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
            ->where('is_menu_item', true)
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->get();
        
        // Get categories for the filter dropdown - this was missing and causing the error
        $categories = \App\Models\ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->where('is_active', true)
            ->get();
        
        // Also get menus for menu structure if needed
        $menus = Menu::with(['menuItems.category'])
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->branch_id, 
                fn($q) => $q->where('branch_id', $admin->branch_id)
            )
            ->get();
        
        // Stock summary for the sidebar widget
        $stockSummary = [
            'available_count' => $menuItems->count(),
            'low_stock_count' => 0,
            'out_of_stock_count' => 0
        ];
        
        // Check if reservation_id is passed for reservation-based orders
        $reservation = null;
        if (request()->has('reservation_id')) {
            $reservationId = request()->get('reservation_id');
            $reservation = Reservation::find($reservationId);
            if ($reservation) {
                // Use the reservation-specific view
                return view('admin.orders.create', compact('branches', 'menus', 'menuItems', 'categories', 'reservation', 'stockSummary'));
            }
        }
        
        // For enhanced order creation without reservation
        return view('admin.orders.enhanced-create', compact('branches', 'menus', 'menuItems', 'categories', 'reservation', 'stockSummary'));
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

    public function createTakeaway()
    {
        $admin = auth('admin')->user();
        
        // Get available branches based on admin permissions
        if ($admin->is_super_admin) {
            $branches = Branch::active()->get();
            $defaultBranch = null;
        } elseif ($admin->branch_id) {
            $branches = Branch::where('id', $admin->branch_id)->active()->get();
            $defaultBranch = $admin->branch_id;
        } elseif ($admin->organization_id) {
            $branches = Branch::where('organization_id', $admin->organization_id)->active()->get();
            $defaultBranch = $branches->first()?->id;
        } else {
            return redirect()->route('admin.dashboard')->with('error', 'Access denied. No branch assigned.');
        }

        // Get validated menu items using the enhanced method
        $menuItems = $this->getValidatedMenuItems($admin, $defaultBranch);
        
        // Add real-time stock information
        $menuItems = $menuItems->map(function ($item) use ($defaultBranch) {
            $branchId = $defaultBranch ?? $item->branch_id;
            $currentStock = \App\Models\ItemTransaction::stockOnHand($item->id, $branchId);
            
            $item->current_stock = $currentStock;
            $item->stock_status = $this->getStockStatus($currentStock, $item->reorder_level ?? 10);
            $item->is_available = $currentStock > 0;
            
            return $item;
        });
        
        // Filter out unavailable items
        $availableMenuItems = $menuItems->where('is_available', true);
        
        // Get categories for filtering
        $categories = \App\Models\ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->active()
            ->get();
        
        // Log for debugging
        Log::info('Menu items loaded for takeaway creation', [
            'total_items' => $menuItems->count(),
            'available_items' => $availableMenuItems->count(),
            'admin_id' => $admin->id,
            'branch_id' => $defaultBranch
        ]);

        return view('admin.orders.takeaway.create', [
            'branches' => $branches,
            'menuItems' => $availableMenuItems,
            'categories' => $categories,
            'defaultBranch' => $defaultBranch,
            'orderType' => 'takeaway_walk_in_demand',
            'stockSummary' => [
                'total_items' => $menuItems->count(),
                'available_items' => $availableMenuItems->count(),
                'out_of_stock' => $menuItems->where('is_available', false)->count()
            ],
            'sessionDefaults' => $this->getSessionDefaults()
        ]);
    }

    /**
     * Store a new takeaway order (admin)
     */
    public function storeTakeaway(Request $request)
    {
        $admin = auth('admin')->user();
        
        $data = $request->validate([
            'order_type' => 'required|in:takeaway_walk_in_demand,takeaway_in_call_scheduled,takeaway_online_scheduled',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date|after_or_equal:now',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        // Validate admin can access this branch
        if (!$admin->is_super_admin && $admin->branch_id && $data['branch_id'] != $admin->branch_id) {
            return back()->withErrors(['branch_id' => 'Access denied to this branch.']);
        }

        DB::beginTransaction();
        try {
            // Set default customer name if empty
            if (empty($data['customer_name']) || trim($data['customer_name']) === '') {
                $data['customer_name'] = 'Not Provided';
            }

            // Create order
            $order = Order::create([
                'order_type' => $data['order_type'],
                'branch_id' => $data['branch_id'],
                'order_time' => $data['order_time'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'status' => 'active',
                'placed_by_admin' => true,
                'created_by_admin_id' => $admin->id,
                'takeaway_id' => 'TW' . now()->format('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
            ]);

            // Add order items
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

            DB::commit();

            return redirect()->route('admin.orders.takeaway.summary', $order->id)
                ->with('success', 'Takeaway order created successfully! Order ID: ' . $order->takeaway_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Takeaway order creation failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create order. Please try again.']);
        }
    }

    /**
     * Display takeaway orders index (admin)
     */
    public function indexTakeaway()
    {
        $admin = auth('admin')->user();
        
        $query = Order::with(['branch'])
            ->where('order_type', 'like', 'takeaway%');

        // Apply admin permissions
        if (!$admin->is_super_admin) {
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            } elseif ($admin->organization_id) {
                $query->whereHas('branch', fn($q) => $q->where('organization_id', $admin->organization_id));
            }
        }

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.takeaway.index', compact('orders'));
    }

    /**
     * Display a specific takeaway order (admin)
     */
    public function showTakeaway(Order $order)
    {
        $order->load(['items.menuItem', 'branch']);
        return view('admin.orders.takeaway.show', compact('order'));
    }

    /**
     * Delete takeaway order (admin)
     */
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
    public function getAvailableMenuItemsLegacy(Request $request)
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

    /**
     * Show reservation order summary
     */
    public function reservationOrderSummary(Request $request)
    {
        $reservationId = $request->get('reservation');
        $orderId = $request->get('order');
        
        if (!$reservationId || !$orderId) {
            return redirect()->route('admin.orders.dashboard')
                ->with('error', 'Invalid reservation or order ID');
        }
        
        $reservation = Reservation::with(['branch', 'table'])->find($reservationId);
        $order = Order::with(['orderItems.menuItem', 'branch'])->find($orderId);
        
        if (!$reservation || !$order) {
            return redirect()->route('admin.orders.dashboard')
                ->with('error', 'Reservation or order not found');
        }
        
        // Verify admin can access this order
        $admin = auth('admin')->user();
        if (!$admin->is_super_admin) {
            if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
                return redirect()->route('admin.orders.dashboard')
                    ->with('error', 'Access denied to this order');
            } elseif ($admin->organization_id && $order->branch->organization_id !== $admin->organization_id) {
                return redirect()->route('admin.orders.dashboard')
                    ->with('error', 'Access denied to this order');
            }
        }
        
        return view('admin.orders.reservations.summary', compact('reservation', 'order'));
    }

    /**
     * Show takeaway order type selector
     */
    public function takeawayTypeSelector()
    {
        return view('admin.orders.takeaway.type-selector');
    }

    /**
     * Edit takeaway order (admin)
     */
    public function editTakeaway(Order $order)
    {
        $admin = auth('admin')->user();
        
        // Check permissions
        if (!$admin->is_super_admin) {
            if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
                return redirect()->route('admin.orders.takeaway.index')
                    ->with('error', 'Access denied to this order');
            } elseif ($admin->organization_id && $order->branch->organization_id !== $admin->organization_id) {
                return redirect()->route('admin.orders.takeaway.index')
                    ->with('error', 'Access denied to this order');
            }
        }

        // Get branches for admin
        $branches = Branch::when(!$admin->is_super_admin && $admin->organization_id, 
            fn($q) => $q->where('organization_id', $admin->organization_id)
        )->active()->get();
        
        // Get menu items with stock information
        $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
            ->where('is_menu_item', true)
            ->where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->get();

        // Add stock information for each menu item
        foreach ($menuItems as $item) {
            $item->current_stock = \App\Models\ItemTransaction::stockOnHand($item->id, $order->branch_id);
            $item->is_low_stock = $item->current_stock <= ($item->reorder_level ?? 10);
        }
        
        // Get categories
        $categories = \App\Models\ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->active()
            ->get();

        return view('admin.orders.takeaway.edit', compact('order', 'branches', 'menuItems', 'categories'));
    }

    /**
     * Update takeaway order (admin)
     */
    public function updateTakeaway(Request $request, Order $order)
    {
        $admin = auth('admin')->user();
        
        // Check permissions
        if (!$admin->is_super_admin) {
            if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
                return redirect()->route('admin.orders.takeaway.index')
                    ->with('error', 'Access denied to this order');
            } elseif ($admin->organization_id && $order->branch->organization_id !== $admin->organization_id) {
                return redirect()->route('admin.orders.takeaway.index')
                    ->with('error', 'Access denied to this order');
            }
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_time' => 'required|date|after_or_equal:now',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Stock validation for all items
            $stockErrors = [];
            foreach ($validated['items'] as $item) {
                $inventoryItem = ItemMaster::find($item['item_id']);
                if (!$inventoryItem) continue;
                
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $validated['branch_id']);
                $currentOrderQty = $order->items->where('menu_item_id', $item['item_id'])->first()->quantity ?? 0;
                $netRequirement = $item['quantity'] - $currentOrderQty;
                
                if ($netRequirement > 0 && $currentStock < $netRequirement) {
                    $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Additional Required: {$netRequirement}";
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Reverse previous stock deductions if order was processed
            if ($order->stock_deducted && $order->status !== 'draft') {
                foreach ($order->items as $orderItem) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $order->branch->organization_id,
                        'branch_id' => $order->branch_id,
                        'inventory_item_id' => $orderItem->menu_item_id,
                        'transaction_type' => 'order_adjustment',
                        'quantity' => $orderItem->quantity, // Positive to add back stock
                        'cost_price' => $orderItem->menuItem->buying_price ?? 0,
                        'unit_price' => $orderItem->unit_price,
                        'source_id' => $order->id,
                        'source_type' => 'Order',
                        'created_by_user_id' => $admin->id,
                        'notes' => "Stock reversed for Takeaway Order #{$order->takeaway_id} update by admin",
                        'is_active' => true,
                    ]);
                }
            }

            // Delete existing order items
            $order->items()->delete();
            
            // Create new order items and adjust stock
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
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
                    'organization_id' => $order->branch->organization_id,
                    'branch_id' => $validated['branch_id'],
                    'inventory_item_id' => $item['item_id'],
                    'transaction_type' => 'sales_order',
                    'quantity' => -$item['quantity'], // Negative for stock deduction
                    'cost_price' => $inventoryItem->buying_price,
                    'unit_price' => $inventoryItem->selling_price,
                    'source_id' => $order->id,
                    'source_type' => 'Order',
                    'created_by_user_id' => $admin->id,
                    'notes' => "Stock deducted for updated Takeaway Order #{$order->takeaway_id} by admin",
                    'is_active' => true,
                ]);
            }

            // Calculate totals
            $tax = $subtotal * 0.10; // 10% tax
            $total = $subtotal + $tax;

            // Update order
            $order->update([
                'branch_id' => $validated['branch_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'order_time' => $validated['order_time'],
                'special_instructions' => $validated['special_instructions'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'stock_deducted' => true,
                'updated_by_admin' => true,
                'updated_by_admin_id' => $admin->id,
            ]);

            DB::commit();

            return redirect()->route('admin.orders.takeaway.show', $order)
                ->with('success', 'Takeaway order updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced stock validation with transaction safety
     */
    private function validateStockAvailability(array $items, int $branchId): array
    {
        $stockErrors = [];
        $lowStockWarnings = [];
        
        foreach ($items as $item) {
            $inventoryItem = ItemMaster::find($item['item_id']);
            if (!$inventoryItem || !$inventoryItem->is_active) {
                $itemName = $inventoryItem ? $inventoryItem->name : 'Unknown Item';
                $stockErrors[] = "Item {$itemName} is not available";
                continue;
            }
            
            $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $branchId);
            $requiredQuantity = $item['quantity'];
            
            if ($currentStock < $requiredQuantity) {
                $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Required: {$requiredQuantity}";
            } elseif ($currentStock <= ($inventoryItem->reorder_level ?? 10)) {
                $lowStockWarnings[] = "Low stock warning for {$inventoryItem->name}. Current: {$currentStock}, Reorder level: {$inventoryItem->reorder_level}";
            }
        }
        
        return [
            'errors' => $stockErrors,
            'warnings' => $lowStockWarnings
        ];
    }

    /**
     * Get session-based defaults for admin order forms
     */
    private function getSessionDefaults(): array
    {
        $admin = auth('admin')->user();
        
        return [
            'branch_id' => $admin->branch_id,
            'organization_id' => $admin->organization_id,
            'is_super_admin' => $admin->is_super_admin,
            'default_order_type' => 'takeaway_online_scheduled',
            'preferred_currency' => 'LKR',
            'default_tax_rate' => 0.10,
            'default_service_charge_rate' => 0.05
        ];
    }

    /**
     * Enhanced menu item retrieval with proper validation
     */
    private function getValidatedMenuItems($admin, $branchId = null)
    {
        $query = ItemMaster::where('is_menu_item', true)
            ->where('is_active', true);
        
        // Apply organization/branch filtering
        if (!$admin->is_super_admin && $admin->organization_id) {
            $query->where('organization_id', $admin->organization_id);
        }
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        return $query->get()->filter(function ($item) {
            // 1. Check if item has proper buy/sell prices
            if (empty($item->buying_price) || empty($item->selling_price)) {
                return false;
            }
            
            // 2. Check if selling price is reasonable (not zero or negative)
            if ($item->selling_price <= 0) {
                return false;
            }
            
            // 3. Check required menu attributes
            $attributes = is_array($item->attributes) ? $item->attributes : [];
            $requiredAttrs = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
            
            foreach ($requiredAttrs as $attr) {
                if (empty($attributes[$attr])) {
                    return false;
                }
            }
            
            // 4. Handle KOT items - they don't need stock validation
            if ($item->item_type === 'KOT') {
                return true; // KOT items are always available if they have valid prices
            }
            
            // 5. Check if item has stock tracking enabled and has stock (for Buy & Sell items)
            if ($item->track_inventory || $item->item_type === 'Buy & Sell') {
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->id, $item->branch_id);
                if ($currentStock <= 0) {
                    return false; // Out of stock items shouldn't appear
                }
            }
            
            return true;
        })->map(function ($item) {
            // Add KOT badge information
            return [
                'id' => $item->id,
                'name' => $item->item_name,
                'price' => $item->selling_price,
                'buying_price' => $item->buying_price,
                'item_type' => $item->item_type,
                'display_kot_badge' => $item->item_type === 'KOT',
                'display_stock' => $item->item_type === 'Buy & Sell',
                'current_stock' => $item->item_type === 'Buy & Sell' ? 
                    \App\Models\ItemTransaction::stockOnHand($item->id, $item->branch_id) : null,
                'is_available' => $item->item_type === 'KOT' ? true : 
                    (\App\Models\ItemTransaction::stockOnHand($item->id, $item->branch_id) > 0),
                'category' => $item->category->category_name ?? 'Uncategorized',
                'description' => $item->description
            ];
        });
    }

    /**
     * Helper method to determine stock status
     */
    private function getStockStatus($currentStock, $reorderLevel)
    {
        if ($currentStock <= 0) return 'out_of_stock';
        if ($currentStock <= $reorderLevel) return 'low_stock';
        if ($currentStock <= $reorderLevel * 2) return 'medium_stock';
        return 'good_stock';
    }

    /**
     * Enhanced method to get menu items from active menus
     */
    public function getAvailableMenuItems(Request $request)
    {
        $branchId = $request->get('branch_id');
        
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID is required'], 400);
        }

        // Get active menu for the branch
        $activeMenu = Menu::where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();
    
        if (!$activeMenu) {
            return response()->json([
                'menu' => null,
                'items' => [],
                'message' => 'No active menu found for this branch'
            ]);
        }

        // Get menu items with comprehensive validation
        $menuItems = ItemMaster::where('is_menu_item', true)
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->get()
            ->filter(function ($item) use ($branchId) {
                // Validate buy/sell prices
                if (empty($item->buying_price) || empty($item->selling_price) || $item->selling_price <= 0) {
                    return false;
                }
                
                // Validate menu attributes
                $attributes = is_array($item->attributes) ? $item->attributes : [];
                $requiredAttrs = ['cuisine_type', 'prep_time_minutes', 'serving_size'];
                
                foreach ($requiredAttrs as $attr) {
                    if (empty($attributes[$attr])) {
                        return false;
                    }
                }
                
                // Check stock availability
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->id, $branchId);
                return $currentStock > 0;
            })
            ->map(function ($item) use ($branchId) {
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->id, $branchId);
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->selling_price,
                    'buying_price' => $item->buying_price,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name,
                    'current_stock' => $currentStock,
                    'stock_status' => $this->getStockStatus($currentStock, $item->reorder_level ?? 10),
                    'is_available' => $currentStock > 0,
                    'attributes' => $item->attributes,
                    'prep_time' => $item->attributes['prep_time_minutes'] ?? 15,
                    'cuisine_type' => $item->attributes['cuisine_type'] ?? 'General',
                ];
            })
            ->values();

        return response()->json([
            'menu' => [
                'id' => $activeMenu->id,
                'name' => $activeMenu->name,
                'branch_id' => $activeMenu->branch_id,
            ],
            'items' => $menuItems,
            'summary' => [
                'total_items' => $menuItems->count(),
                'available_items' => $menuItems->where('is_available', true)->count(),
                'low_stock_items' => $menuItems->where('stock_status', 'low_stock')->count(),
            ]
        ]);
    }
}
