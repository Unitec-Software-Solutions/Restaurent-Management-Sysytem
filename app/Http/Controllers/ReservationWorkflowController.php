<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\MenuItem;
use App\Models\Employee;
use App\Enums\ReservationType;
use App\Enums\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\OrderNumberService;

class ReservationWorkflowController extends Controller
{
    /**
     * Show reservation summary and ask for order decision
     */
    public function showReservationSummary(Reservation $reservation)
    {
        $reservation->load(['branch', 'customer']);
        
        // Calculate fees based on reservation type and restaurant config
        $fees = $this->calculateReservationFees($reservation);
        
        return view('reservations.summary', compact('reservation', 'fees'));
    }

    /**
     * Handle reservation confirmation and order decision
     */
    public function confirmReservation(Request $request, Reservation $reservation)
    {
        $request->validate([
            'action' => 'required|in:make_order,payment_only'
        ]);

        DB::beginTransaction();
        try {
            // Confirm the reservation
            $reservation->update([
                'status' => 'confirmed',
                'confirmed_at' => now()
            ]);

            if ($request->action === 'make_order') {
                // Redirect to order creation
                DB::commit();
                return redirect()->route('orders.create-from-reservation', $reservation)
                    ->with('success', 'Reservation confirmed! Please create your order.');
            } else {
                // Redirect to payment for reservation fee
                DB::commit();
                return redirect()->route('reservations.payment', $reservation)
                    ->with('success', 'Reservation confirmed! Please proceed to payment.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to confirm reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show order creation form for confirmed reservation
     */
    public function createOrderFromReservation(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
            return redirect()->route('reservations.summary', $reservation)
                ->withErrors(['error' => 'Reservation must be confirmed before creating orders']);
        }

        // Get available menu items for the branch and organization
        $menuItems = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($reservation) {
                $q->where('branch_id', $reservation->branch_id)
                  ->orWhere(function($subQ) use ($reservation) {
                      // Include organization-wide items
                      $subQ->whereNull('branch_id')
                           ->where('organization_id', $reservation->branch->organization_id);
                  });
            })
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Add stock and availability information
        foreach ($menuItems as $item) {
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                $item->current_stock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $reservation->branch_id);
                $item->can_order = $item->current_stock > 0;
            } else {
                $item->current_stock = null;
                $item->can_order = true; 
            }
        }

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('branch_id', $reservation->branch_id)
            ->where('is_active', true)
            ->get();

        // Get dine-in order types
        $orderTypes = collect(OrderType::dineInTypes())->map(function($type) {
            return array(
                'value' => $type->value,
                'label' => $type->getLabel(),
            );
        });

        return view('reservations.create-order', compact(
            'reservation', 
            'menuItems', 
            'stewards', 
            'orderTypes'
        ));
    }

