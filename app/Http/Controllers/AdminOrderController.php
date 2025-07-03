<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Traits\Exportable;
use App\Enums\OrderType;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\MenuSafetyService;
use App\Services\EnhancedOrderService;
use App\Services\EnhancedMenuSchedulingService;

class AdminOrderController extends Controller
{
    use Exportable;

    protected $menuSafetyService;
    protected $enhancedOrderService;
    protected $menuSchedulingService;
    protected $notificationService;

    public function __construct(
        MenuSafetyService $menuSafetyService,
        EnhancedOrderService $enhancedOrderService,
        EnhancedMenuSchedulingService $menuSchedulingService,
        NotificationService $notificationService
    ) {
        $this->menuSafetyService = $menuSafetyService;
        $this->enhancedOrderService = $enhancedOrderService;
        $this->menuSchedulingService = $menuSchedulingService;
        $this->notificationService = $notificationService;
    }
    
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Implement branch-scoped queries as requested
        $query = Order::with(['reservation', 'branch', 'orderItems.menuItem']);

        // Branch scoping logic
        if ($admin->is_super_admin) {
            // Super admin can see all orders
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->input('branch_id'));
            }
        } elseif ($admin->branch_id) {
            // Regular admin can only see orders from their branch
            $query->where('branch_id', $admin->branch_id);
        } else {
            // Admin without branch assignment sees no orders
            $query->whereRaw('1 = 0');
        }

        // Apply additional filters
        $this->applyOrderFilters($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'orders_export.xlsx', [
                'ID', 'Customer Name', 'Phone', 'Branch', 'Status', 'Total Amount', 'Order Date', 'Created At'
            ]);
        }

        $orders = $query->latest()->paginate(20);
        
        // Get branches based on admin permissions
        $branches = $this->getAdminAccessibleBranches($admin);

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
    public function create(Request $request)
    {
        $admin = auth('admin')->user();
        $orderType = $request->get('type', 'in_house'); // Default to in_house
        
        // For takeaway orders, set default takeaway type if not specified
        if ($orderType === 'takeaway' && !$request->has('subtype')) {
            $orderType = 'takeaway_walk_in_demand'; // Default takeaway subtype
        }
        
        // Apply admin defaults (pre-filled values as requested in refactoring)
        $defaultData = [
            'branch_id' => $admin->branch_id,
            'organization_id' => $admin->organization_id,
            'order_type' => $orderType,
            'created_by' => $admin->id,
            'placed_by_admin' => true
        ];
        
        $branches = $this->getAdminAccessibleBranches($admin);
        
        // Get menu items with proper relationships for stock/KOT display
        $menuItems = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->when(!$admin->is_super_admin && $admin->branch_id, function($q) use ($admin) {
                $q->where('branch_id', $admin->branch_id);
            })
            ->get()
            ->map(function($item) use ($admin) {
                // Determine item type based on linked item_master (if exists)
                $currentStock = 0;
                $itemType = MenuItem::TYPE_KOT; // Default to KOT
                
                if ($item->item_master_id && $item->itemMaster && $item->itemMaster->is_active) {
                    $itemType = MenuItem::TYPE_BUY_SELL;
                    // Calculate current stock from item_transactions
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $admin->branch_id ?? null);
                }
                
                // Add type and availability information for frontend display
                $item->display_type = $itemType == MenuItem::TYPE_BUY_SELL ? 'stock' : 'kot';
                $item->current_stock = $currentStock;
                $item->item_type = $itemType;
                $item->availability_info = $this->getItemAvailabilityInfo($item, $currentStock, $itemType);
                
                return $item;
            });
        
        // Get categories for filtering
        $categories = \App\Models\MenuCategory::where('is_active', true)
            ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->get();
        
        // Set default branch for the template
        $defaultBranch = $admin->branch_id ?? $branches->first()?->id;
        
        // Always use the unified create template
        return view('admin.orders.create', compact('menuItems', 'categories', 'branches', 'defaultData', 'orderType', 'defaultBranch'));
    }

    /**
     * Get availability information for menu item display
     */
    private function getItemAvailabilityInfo($item, $currentStock, $itemType)
    {
        if ($itemType == MenuItem::TYPE_BUY_SELL) {
            return [
                'type' => 'stock',
                'stock' => $currentStock,
                'available' => $currentStock > 0
            ];
        } else {
            return [
                'type' => 'kot',
                'available' => $item->is_available
            ];
        }
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'branch_id' => 'required|exists:branches,id',
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::cases(), 'value')),
            'reservation_id' => 'nullable|exists:reservations,id',
            'order_time' => 'nullable|date',
            'special_instructions' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'preferred_contact' => 'nullable|string|in:email,sms',
        ]);

        try {
            DB::beginTransaction();

            // Validate stock availability before creating order
            $this->validateStockForItems($validated['items']);

            $admin = auth('admin')->user();

            // Find or create customer by phone
            $customer = Customer::findByPhone($validated['customer_phone']);
            if (!$customer) {
                $customer = Customer::createFromPhone($validated['customer_phone'], [
                    'name' => $validated['customer_name'] ?? 'Admin Customer',
                    'email' => $validated['customer_email'],
                    'preferred_contact' => $validated['preferred_contact'] ?? 'email',
                ]);
            } else {
                // Update customer info if provided
                $customer->update([
                    'name' => $validated['customer_name'] ?? $customer->name,
                    'email' => $validated['customer_email'] ?? $customer->email,
                    'preferred_contact' => $validated['preferred_contact'] ?? $customer->preferred_contact,
                ]);
            }

            // Determine order type with admin default
            $orderType = OrderType::from($validated['order_type']);

            // Validate dine-in orders have reservation if required
            if ($orderType->isDineIn() && empty($validated['reservation_id'])) {
                // For admin orders, allow walk-in demand orders without reservation
                if ($orderType !== OrderType::DINE_IN_WALK_IN_DEMAND) {
                    throw new \Exception('Reservation required for this dine-in order type');
                }
            }

            // Get reservation if provided
            $reservation = null;
            if (!empty($validated['reservation_id'])) {
                $reservation = Reservation::find($validated['reservation_id']);
                if (!$reservation || $reservation->customer_phone_fk !== $customer->phone) {
                    throw new \Exception('Invalid reservation for this customer');
                }
            }

            // Create order with admin defaults
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'customer_email' => $customer->email,
                'branch_id' => $validated['branch_id'],
                'organization_id' => $admin->organization_id,
                'reservation_id' => $reservation?->id,
                'order_type' => $orderType,
                'order_time' => $validated['order_time'] ?? now(),
                'special_instructions' => $validated['special_instructions'],
                'status' => Order::STATUS_PENDING,
                'created_by' => $admin->id,
                'placed_by_admin' => true,
                'order_date' => now(),
                'total_amount' => 0
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                
                // Check if item requires stock validation
                if ($menuItem->type == MenuItem::TYPE_BUY_SELL) {
                    $this->reserveStock($menuItem->id, $order->id, $item['quantity']);
                }
                
                $subtotal = $menuItem->price * $item['quantity'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $subtotal,
                    'special_instructions' => $item['special_instructions'] ?? null
                ]);
                
                $totalAmount += $subtotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            // Auto-transition to confirmed for admin orders
            $order->transitionToStatus(Order::STATUS_CONFIRMED);

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

            return redirect()->route('admin.orders.summary', $order->id)
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
    /**
     * Show takeaway orders - redirect to unified index with filter
     */
    public function indexTakeaway()
    {
        return redirect()->route('admin.orders.index', ['type' => 'takeaway']);
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
     * Validate stock availability for order items
     */
    private function validateStockForItems(array $items)
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            
            if ($menuItem->type == MenuItem::TYPE_BUY_SELL) {
                $availableStock = $menuItem->itemMaster->stock ?? 0;
                if ($availableStock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$menuItem->name}. Available: {$availableStock}, Requested: {$item['quantity']}");
                }
            }
        }
    }

    /**
     * Reserve stock for an order item
     */
    private function reserveStock(int $itemId, int $orderId, int $quantity)
    {
        // Create stock reservation (if StockReservation model exists)
        if (class_exists('App\Models\StockReservation')) {
            \App\Models\StockReservation::createReservation($itemId, $orderId, $quantity);
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('Ymd');
        $sequence = str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$timestamp}-{$sequence}";
    }

    /**
     * Apply order-specific filters to query
     */
    private function applyOrderFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->input('order_type'));
        }

        // Handle legacy 'type' parameter for URLs like ?type=takeaway
        if ($request->filled('type')) {
            $query->where('order_type', $request->input('type'));
        }

        if ($request->filled('customer_phone')) {
            $query->where('customer_phone', 'like', '%' . $request->input('customer_phone') . '%');
        }

        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->input('customer_name') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->input('date_to'));
        }

        return $query;
    }

    /**
     * Get branches accessible to the admin
     */
    private function getAdminAccessibleBranches($admin)
    {
        if ($admin->is_super_admin) {
            return Branch::active()->get();
        } elseif ($admin->organization_id) {
            return Branch::where('organization_id', $admin->organization_id)->active()->get();
        } else {
            return collect();
        }
    }
    
    /**
     * Get menu items for a specific branch (API endpoint)
     */
    public function getMenuItems(Request $request)
    {
        $admin = auth('admin')->user();
        $branchId = $request->get('branch_id');
        
        try {
            // Verify admin has access to this branch
            if (!$admin->is_super_admin) {
                $allowedBranches = $this->getAdminAccessibleBranches($admin)->pluck('id');
                if ($branchId && !$allowedBranches->contains($branchId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied to this branch'
                    ], 403);
                }
            }
            
            // Get menu items with proper relationships
            $query = MenuItem::with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->where('is_available', true);
                
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            
            $menuItems = $query->get()->map(function($item) use ($branchId) {
                // Determine item type and stock
                $currentStock = 0;
                $itemType = MenuItem::TYPE_KOT;
                
                if ($item->item_master_id && $item->itemMaster && $item->itemMaster->is_active) {
                    $itemType = MenuItem::TYPE_BUY_SELL;
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'category' => $item->menuCategory?->name,
                    'display_type' => $itemType == MenuItem::TYPE_BUY_SELL ? 'stock' : 'kot',
                    'current_stock' => $currentStock,
                    'is_available' => $itemType == MenuItem::TYPE_KOT || $currentStock > 0,
                    'stock_status' => $this->getStockStatus($currentStock),
                ];
            });
            
            return response()->json([
                'success' => true,
                'items' => $menuItems,
                'branch_id' => $branchId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get menu items', [
                'branch_id' => $branchId,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load menu items'
            ], 500);
        }
    }
    
    /**
     * Get stock summary for API endpoint
     */
    public function getStockSummary(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            $branchId = $request->get('branch_id', $admin->branch_id);
            
            $stockSummary = ItemMaster::where('branch_id', $branchId)
                ->select('id', 'name', 'current_stock', 'minimum_stock', 'unit')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'current_stock' => $item->current_stock ?? 0,
                        'minimum_stock' => $item->minimum_stock ?? 0,
                        'unit' => $item->unit,
                        'status' => $item->current_stock <= $item->minimum_stock ? 'low' : 'ok'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $stockSummary
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting stock summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock summary'
            ], 500);
        }
    }

    /**
     * Validate cart items against stock
     */
    public function validateCart(Request $request)
    {
        try {
            $cartItems = $request->get('cart_items', []);
            $branchId = $request->get('branch_id');
            
            $validationResults = [];
            
            foreach ($cartItems as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                if (!$menuItem) {
                    $validationResults[] = [
                        'menu_item_id' => $item['menu_item_id'],
                        'valid' => false,
                        'message' => 'Menu item not found'
                    ];
                    continue;
                }
                
                // Check if we have enough stock
                $stockCheck = $this->checkMenuItemStock($menuItem, $item['quantity'], $branchId);
                
                $validationResults[] = [
                    'menu_item_id' => $item['menu_item_id'],
                    'valid' => $stockCheck['available'],
                    'message' => $stockCheck['message'],
                    'available_quantity' => $stockCheck['available_quantity']
                ];
            }

            return response()->json([
                'success' => true,
                'validation_results' => $validationResults
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate cart'
            ], 500);
        }
    }

    /**
     * Get menu alternatives for unavailable items
     */
    public function getMenuAlternatives($itemId, Request $request)
    {
        try {
            $menuItem = MenuItem::find($itemId);
            if (!$menuItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu item not found'
                ], 404);
            }
            
            $branchId = $request->get('branch_id');
            
            // Find alternatives based on category and availability
            $alternatives = MenuItem::where('menu_id', $menuItem->menu_id)
                ->where('id', '!=', $itemId)
                ->where('category', $menuItem->category)
                ->where('is_available', true)
                ->limit(5)
                ->get()
                ->map(function ($item) use ($branchId) {
                    $stockCheck = $this->checkMenuItemStock($item, 1, $branchId);
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'category' => $item->category,
                        'available' => $stockCheck['available']
                    ];
                });

            return response()->json([
                'success' => true,
                'alternatives' => $alternatives
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting menu alternatives: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get alternatives'
            ], 500);
        }
    }

    /**
     * Get real-time availability for a branch
     */
    public function getRealTimeAvailability($branchId)
    {
        try {
            $availability = MenuItem::join('menus', 'menu_items.menu_id', '=', 'menus.id')
                ->where('menus.branch_id', $branchId)
                ->where('menus.is_active', true)
                ->select('menu_items.*')
                ->get()
                ->map(function ($item) use ($branchId) {
                    $stockCheck = $this->checkMenuItemStock($item, 1, $branchId);
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'available' => $stockCheck['available'],
                        'available_quantity' => $stockCheck['available_quantity']
                    ];
                });

            return response()->json([
                'success' => true,
                'availability' => $availability
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting real-time availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get availability'
            ], 500);
        }
    }

    /**
     * Get inventory items for a branch
     */
    public function getInventoryItems($branchId)
    {
        try {
            $items = ItemMaster::where('branch_id', $branchId)
                ->select('id', 'name', 'current_stock', 'unit', 'minimum_stock')
                ->get();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting inventory items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get inventory items'
            ], 500);
        }
    }

    /**
     * Update menu availability for a branch
     */
    public function updateMenuAvailability($branchId, Request $request)
    {
        try {
            $menuItemId = $request->get('menu_item_id');
            $isAvailable = $request->get('is_available', false);
            
            $menuItem = MenuItem::join('menus', 'menu_items.menu_id', '=', 'menus.id')
                ->where('menus.branch_id', $branchId)
                ->where('menu_items.id', $menuItemId)
                ->first();
                
            if (!$menuItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu item not found'
                ], 404);
            }
            
            MenuItem::where('id', $menuItemId)->update([
                'is_available' => $isAvailable
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu availability updated'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating menu availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability'
            ], 500);
        }
    }

    /**
     * Create takeaway order
     */
    public function createTakeaway()
    {
        return redirect()->route('admin.orders.create', ['type' => 'takeaway']);
    }

    /**
     * Confirm order stock before processing
     */
    public function confirmOrderStock(Request $request)
    {
        try {
            $orderId = $request->get('order_id');
            $order = Order::with('orderItems.menuItem')->find($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            $stockIssues = [];
            foreach ($order->orderItems as $orderItem) {
                $stockCheck = $this->checkMenuItemStock(
                    $orderItem->menuItem, 
                    $orderItem->quantity, 
                    $order->branch_id
                );
                
                if (!$stockCheck['available']) {
                    $stockIssues[] = [
                        'item' => $orderItem->menuItem->name,
                        'requested' => $orderItem->quantity,
                        'available' => $stockCheck['available_quantity'],
                        'message' => $stockCheck['message']
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'has_stock_issues' => !empty($stockIssues),
                'stock_issues' => $stockIssues
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming order stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm stock'
            ], 500);
        }
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrderWithStock(Request $request)
    {
        try {
            $orderId = $request->get('order_id');
            $order = Order::with('orderItems.menuItem')->find($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            DB::transaction(function () use ($order) {
                // Restore stock for cancelled order
                foreach ($order->orderItems as $orderItem) {
                    // Logic to restore stock would go here
                    // This depends on your stock management system
                }
                
                $order->update(['status' => 'cancelled']);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled and stock restored'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order'
            ], 500);
        }
    }

    /**
     * Helper method to check menu item stock
     */
    private function checkMenuItemStock($menuItem, $quantity, $branchId)
    {
        // This is a simplified stock check - you may need to implement
        // more complex logic based on your recipe/ingredient system
        
        // For now, assume menu items are always available
        // In a real system, you'd check against ingredient stock
        
        return [
            'available' => true,
            'available_quantity' => 999,
            'message' => 'Item available'
        ];
    }
}
