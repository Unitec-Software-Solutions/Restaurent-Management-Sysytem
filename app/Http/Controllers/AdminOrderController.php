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
use App\Models\ItemCategory;
use App\Models\ItemTransaction;
use App\Models\Organization;
use App\Models\StockReservation;
use App\Traits\Exportable;
use App\Enums\OrderType;
use App\Services\NotificationService;
use App\Services\OrderNumberService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{

    /**
     * Generate and download KOT PDF (Kitchen Order Ticket) for admin
     */

    /**
     * Generate and download KOT PDF (Kitchen Order Ticket) for admin
     */
    public function printKOTPDF($orderId)
    {
        $order = Order::with([
            'orderItems.menuItem',
            'branch',
            'reservation',
            'customer'
        ])->findOrFail($orderId);

        // Mark KOT as generated
        $order->update(['kot_generated' => true]);

        // Use DomPDF (Barryvdh\DomPDF)
        $pdf = PDF::loadView('admin.orders.kot-pdf', compact('order'));
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'DejaVu Sans Mono',
            'fontDir' => storage_path('fonts/'),
            'fontCache' => storage_path('fonts/'),
            'tempDir' => storage_path('app/temp/'),
            'chroot' => public_path(),
        ]);
        $filename = 'KOT-' . ($order->order_number ?? $order->id) . '-' . now()->format('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    public function index(Request $request)
{
    $admin = auth('admin')->user();
    $search = $request->input('search');
    $status = $request->input('status');
    $orderType = $request->input('order_type');

    $orders = Order::with(['branch', 'orderItems.menuItem'])
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                foreach ($this->getSearchableColumns() as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
                $q->orWhereHas('orderItems.menuItem', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        })
        ->when($status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($orderType, function ($query, $orderType) {
            $query->where('order_type', $orderType);
        })
        ->when(!$admin->is_super_admin, function ($query) use ($admin) {
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            } elseif ($admin->organization_id) {
                $query->whereHas('branch', function ($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                });
            }
        })
        ->orderBy('order_time', 'desc')
        ->paginate(25);

    $statusOptions = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    $orderTypes = collect(OrderType::cases())->mapWithKeys(function ($case) {
        return [$case->value => $case->name];
    })->toArray();

    // Always pass branches for filter dropdown
    $branches = $this->getAdminAccessibleBranches($admin);
    return view('admin.orders.index', compact('orders', 'statusOptions', 'orderTypes', 'branches'));
}

public function create()
{
    $admin = auth('admin')->user();
    if (!$admin->can('create', Order::class)) {
        abort(403, 'You do not have permission to create orders.');
    }

    $branches = $this->getAdminAccessibleBranches($admin);
    if ($branches->isEmpty()) {
        return redirect()->route('admin.dashboard')->with('error', 'No branches available. Please contact your administrator.');
    }

    $menuItems = collect([]);
    $categories = collect([]);

    // If admin is restricted to one branch, preload menu items
    if ($admin->branch_id) {
        $activeMenu = Menu::getActiveMenuForBranch($admin->branch_id);
        if ($activeMenu) {
            $menuItems = $activeMenu->availableMenuItems()->with('itemMaster')->get();
        }

        $categories = ItemCategory::where('organization_id', $admin->organization_id)
            ->active()
            ->get();
    }

    $statusOptions = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    return view('admin.orders.create', compact('branches', 'menuItems', 'categories', 'statusOptions'));
}

public function show(Order $order)
{
    $admin = auth('admin')->user();

    // Authorization check
    if (!$admin->is_super_admin) {
        if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
            abort(403, 'Access denied to this order');
        } elseif ($admin->organization_id && (!$order->branch || $order->branch->organization_id !== $admin->organization_id)) {
            abort(403, 'Access denied to this order');
        }
    }

    $order->load([
        'orderItems.menuItem.itemMaster',
        'branch.organization',
        'createdByAdmin'
    ]);

    // Prepare data for view
    $orderDetails = [
        'id' => $order->id,
        'order_number' => $order->order_number,
        'customer_name' => $order->customer_name,
        'customer_phone' => $order->customer_phone,
        'status' => $order->status,
        'order_type' => $order->order_type,
        'order_time' => $order->order_time->format('M d, Y h:i A'),
        'subtotal' => number_format((float)$order->subtotal, 2),
        'tax' => number_format((float)$order->tax, 2),
        'total' => number_format((float)$order->total, 2),
        'branch_name' => $order->branch->name,
        'created_by' => $order->createdByAdmin->name ?? 'System',
        'special_instructions' => $order->special_instructions,
        'items' => $order->orderItems->map(function ($item) {
            return [
                'name' => $item->menuItem->name,
                'quantity' => $item->quantity,
                'unit_price' => number_format($item->unit_price, 2),
                'total' => number_format($item->total_price, 2),
                'special_instructions' => $item->special_instructions
            ];
        })
    ];

    return view('admin.orders.show', compact('order', 'orderDetails'));
}