    /**
     * Store order created from reservation
     */
    public function storeOrderFromReservation(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::dineInTypes(), 'value')),
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            'steward_id' => 'nullable|exists:employees,id',
        ]);

        return DB::transaction(function () use ($validated, $reservation) {
            // Get customer
            $customer = $reservation->customer ?? Customer::findByPhone($reservation->phone);
            
            if (!$customer) {
                throw new \Exception('Customer not found for reservation');
            }

            // Validate stock for all items first
            $stockErrors = [];
            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem) continue;
                
                // Only check stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $reservation->branch_id);
                    if ($currentStock < $itemData['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$itemData['quantity']}";
                    }
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Create order
            $order = Order::create([
                'reservation_id' => $reservation->id,
                'branch_id' => $reservation->branch_id,
                'organization_id' => $reservation->branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'customer_email' => $customer->email,
                'order_type' => OrderType::from($validated['order_type']),
                'order_number' => OrderNumberService::generate($reservation->branch_id),
                'status' => 'pending',
                'order_date' => now(),
                'special_instructions' => $validated['special_instructions'],
                'user_id' => $validated['steward_id'],
            ]);

            // Create order items and calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem) continue;
                
                $lineTotal = $menuItem->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $order->orderItems()->create([
                    'menu_item_id' => $itemData['menu_item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $lineTotal,
                    'total_price' => $lineTotal,
                    'special_instructions' => $itemData['special_instructions'],
                ]);

                // Reserve stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    \App\Models\ItemTransaction::create([
                        'item_master_id' => $menuItem->item_master_id,
                        'branch_id' => $reservation->branch_id,
                        'transaction_type' => 'reservation',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Reserved for order #{$order->id}",
                    ]);
                }
            }

            // Calculate taxes and total
            $taxRate = 0.1; // 10% tax - can be configurable
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax,
                'total' => $total,
            ]);

            return redirect()->route('orders.summary', $order)
                ->with('success', 'Order created successfully!');
        });
    }

    /**
     * Show takeaway order creation form with customer phone
     */
    public function createTakeawayOrder(Request $request)
    {
        $phone = $request->input('phone');
        $organizationId = $request->input('organization_id');
        $branchId = $request->input('branch_id');
        $customer = null;
        
        if ($phone) {
            $customer = Customer::findByPhone($phone);
        }

        // Get user context (admin or regular user)
        $admin = auth('admin')->user();
        $isAdmin = !is_null($admin);
        
        // Get organizations and branches based on user type
        $organizations = collect();
        $branches = collect();
        $defaultOrganization = null;
        $defaultBranch = null;

        if ($isAdmin) {
            // Admin user - show organization selection
            if ($admin->is_super_admin) {
                // Super admin can see all organizations
                $organizations = Organization::where('is_active', true)->get();
            } elseif ($admin->organization_id) {
                // Org/Branch admin - get their organization
                $defaultOrganization = $admin->organization_id;
                $organizations = Organization::where('id', $admin->organization_id)
                    ->where('is_active', true)->get();
                
                if ($admin->branch_id) {
                    // Branch admin - set default branch
                    $defaultBranch = $admin->branch_id;
                }
            }
            
            // Get branches for selected organization
            $selectedOrgId = $organizationId ?: $defaultOrganization;
            if ($selectedOrgId) {
                $branches = Branch::where('organization_id', $selectedOrgId)
                    ->where('is_active', true)->get();
            }
        } else {
            // Regular customer - show all active branches from all organizations
            $branches = Branch::with('organization')
                ->where('is_active', true)
                ->whereHas('organization', function($q) {
                    $q->where('is_active', true);
                })
                ->get();
        }

        // Get menu items for selected branch
        $items = collect();
        $selectedBranchId = $branchId ?: $defaultBranch;
        
        if ($selectedBranchId) {
            $items = MenuItem::with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->where('is_available', true)
                ->where(function($q) use ($selectedBranchId, $selectedOrgId) {
                    $q->where('branch_id', $selectedBranchId)
                      ->orWhere(function($subQ) use ($selectedOrgId) {
                          // Include organization-wide items
                          $subQ->whereNull('branch_id')
                               ->where('organization_id', $selectedOrgId);
                      });
                })
                ->orderBy('menu_category_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Add stock and availability information
            foreach ($items as $item) {
                $itemType = $item->type ?? MenuItem::TYPE_KOT;
                
                if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                    $item->current_stock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $selectedBranchId);
                    $item->can_order = $item->current_stock > 0;
                    $item->item_type = 'Buy & Sell';
                } else {
                    $item->current_stock = null;
                    $item->can_order = true; // KOT items are always available
                    $item->item_type = 'KOT';
                }
                
                // Add selling price for display
                $item->selling_price = $item->price;
            }
        }

        return view('orders.takeaway.create', compact(
            'branches', 
            'organizations', 
            'customer', 
            'phone', 
            'defaultOrganization', 
            'defaultBranch', 
            'items',
            'isAdmin',
            'organizationId',
            'branchId'
        ));
    }

    /**
     * Get branches for organization (AJAX)
     */
    public function getBranchesForOrganization($organizationId)
    {
        // Only return active branches
        $branches = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get(['id', 'name', 'address', 'opening_time', 'closing_time', 'phone']);
        return response()->json(['branches' => $branches]);
    }

    /**
     * Get menu items for branch (AJAX) - Enhanced version
     */
    public function getMenuItemsForBranch(Request $request, $branchId)
    {
        $organizationId = $request->input('organization_id');
        
        if (!$branchId) {
            return response()->json(['items' => []]);
        }

        // Get branch information to determine organization
        $branch = Branch::find($branchId);
        if (!$branch) {
            return response()->json(['items' => [], 'error' => 'Branch not found']);
        }

        $orgId = $organizationId ?: $branch->organization_id;

        $menuItems = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($branchId, $orgId) {
                $q->where('branch_id', $branchId)
                  ->orWhere(function($subQ) use ($orgId) {
                      // Include organization-wide items
                      $subQ->whereNull('branch_id')
                           ->where('organization_id', $orgId);
                  });
            })
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $items = $menuItems->map(function($item) use ($branchId) {
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            $currentStock = 0;
            $canOrder = true;
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                $canOrder = $currentStock > 0;
            }
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'selling_price' => $item->price,
                'type' => $itemType,
                'type_name' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                'item_type' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                'current_stock' => $currentStock,
                'can_order' => $canOrder,
                'category_name' => $item->menuCategory->name ?? 'Uncategorized',
                'category_id' => $item->menu_category_id,
                'preparation_time' => $item->preparation_time ?? 15,
                'is_vegetarian' => $item->is_vegetarian ?? false,
                'is_featured' => $item->is_featured ?? false,
                'display_order' => $item->display_order ?? 0,
            ];
        });

        // Group items by category for better display
        $categorizedItems = $items->groupBy('category_name')->map(function($categoryItems, $categoryName) {
            return [
                'category_name' => $categoryName,
                'items' => $categoryItems->sortBy('display_order')->values()
            ];
        })->values();

        return response()->json([
            'items' => $items,
            'categorized_items' => $categorizedItems,
            'branch_info' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'organization_id' => $branch->organization_id
            ]
        ]);
    }

    /**
     * Store takeaway order
     */
    public function storeTakeawayOrder(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_time' => 'required|date',
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::takeawayTypes(), 'value')),
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            // Find or create customer
            $customer = Customer::findOrCreateByPhone($validated['customer_phone'], [
                'name' => $validated['customer_name'],
            ]);

            // Validate branch
            $branch = Branch::with('organization')->findOrFail($validated['branch_id']);
            if (!$branch->is_active || !$branch->organization->is_active) {
                throw new \Exception('Branch or organization is not active');
            }

            // Validate stock for all items first
            $stockErrors = [];
            $orderItems = [];
            $subtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem || !$menuItem->is_active) {
                    $stockErrors[] = "Item is not available";
                    continue;
                }
                
                // Check stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $validated['branch_id']);
                    if ($currentStock < $itemData['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$itemData['quantity']}";
                        continue;
                    }
                }

                $lineTotal = $menuItem->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                ];
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Create order
            $orderNumber = OrderNumberService::generate($validated['branch_id']);
            $taxRate = 0.1; // 10% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            $order = Order::create([
                'branch_id' => $validated['branch_id'],
                'organization_id' => $branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'order_type' => OrderType::from($validated['order_type']),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'order_date' => now(),
                'order_time' => $validated['order_time'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax,
                'total' => $total,
                'currency' => 'LKR',
                'payment_status' => 'pending',
                'special_instructions' => $validated['special_instructions'],
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $order->orderItems()->create([
                    'menu_item_id' => $itemData['menu_item']->id,
                    'item_name' => $itemData['menu_item']->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['total_price'], // Set subtotal as required by the table
                    'total_price' => $itemData['total_price'],
                ]);

                // Deduct stock for Buy & Sell items
                if ($itemData['menu_item']->type === MenuItem::TYPE_BUY_SELL && $itemData['menu_item']->item_master_id) {
                    \App\Models\ItemTransaction::create([
                        'item_master_id' => $itemData['menu_item']->item_master_id,
                        'branch_id' => $validated['branch_id'],
                        'transaction_type' => 'sale',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Sold via takeaway order #{$order->id}",
                    ]);
                }
            }

            return redirect()->route('orders.takeaway.summary', $order)
                ->with('success', 'Takeaway order created successfully!');
        });
    }

    /**
     * Show order summary
     */
    public function showOrderSummary(Order $order)
    {
        $order->load(['orderItems.menuItem', 'customer', 'branch', 'reservation']);
        
        // Check if order has KOT items (items that require production)
        $hasKotItems = $order->orderItems()->whereHas('menuItem', function($q) {
            $q->where('type', MenuItem::TYPE_KOT)
              ->where('requires_preparation', true);
        })->exists();
        
        // Determine if order is editable (for payment options)
        $editable = in_array($order->status, ['pending', 'confirmed']) && 
                   $order->payment_status !== 'paid';
        
        return view('orders.summary', compact('order', 'hasKotItems', 'editable'));
    }

    /**
     * Print KOT for order
     */
    public function printKOT(Order $order)
    {
        // Only generate KOT for orders with KOT items
        $kotItems = $order->orderItems()->whereHas('menuItem', function($q) {
            $q->where('type', MenuItem::TYPE_KOT)
              ->where('requires_preparation', true);
        })->get();

        if ($kotItems->isEmpty()) {
            return back()->withErrors(['error' => 'No items in this order require kitchen preparation']);
        }

        // Update order to mark KOT as generated
        $order->update([
            'kot_generated' => true,
            'kot_generated_at' => now(),
            'status' => 'preparing'
        ]);

        return view('admin.orders.print-kot', compact('order', 'kotItems'));
    }

    /**
     * Calculate reservation fees based on type and restaurant config
     */
    private function calculateReservationFees(Reservation $reservation)
    {
        $config = \App\Models\RestaurantConfig::where('organization_id', $reservation->branch->organization_id)
            ->where('branch_id', $reservation->branch_id)
            ->first();
        
        $fees = [
            'reservation_fee' => 0,
            'cancellation_fee' => 0
        ];
        
        if ($config) {
            // Set reservation fee based on type
            switch ($reservation->type ?? 'online') {
                case 'online':
                    $fees['reservation_fee'] = $config->online_reservation_fee ?? 0;
                    break;
                case 'in_call':
                    $fees['reservation_fee'] = $config->phone_reservation_fee ?? 0;
                    break;
                case 'walk_in':
                    $fees['reservation_fee'] = $config->walkin_reservation_fee ?? 0;
                    break;
                default:
                    $fees['reservation_fee'] = $config->default_reservation_fee ?? 0;
            }
            
            // Cancellation fee rules can be complex based on time, etc.
            $fees['cancellation_fee'] = $config->cancellation_fee_rules['default'] ?? 0;
        }
        
        return $fees;
    }

    /**
     * Initialize reservation workflow - customer chooses reservation type
     */
    public function initializeReservation(Request $request)
    {
        $admin = auth('admin')->user();
        $isAdmin = !is_null($admin);
        
        // Get organizations and branches for admin
        $organizations = collect();
        $branches = collect();
        
        if ($isAdmin) {
            if ($admin->is_super_admin) {
                $organizations = Organization::where('is_active', true)->get();
            } elseif ($admin->organization_id) {
                $organizations = Organization::where('id', $admin->organization_id)
                    ->where('is_active', true)->get();
            }
        } else {
            // For customers, show all active branches
            $branches = Branch::with('organization')
                ->where('is_active', true)
                ->whereHas('organization', function($q) {
                    $q->where('is_active', true);
                })
                ->get();
        }
        
        // Get admin defaults
        $defaults = $this->getAdminDefaults();
        
        return view('reservations.initialize', compact(
            'organizations', 
            'branches', 
            'isAdmin', 
            'defaults'
        ));
    }

    /**
     * Create reservation form based on type
     */
    public function createReservation(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:online,in_call,walk_in',
            'organization_id' => 'nullable|exists:organizations,id',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|min:10|max:15'
        ]);

        $reservationType = ReservationType::from($validated['type']);
        $branch = Branch::with('organization')->findOrFail($validated['branch_id']);
        
        // Calculate fees for this reservation type
        $fees = $this->calculateReservationFeesForType($reservationType, $branch);
        
        // Find customer if phone provided
        $customer = null;
        if (!empty($validated['phone'])) {
            $customer = Customer::findByPhone($validated['phone']);
        }

        // Get admin defaults
        $admin = auth('admin')->user();
        $defaults = [];
        
        if ($admin) {
            $defaults = [
                'phone' => $admin->phone ?? '',
                'datetime' => now()->format('Y-m-d\TH:i'),
                'name' => $customer->name ?? '',
                'email' => $customer->email ?? ''
            ];
        }

        // Get available tables/arrangements
        $tables = \App\Models\Table::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('capacity')
            ->get();

        // Get branch phone number
        $branchPhone = $branch->phone;

        // Get stewards for the branch
        $stewards = \App\Models\Employee::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('role', 'steward')
                  ->orWhereHas('roles', function($r) {
                      $r->where('name', 'steward');
                  });
            })
            ->get();

        return view('reservations.create', compact(
            'reservationType',
            'branch',
            'fees', 
            'customer',
            'defaults',
            'tables',
            'branchPhone',
            'stewards'
        ));
    }

    /**
     * Calculate fees for specific reservation type and branch
     */
    private function calculateReservationFeesForType(ReservationType $type, Branch $branch)
    {
        $config = \App\Models\RestaurantConfig::where('organization_id', $branch->organization_id)
            ->where('branch_id', $branch->id)
            ->first();
        
        $fees = [
            'reservation_fee' => 0,
            'cancellation_fee' => 0
        ];
        
        if ($config) {
            switch ($type) {
                case ReservationType::ONLINE:
                    $fees['reservation_fee'] = $config->online_reservation_fee ?? 0;
                    break;
                case ReservationType::IN_CALL:
                    $fees['reservation_fee'] = $config->phone_reservation_fee ?? 0;
                    break;
                case ReservationType::WALK_IN:
                    $fees['reservation_fee'] = $config->walkin_reservation_fee ?? 0;
                    break;
            }
            
            // Get cancellation fee rules
            $cancellationRules = json_decode($config->cancellation_fee_rules ?? '{}', true);
            $fees['cancellation_fee'] = $cancellationRules['default'] ?? 0;
        }
        
        return $fees;
    }

    /**
     * Store new reservation
     */
    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:online,in_call,walk_in',
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:15',
            'email' => 'nullable|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'party_size' => 'required|integer|min:1|max:50',
            'table_id' => 'nullable|exists:tables,id',
            'special_requirements' => 'nullable|string|max:1000',
            'dietary_preferences' => 'nullable|array',
            'occasion' => 'nullable|string|max:100'
        ]);

        return DB::transaction(function () use ($validated) {
            $branch = Branch::findOrFail($validated['branch_id']);
            
            // Find or create customer
            $customer = Customer::findOrCreateByPhone($validated['phone'], [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'dietary_preferences' => json_encode($validated['dietary_preferences'] ?? [])
            ]);

            // Calculate fees
            $reservationType = ReservationType::from($validated['type']);
            $fees = $this->calculateReservationFeesForType($reservationType, $branch);

            // Create reservation
            $reservation = Reservation::create([
                'branch_id' => $branch->id,
                'customer_phone_fk' => $customer->phone,
                'name' => $validated['name'],
                'phone' => $customer->phone,
                'email' => $validated['email'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'party_size' => $validated['party_size'],
                'table_id' => $validated['table_id'],
                'special_requirements' => $validated['special_requirements'],
                'type' => $reservationType,
                'table_size' => $validated['party_size'],
                'reservation_fee' => $fees['reservation_fee'],
                'cancellation_fee' => $fees['cancellation_fee'],
                'status' => 'pending',
                'created_by' => auth('admin')->id()
            ]);

            // Send confirmation email/SMS if configured
            $this->sendReservationConfirmation($reservation);

            return redirect()->route('reservations.summary', $reservation)
                ->with('success', 'Reservation created successfully!');
        });
    }

    /**
     * Send reservation confirmation
     */
    private function sendReservationConfirmation(Reservation $reservation)
    {
        try {
            if ($reservation->email && $reservation->customer->isPreferredContactEmail()) {
                Mail::to($reservation->email)
                    ->send(new \App\Mail\ReservationConfirmed($reservation));
            }
            
            // Add SMS notification here if needed
            
        } catch (\Exception $e) {
            Log::warning('Failed to send reservation confirmation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show today's orders with KOT filtering for admin dashboard
     */
    public function showTodaysOrders(Request $request)
    {
        $admin = auth('admin')->user();
        
        $query = Order::with(['orderItems.menuItem', 'branch', 'customer'])
            ->whereDate('created_at', today());

        // Branch scoping
        if (!$admin->is_super_admin) {
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            } elseif ($admin->organization_id) {
                $query->whereHas('branch', function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                });
            }
        } else {
            // Super admin can filter by branch
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->input('branch_id'));
            }
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->input('order_type'));
        }

        if ($request->filled('has_kot')) {
            if ($request->input('has_kot') == '1') {
                $query->whereHas('orderItems.menuItem', function($q) {
                    $q->where('type', MenuItem::TYPE_KOT);
                });
            } else {
                $query->whereDoesntHave('orderItems.menuItem', function($q) {
                    $q->where('type', MenuItem::TYPE_KOT);
                });
            }
        }

        $orders = $query->paginate(20);

        // Add KOT status and priority to each order
        foreach ($orders as $order) {
            $order->has_kot_items = $order->hasKotItems();
            $order->can_generate_kot = $order->canGenerateKot();
            $order->can_generate_bill = $order->canGenerateBill();
            $order->priority = $order->getPriority();
        }

        $branches = $admin->is_super_admin ? Branch::all() : collect();

        return view('admin.orders.today', compact('orders', 'branches'));
    }

    /**
     * Get admin defaults for phone and datetime
     */
    public function getAdminDefaults()
    {
        $admin = auth('admin')->user();
        
        $defaults = [
            'phone' => '',
            'datetime' => now()->format('Y-m-d\TH:i'),
            'customer_name' => '',
            'customer_email' => '',
            'order_time' => now()->format('Y-m-d\TH:i'),
            'branch_id' => null,
            'organization_id' => null
        ];

        if ($admin) {
            $defaults['phone'] = $admin->phone ?? '';
            $defaults['branch_id'] = $admin->branch_id;
            $defaults['organization_id'] = $admin->organization_id;
        }

        return $defaults;
    }
}
