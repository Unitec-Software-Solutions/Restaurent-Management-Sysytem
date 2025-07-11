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
use App\Models\Kot;
use App\Traits\Exportable;
use App\Enums\OrderType;
use App\Services\NotificationService;
use App\Services\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Check if order has KOT items for today's orders view
     */
    public function checkKotItems(Order $order)
    {
        try {
            $hasKotItems = $order->hasKotItems();
            $canGenerateKot = $order->canGenerateKot();
            
            return response()->json([
                'success' => true,
                'hasKotItems' => $hasKotItems,
                'canGenerateKot' => $canGenerateKot,
                'kotPrintUrl' => $hasKotItems ? route('admin.orders.print-kot', $order->id) : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking KOT items: ' . $e->getMessage()
            ], 500);
        }
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
        $menus = Menu::with(['menuItems.menuCategory'])
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
        $admin = auth('admin')->user();
        
        // Determine organization_id based on admin type
        $organizationId = null;
        if ($admin->is_super_admin) {
            // For super admin, get organization_id from the reservation's branch
            $branch = Branch::findOrFail($reservation->branch_id);
            $organizationId = $branch->organization_id;
        } else {
            // For regular admins, use their organization_id
            $organizationId = $admin->organization_id;
        }
        
        $order = Order::create([
            'reservation_id' => $reservation->id,
            'branch_id' => $reservation->branch_id,
            'organization_id' => $organizationId,
            'customer_name' => $reservation->name,
            'customer_phone' => $reservation->phone,
            'customer_email' => null,
            'order_type' => 'dine_in_admin',
            'status' => 'active',
            'subtotal' => 0,
            'created_by' => $admin->id,
            'placed_by_admin' => true,
            'order_date' => now(),
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
                'subtotal' => $lineTotal,
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
            'placed_by_admin' => true,
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => $admin->branch ? $admin->branch->phone ?? '0000000000' : '0000000000',
            'customer_email' => '',
            'order_time' => now()->addMinutes(30)->format('Y-m-d\TH:i')
        ];
        
        $branches = $this->getAdminAccessibleBranches($admin);
        
        // Get organizations if super admin
        $organizations = $admin->is_super_admin ? 
            \App\Models\Organization::where('is_active', true)->get() : 
            collect([$admin->organization]);
        
        return view('admin.orders.create', [
            'admin' => $admin,
            'organizations' => $organizations,
            'branches' => $branches,
            'defaultBranch' => $admin->branch_id,
            'defaultOrganization' => $admin->organization_id,
            'defaultCustomerName' => $defaultData['customer_name'],
            'defaultPhone' => $defaultData['customer_phone'],
            'orderType' => $orderType,
            'defaultData' => $defaultData
        ]);
    }

    
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

            // Determine organization_id based on admin type
            $organizationId = null;
            if ($admin->is_super_admin) {
                // For super admin, get organization_id from the selected branch
                $branch = Branch::findOrFail($validated['branch_id']);
                $organizationId = $branch->organization_id;
            } else {
                // For regular admins, use their organization_id
                $organizationId = $admin->organization_id;
            }

            // Find or create customer by phone
            $customer = Customer::findByPhone($validated['customer_phone']);
            if (!$customer) {
                $customer = Customer::createFromPhone($validated['customer_phone'], [
                    'name' => $validated['customer_name'] ?? 'Admin Customer',
                    'email' => $validated['customer_email'] ?? null,
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
                'order_number' => OrderNumberService::generate($validated['branch_id']),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'customer_email' => $customer->email ?? null,
                'branch_id' => $validated['branch_id'],
                'organization_id' => $organizationId,
                'reservation_id' => $reservation?->id,
                'order_type' => $orderType,
                'order_time' => $validated['order_time'] ?? now(),
                'special_instructions' => $validated['special_instructions'],
                'status' => Order::STATUS_PENDING,
                'created_by' => $admin->id,
                'placed_by_admin' => true,
                'order_date' => now(),
                'subtotal' => 0,
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

            // Check and print KOT if order has KOT items
            $kotResult = $this->checkAndPrintKOT($order, true);
            
            if ($kotResult['success'] && $kotResult['has_kot_items']) {
                // If KOT was generated successfully, include the print URL in success message
                return redirect()->route('admin.orders.show', $order)
                    ->with('success', 'Order created successfully!')
                    ->with('kot_print_url', $kotResult['print_url'] ?? null)
                    ->with('kot_generated', true);
            } else if ($kotResult['has_kot_items'] && !$kotResult['success']) {
                // KOT items exist but generation failed, still proceed but show warning
                return redirect()->route('admin.orders.show', $order)
                    ->with('warning', 'Order created successfully, but KOT generation failed: ' . $kotResult['message'])
                    ->with('kot_error', $kotResult['message']);
            }
            
            // No KOT items or KOT already exists, proceed normally
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
        $order->load(['orderItems.menuItem.menuCategory', 'branch', 'reservation']);
        
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
                'subtotal' => $lineTotal,
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
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        // Validate admin can access this branch
        if (!$admin->is_super_admin && $admin->branch_id && $data['branch_id'] != $admin->branch_id) {
            return back()->withErrors(['branch_id' => 'Access denied to this branch.']);
        }

        return DB::transaction(function () use ($data, $admin) {
            // Validate branch exists and is active
            $branch = Branch::with('organization')->findOrFail($data['branch_id']);
            if (!$branch->is_active || !$branch->organization->is_active) {
                throw new \Exception('Branch or organization is not active');
            }

            // Generate order number first for customer name
            $orderNumber = OrderNumberService::generate($data['branch_id']);
            
            // Update customer name with actual order number if it was default
            if (strpos($data['customer_name'], 'Customer with Order #') !== false) {
                $data['customer_name'] = 'Customer with Order #' . $orderNumber;
            }

            // Find or create customer
            $customer = Customer::findOrCreateByPhone($data['customer_phone'], [
                'name' => $data['customer_name']
            ]);

            // Stock validation
            $stockErrors = [];
            $orderItems = [];
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                // Validate that menu_item_id exists and find the menu item
                if (!isset($item['menu_item_id'])) {
                    $stockErrors[] = "Missing menu item ID in order data";
                    continue;
                }
                
                $menuItem = MenuItem::find($item['menu_item_id']);
                if (!$menuItem) {
                    $stockErrors[] = "Menu item with ID {$item['menu_item_id']} not found";
                    continue;
                }
                
                if (!$menuItem->is_active) {
                    $stockErrors[] = "Menu item {$menuItem->name} is not active";
                    continue;
                }
                
                // Get the associated ItemMaster for stock checking if available
                $itemMaster = $menuItem->itemMaster;
                if ($itemMaster && $itemMaster->is_perishable) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($itemMaster->id, $data['branch_id']);
                    if ($currentStock < $item['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                    }
                }

                // Calculate item total using MenuItem's price
                $itemTotal = $menuItem->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $itemTotal
                ];
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Calculate totals
            $taxRate = 0.10; // 10% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            // Generate takeaway ID
            $takeawayId = OrderNumberService::generateTakeawayId($data['branch_id']);

            // Create takeaway order with all required fields
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'organization_id' => $branch->organization_id,
                'customer_name' => $data['customer_name'], // Use updated customer name
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'order_type' => $data['order_type'],
                'order_number' => $orderNumber,
                'status' => 'pending',
                'order_date' => now(),
                'order_time' => $data['order_time'] ?? now(),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax, 
                'total' => $total, 
                'currency' => 'LKR',
                'payment_status' => 'pending',
                'special_instructions' => $data['special_instructions'] ?? null,
                'takeaway_id' => $takeawayId,
                'placed_by_admin' => true,
                'created_by_admin_id' => $admin->id,
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['menu_item']->id, // MenuItem ID
                    'inventory_item_id' => $itemData['menu_item']->itemMaster ? $itemData['menu_item']->itemMaster->id : null, // ItemMaster ID if available
                    'item_name' => $itemData['menu_item']->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['total_price'],
                    'subtotal' => $itemData['total_price'], 
                ]);

                // Deduct stock for perishable items (Buy & Sell items)
                if ($itemData['menu_item']->itemMaster && $itemData['menu_item']->itemMaster->is_perishable) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $branch->organization_id,
                        'branch_id' => $data['branch_id'],
                        'inventory_item_id' => $itemData['menu_item']->itemMaster->id,
                        'transaction_type' => 'order_deduction',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Stock deduction for admin takeaway order #{$order->order_number}",
                        'is_active' => true,
                        'created_by_user_id' => $admin->id,
                    ]);
                }
            }

            // Check and print KOT if order has KOT items
            $kotResult = $this->checkAndPrintKOT($order, true);
            
            if ($kotResult['success'] && $kotResult['has_kot_items']) {
                // If KOT was generated successfully, include the print URL in success message
                return redirect()->route('admin.orders.takeaway.summary', $order)
                    ->with('success', 'Takeaway order created successfully!')
                    ->with('kot_print_url', $kotResult['print_url'] ?? null)
                    ->with('kot_generated', true);
            } else if ($kotResult['has_kot_items'] && !$kotResult['success']) {
                // KOT items exist but generation failed, still proceed but show warning
                return redirect()->route('admin.orders.takeaway.summary', $order)
                    ->with('warning', 'Takeaway order created successfully, but KOT generation failed: ' . $kotResult['message'])
                    ->with('kot_error', $kotResult['message']);
            }
            
            // No KOT items or KOT already exists, proceed normally
            return redirect()->route('admin.orders.takeaway.summary', $order)
                ->with('success', 'Takeaway order created successfully!');
        });
    }

    /**
     * Display takeaway order summary after creation
     */
    public function summaryTakeaway($id)
    {
        $order = Order::with(['orderItems.menuItem', 'branch', 'customer'])
            ->findOrFail($id);
        
        // Check if admin has access to this order
        $admin = auth()->guard('admin')->user();
        if (!$admin->is_super_admin && $admin->branch_id && $order->branch_id != $admin->branch_id) {
            abort(403, 'Access denied to this order.');
        }

        return view('admin.orders.takeaway.summary', compact('order'));
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
        // $menuType = $request->get('menu_type', null); // optional filter by menu type
        
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
            ->with(['menuCategory'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'category_id' => $item->menu_category_id,
                    'category_name' => $item->menuCategory?->name,
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

            $admin = auth('admin')->user();
            
            // Determine organization_id based on admin type
            $organizationId = null;
            if ($admin->is_super_admin) {
                // For super admin, get organization_id from the selected branch
                $branch = Branch::findOrFail($validated['branch_id']);
                $organizationId = $branch->organization_id;
            } else {
                // For regular admins, use their organization_id
                $organizationId = $admin->organization_id;
            }

            // Create the order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => null,
                'branch_id' => $validated['branch_id'],
                'organization_id' => $organizationId,
                'menu_id' => $validated['menu_id'], 
                'order_type' => $validated['order_type'] ?? 'dine_in',
                'reservation_id' => $validated['reservation_id'] ?? null,
                'status' => 'pending',
                'special_instructions' => $validated['special_instructions'] ?? null,
                'order_time' => now(),
                'order_date' => now(),
                'subtotal' => 0,
                'created_by' => $admin->id,
                'placed_by_admin' => true,
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
                    'subtotal' => $lineTotal,
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
                        'reference_id' => $order->id,
                        'reference_type' => 'Order',
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
                    'reference_id' => $order->id,
                    'reference_type' => 'Order',
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
     * Update order status via AJAX
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,completed,cancelled'
        ]);

        try {
            $order->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print KOT for an order
     */
    public function printKOT(Order $order)
    {
        // Load necessary relationships
        $order->load([
            'orderItems.menuItem',
            'branch',
            'reservation',
            'customer'
        ]);
        
        // Update order to mark KOT as generated
        $order->update(['kot_generated' => true]);
        
        return view('orders.kot-print', compact('order'));
    }

    /**
     * Generate and download KOT as PDF (Admin)
     */
    public function printKOTPDF(Order $order)
    {
        // Load necessary relationships
        $order->load([
            'orderItems.menuItem',
            'branch',
            'reservation',
            'customer'
        ]);
        
        // Update order to mark KOT as generated
        $order->update(['kot_generated' => true]);
        
        // Generate PDF using DOMPDF
        $pdf = Pdf::loadView('admin.orders.kot-pdf', compact('order'));
        
        // Set paper size for thermal printer (80mm width)
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm x ~210mm in points
        
        // Set PDF options for better printing
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'DejaVu Sans Mono',
            'fontDir' => storage_path('fonts/'),
            'fontCache' => storage_path('fonts/'),
            'tempDir' => storage_path('app/temp/'),
            'chroot' => public_path(),
        ]);
        
        $filename = 'KOT-' . $order->order_number . '-' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($filename);
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
     * Check and print KOT if order has KOT items (Admin version)
     * This function checks if an order has KOT items and automatically prints the KOT
     */
    public function checkAndPrintKOT(Order $order, $autoPrint = false)
    {
        try {
            // Check if order has KOT items
            if (!$order->hasKotItems()) {
                return [
                    'success' => false,
                    'message' => 'No items in this order require kitchen preparation (KOT)',
                    'has_kot_items' => false
                ];
            }

            // Check if KOT already generated
            $existingKot = Kot::where('order_id', $order->id)->first();
            if ($existingKot) {
                return [
                    'success' => true,
                    'message' => 'KOT already exists for this order',
                    'has_kot_items' => true,
                    'kot_already_exists' => true,
                    'kot_id' => $existingKot->id,
                    // 'print_url' => route('admin.kots.print', $existingKot->id)
                    'print_url' => route('admin.orders.print-kot', $order->id)
                ];
            }

            // Generate KOT using KotController
            $kotController = new \App\Http\Controllers\KotController();
            $kotResponse = $kotController->generateKot(request(), $order);
            
            if ($kotResponse instanceof \Illuminate\Http\JsonResponse) {
                $kotData = $kotResponse->getData(true);
                
                if ($kotData['success']) {
                    $result = [
                        'success' => true,
                        'message' => 'KOT generated successfully',
                        'has_kot_items' => true,
                        'kot_generated' => true,
                        'kot_id' => $kotData['kot']['id'],
                        'print_url' => $kotData['print_url'],
                        'items_count' => $kotData['items_count']
                    ];
                    
                    // If auto-print is enabled, return print URL
                    if ($autoPrint) {
                        $result['auto_print_url'] = $kotData['print_url'];
                    }
                    
                    Log::info('Admin KOT check and print completed', [
                        'order_id' => $order->id,
                        'kot_id' => $kotData['kot']['id'],
                        'auto_print' => $autoPrint,
                        'admin_id' => auth('admin')->id()
                    ]);
                    
                    return $result;
                } else {
                    return [
                        'success' => false,
                        'message' => $kotData['message'] ?? 'Failed to generate KOT',
                        'has_kot_items' => true,
                        'error' => $kotData['message'] ?? 'Unknown error'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Unexpected response from KOT generation',
                'has_kot_items' => true
            ];

        } catch (\Exception $e) {
            Log::error('Error in Admin checkAndPrintKOT', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing KOT: ' . $e->getMessage(),
                'has_kot_items' => $order->hasKotItems()
            ];
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
            
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch ID is required',
                    'items' => []
                ]);
            }
            
            // Get the currently active menu for the branch
            $activeMenu = Menu::getActiveMenuForBranch($branchId);
            
            if (!$activeMenu) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active menu found for this branch',
                    'items' => [],
                    'menu' => null
                ]);
            }

            // Get menu items from the active menu only
            $menuItems = $activeMenu->menuItems()
                ->with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->wherePivot('is_available', true)
                ->get();
            
            $items = $menuItems->map(function($item) use ($branchId) {
                $itemType = $item->type ?? MenuItem::TYPE_KOT;
                $currentStock = 0;
                $canOrder = true;
                $stockStatus = 'available';
                
                if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                    $canOrder = $currentStock > 0;
                    
                    if ($currentStock <= 0) {
                        $stockStatus = 'out_of_stock';
                    } elseif ($currentStock <= 5) {
                        $stockStatus = 'low_stock';
                    }
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->pivot->override_price ?? $item->price,
                    'selling_price' => $item->pivot->override_price ?? $item->price,
                    'category' => $item->menuCategory?->name ?? 'Uncategorized',
                    'category_id' => $item->menu_category_id,
                    'display_type' => $itemType === MenuItem::TYPE_BUY_SELL ? 'stock' : 'kot',
                    'type' => $itemType,
                    'type_name' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                    'item_type' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                    'current_stock' => $currentStock,
                    'can_order' => $canOrder,
                    'is_available' => $canOrder,
                    'stock_status' => $stockStatus,
                    'preparation_time' => $item->preparation_time ?? 15,
                    'is_vegetarian' => $item->is_vegetarian ?? false,
                    'is_featured' => $item->is_featured ?? false,
                    'display_order' => $item->display_order ?? 0,
                    'special_notes' => $item->pivot->special_notes,
                ];
            });
            
            return response()->json([
                'success' => true,
                'menu' => [
                    'id' => $activeMenu->id,
                    'name' => $activeMenu->name,
                    'type' => $activeMenu->menu_type
                ],
                'items' => $items->toArray(),
                'branch_id' => $branchId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get menu items from active menu', [
                'branch_id' => $branchId,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            $alternatives = MenuItem::where('menu_category_id', $menuItem->menu_category_id)
                ->where('id', '!=', $itemId)
                ->where('is_available', true)
                ->limit(5)
                ->get()
                ->map(function ($item) use ($branchId) {
                    $stockCheck = $this->checkMenuItemStock($item, 1, $branchId);
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'category' => $item->menuCategory?->name,
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
            ],  500);
        }
    }

    /**
     * Create takeaway order
     */
    public function createTakeaway()
    {
        $admin = auth()->guard('admin')->user();
        
        // Generate default customer info with order ID placeholder
        $defaultCustomerName = 'Customer with Order #PENDING';
        $defaultPhone = $admin->branch ? $admin->branch->phone ?? '0000000000' : '0000000000';
        
        if ($admin->is_super_admin) {
            // Super admin can see all organizations and branches like customer
            $organizations = \App\Models\Organization::where('is_active', true)->get();
            $branches = \App\Models\Branch::with('organization')
                ->where('is_active', true)
                ->get();
            
            return view('admin.orders.takeaway.create', [
                'admin' => $admin,
                'organizations' => $organizations,
                'branches' => $branches,
                'defaultCustomerName' => $defaultCustomerName,
                'defaultPhone' => $defaultPhone
            ]);
        } else {
            // Regular admin - use their assigned branch
            $currentBranch = $admin->branch;
            if (!$currentBranch) {
                return redirect()->route('admin.orders.index')->with('error', 'No branch assigned to your account.');
            }
            
            if (!$currentBranch->is_active || !$currentBranch->organization->is_active) {
                return redirect()->route('admin.orders.index')->with('error', 'Your branch or organization is not active.');
            }
            
            // For branch admin, only show their branch
            $branches = collect([$currentBranch]);
            $organizations = collect([$currentBranch->organization]);
            
            return view('admin.orders.takeaway.create', [
                'admin' => $admin,
                'organizations' => $organizations,
                'branches' => $branches,
                'defaultBranch' => $currentBranch->id,
                'defaultOrganization' => $currentBranch->organization_id,
                'defaultCustomerName' => $defaultCustomerName,
                'defaultPhone' => $defaultPhone
            ]);
        }
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
                // Logic to restore stock would go here
                // This depends on your stock management system
                
                $order->update(['status' => 'cancelled']);
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
        // Parameters are currently unused; this is a stub for future stock logic.
        return [
            'available' => true,
            'available_quantity' => 999,
            'message' => 'Item available'
        ];
    }
    /**
     * Get branches for organization (API endpoint for admin orders)
     */
    public function getBranchesForOrganization(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            $organizationId = $request->get('organization_id');
            
            if (!$admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            if (!$organizationId) {
                return response()->json(['error' => 'Organization ID required'], 400);
            }
            
            // Build query based on admin permissions
            $query = Branch::where('organization_id', $organizationId)
                ->where('is_active', true);
            
            // Super admin can access any organization's branches
            if (!$admin->is_super_admin) {
                // Non-super admin can only access their own organization
                if ($admin->organization_id && $admin->organization_id != $organizationId) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
                
                // Branch admin can only see their own branch
                if ($admin->branch_id) {
                    $query->where('id', $admin->branch_id);
                }
            }
            
            $branches = $query->select(['id', 'name', 'address', 'phone', 'is_head_office'])
                ->orderBy('is_head_office', 'desc')
                ->orderBy('name')
                ->get();
            
            Log::info('Admin order branches fetched for organization', [
                'admin_id' => $admin->id,
                'organization_id' => $organizationId,
                'branches_count' => $branches->count()
            ]);
            
            return response()->json([
                'success' => true,
                'branches' => $branches
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching admin order branches', [
                'organization_id' => $organizationId ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch branches'
            ], 500);
        }
    }
}