// Helper method to get accessible branches (already used in other methods)
protected function getAdminAccessibleBranches($admin)
{
    if ($admin->is_super_admin) {
        return Branch::active()->get();
    } elseif ($admin->organization_id) {
        $query = Branch::where('organization_id', $admin->organization_id)->active();
        if ($admin->branch_id) {
            $query->where('id', $admin->branch_id);
        }
        return $query->get();
    } else {
        return collect([]);
    }
}

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->can('create', Order::class)) {
            abort(403, 'You do not have permission to create orders.');
        }

        $data = $request->validate([
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
            'customer_email' => 'nullable|email|max:255',
            'reservation_id' => 'nullable|exists:reservations,id',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'preferred_contact' => 'nullable|string|in:email,sms',
        ]);

        DB::beginTransaction();
        try {
            // Initialize required fields with default values
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'order_type' => $data['order_type'],
                'order_time' => $data['order_time'],
                'special_instructions' => $data['special_instructions'] ?? null,
                'status' => 'pending',
                'created_by' => $admin->id,
                'order_date' => now(),
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'service_charge' => 0,
                'delivery_fee' => 0,
                'total_amount' => 0,
                'total' => 0,
                'currency' => 'USD' // or your default currency
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                $lineTotal = ($menuItem?->price ?? 0) * $item['quantity'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem?->price ?? 0,
                    'subtotal' => $lineTotal,
                    'total_price' => $lineTotal,
                ]);

                if ($menuItem && $menuItem->itemMaster) {
                    $itemMaster = $menuItem->itemMaster;
                    $currentStock = $itemMaster->current_stock ?? 0;
                    $newStock = $currentStock - $item['quantity'];
                    $itemMaster->current_stock = $newStock;
                    $itemMaster->save();
                }
            }

            $tax = $subtotal * 0.10;
            // Calculate all amounts
            $serviceCharge = $subtotal * 0.05; // 5% service charge
            $total = $subtotal + $tax + $serviceCharge;
            
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'tax_amount' => $tax,
                'service_charge' => $serviceCharge,
                'total' => $total,
                'total_amount' => $total,
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->route('admin.orders.show', $order)
                ->with('success', "Order created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Failed to create order: {$e->getMessage()}");
        }
    }

    public function update(Request $request, Order $order)
    {
        $orderTimeRule = $order->id ? 'required|date|after_or_equal:now' : 'required|date|after_or_equal:now';

        $validated = $request->validate([
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled',
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => $orderTimeRule,
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|min:10|max:15',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $order->update([
                'status' => $validated['status'],
                'order_type' => $validated['order_type'],
                'branch_id' => $validated['branch_id'],
                'order_time' => $validated['order_time'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone']
            ]);

            if ($request->has('items') && is_array($request->items)) {
                $selectedItems = [];
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['item_id'])) {
                        $selectedItems[$itemId] = [
                            'item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'] ?? 1
                        ];
                    }
                }

                Log::debug('Selected items for order #' . $order->id, [
                    'items' => $selectedItems,
                    'raw' => $request->items
                ]);

                if (!empty($selectedItems)) {
                    $order->orderItems()->delete();

                    $subtotal = 0;
                    foreach ($selectedItems as $itemData) {
                        $menuItem = ItemMaster::find($itemData['item_id']);
                        if (!$menuItem) continue;

                        $lineTotal = $menuItem->selling_price * $itemData['quantity'];
                        $subtotal += $lineTotal;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_item_id' => $itemData['item_id'],
                            'inventory_item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $menuItem->selling_price,
                            'subtotal' => $lineTotal,
                            'total_price' => $lineTotal
                        ]);
                    }

                    $tax = $subtotal * 0.10;
                    $order->update([
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $subtotal + $tax
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function edit(Order $order)
    {
        $admin = auth('admin')->user();

        if (!$admin->is_super_admin) {
            if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Access denied to this order');
            } elseif ($admin->organization_id && $order->branch && $order->branch->organization_id !== $admin->organization_id) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Access denied to this order');
            }
        }

        $order->load(['orderItems.menuItem', 'branch']);
        $branches = $this->getAdminAccessibleBranches($admin);
        if (!$branches || $branches->isEmpty()) {
            $branches = collect([]);
        }

        $activeMenu = null;
        $menuItems = collect([]);
        if ($order->branch_id) {
            $activeMenu = Menu::getActiveMenuForBranch($order->branch_id);
            if ($activeMenu) {
                $menuItems = $activeMenu->availableMenuItems()->with('itemMaster')->get();
            }
        }

        if ($menuItems->isEmpty()) {
            $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
                ->where('is_menu_item', true)
                ->where('is_active', true)
                ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })
                ->get();
        }

        foreach ($menuItems as $item) {
            $item->current_stock = ItemTransaction::stockOnHand($item->id, $order->branch_id);
            $item->is_low_stock = $item->current_stock <= ($item->reorder_level ?? 10);
        }

        $categories = ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->active()
            ->get();

        $statusOptions = [
            'submitted' => 'Submitted',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];

        return view('admin.orders.edit', compact('order', 'statusOptions', 'branches', 'menuItems', 'categories', 'activeMenu'));
    }

    protected function getSearchableColumns(): array
    {
        return ['customer_name', 'customer_phone', 'id'];
    }

    private function checkMenuItemStock($menuItem, $quantity, $branchId)
    {
        return [
            'available' => true,
            'available_quantity' => 999999,
            'message' => 'Item available'
        ];
    }

    public function getBranches(Request $request)
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

            $query = Branch::where('organization_id', $organizationId)
                ->where('is_active', true);

            if (!$admin->is_super_admin) {
                if ($admin->organization_id && $admin->organization_id != $organizationId) {
                    return response()->json(['error' => 'Access denied'], 403);
                }

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

    public function destroy($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $reservationId = $request->input('reservation_id', $order->reservation_id);
        // Deletion of orders is forbidden for all admin roles
        abort(403, 'Order deletion is forbidden for all admin roles.');
        return redirect()->route('orders.index', ['reservation_id' => $reservationId])
            ->with('success', 'Order deleted successfully.');
    }
    public function destroyTakeaway($id)
    {
        $order = Order::findOrFail($id);
        $order->orderItems()->delete();
        $order->delete();
        return redirect()->route('orders.index')
            ->with('success', 'Takeaway order deleted successfully.');
    }
}
