<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ItemMaster;
use App\Models\Bill;
use App\Services\OrderService;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OrderManagementController extends Controller
{
    protected $orderService;
    protected $printService;

    public function __construct(OrderService $orderService, PrintService $printService)
    {
        $this->orderService = $orderService;
        $this->printService = $printService;
    }    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }
        
        // Super admins don't have organization restrictions
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return null; // Return null to indicate no restriction
        }
        
        if (!$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    protected function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && $user->isSuperAdmin();
    }public function index(Request $request)
    {        $orgId = $this->getOrganizationId();
        
        $query = Order::with(['items.inventoryItem', 'branch', 'steward', 'reservation']);

        // Apply organization filter only if not super admin
        if ($orgId !== null) {
            $query->whereHas('branch', fn($q) => $q->where('organization_id', $orgId));
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%')
                  ->orWhere('id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('steward_id')) {
            $query->where('steward_id', $request->steward_id);
        }

        // Date filters
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }        $orders = $query->orderByDesc('order_date')->paginate(20);

        // Get filter options
        $branches = $orgId !== null 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $stewards = $orgId !== null 
            ? Employee::stewards()->where('organization_id', $orgId)->active()->get()
            : Employee::stewards()->active()->get();
        $stockAlerts = $this->orderService->getStockAlerts(
            $request->branch_id ?: $branches->first()?->id,
            $orgId
        );

        return view('admin.orders.index', compact(
            'orders',
            'branches',
            'stewards',
            'stockAlerts'
        ));
    }    public function create(Request $request)
    {
        $orgId = $this->getOrganizationId();
        $branchId = $request->get('branch_id') ?: (
            $orgId !== null 
                ? Branch::where('organization_id', $orgId)->first()?->id
                : Branch::first()?->id
        );
        
        if (!$branchId) {
            return redirect()->back()->with('error', 'No branch available for ordering');
        }

        $branches = $orgId !== null 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $stewards = $this->orderService->getAvailableStewards($branchId);
        $items = $this->orderService->getItemsWithStock($branchId, $orgId);
        $stockAlerts = $this->orderService->getStockAlerts($branchId, $orgId);

        return view('admin.orders.enhanced-create', compact(
            'branches',
            'stewards',
            'items',
            'stockAlerts',
            'branchId'
        ));
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'order_type' => 'required|in:' . implode(',', [
                Order::TYPE_TAKEAWAY_IN_CALL,
                Order::TYPE_TAKEAWAY_ONLINE,
                Order::TYPE_TAKEAWAY_WALKIN_SCHEDULED,
                Order::TYPE_TAKEAWAY_WALKIN_DEMAND,
                Order::TYPE_DINEIN_ONLINE,
                Order::TYPE_DINEIN_INCALL,
                Order::TYPE_DINEIN_WALKIN_SCHEDULED,
                Order::TYPE_DINEIN_WALKIN_DEMAND
            ]),
            'steward_id' => 'nullable|exists:employees,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);        // Validate branch belongs to organization (skip for super admin)
        if ($orgId !== null) {
            $branch = Branch::where('id', $validated['branch_id'])
                ->where('organization_id', $orgId)
                ->firstOrFail();
        }

        // Validate steward if provided
        if ($validated['steward_id']) {
            Employee::where('id', $validated['steward_id'])
                ->where('organization_id', $orgId)
                ->where('role', Employee::ROLE_STEWARD)
                ->firstOrFail();
        }

        try {
            $order = $this->orderService->createOrder($validated);

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Order created successfully! KOT has been generated.')
                ->with('print_kot', true);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }    public function show(Order $order)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all orders, others need organization match
        if ($orgId !== null && $order->branch->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $order->load(['items.inventoryItem.category', 'branch', 'steward', 'reservation', 'bills']);
        
        return view('admin.orders.show', compact('order'));
    }    public function edit(Order $order)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all orders, others need organization match
        if ($orgId !== null && $order->branch->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $orgId = $this->getOrganizationId();
        $branches = ($orgId !== null) 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $stewards = $this->orderService->getAvailableStewards($order->branch_id);
        $items = $this->orderService->getItemsWithStock($order->branch_id, $orgId);

        return view('admin.orders.edit', compact('order', 'branches', 'stewards', 'items'));
    }    public function update(Request $request, Order $order)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all orders, others need organization match
        if ($orgId !== null && $order->branch->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'steward_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:' . implode(',', [
                Order::STATUS_SUBMITTED,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED
            ]),
            'items' => 'sometimes|array|min:1',
            'items.*.item_id' => 'required_with:items|exists:item_master,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        try {
            if (isset($validated['items'])) {
                $this->orderService->updateOrder($order, $validated);
            } else {
                $order->update($validated);
            }

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }    public function destroy(Order $order)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all orders, others need organization match
        if ($orgId !== null && $order->branch->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        try {
            $this->orderService->cancelOrder($order, 'Deleted by admin');

            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'Order cancelled and stock restored successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error cancelling order: ' . $e->getMessage());
        }
    }

    /**
     * Print KOT for order
     */
    public function printKOT(Order $order)
    {
        if ($order->branch->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $kotData = $this->printService->generateKOTData($order);
        
        return view('prints.kot', $kotData);
    }

    /**
     * Generate and show bill for order
     */
    public function generateBill(Order $order)
    {
        if ($order->branch->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        try {
            DB::transaction(function () use ($order) {
                // Mark order as completed
                $order->markAsCompleted();
                
                // Create bill if not exists
                if (!$order->bills()->exists()) {
                    Bill::createFromOrder($order);
                }
            });

            $bill = $order->bills()->latest()->first();
            
            return redirect()
                ->route('admin.bills.show', $bill)
                ->with('success', 'Bill generated successfully!')
                ->with('print_bill', true);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error generating bill: ' . $e->getMessage());
        }
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $orgId = $this->getOrganizationId();
        
        $query = Order::with(['items.inventoryItem', 'branch', 'steward'])
            ->whereHas('branch', fn($q) => $q->where('organization_id', $orgId));

        // Apply same filters as index
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $orders = $query->orderByDesc('order_date')->get();
        $csvData = $this->printService->formatForCSV($orders, 'orders');

        $filename = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($csvData->isNotEmpty()) {
                fputcsv($file, array_keys($csvData->first()));
            }
            
            // Add data
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get items with stock for AJAX requests
     */
    public function getItemsWithStock(Request $request)
    {
        $branchId = $request->get('branch_id');
        $orgId = $this->getOrganizationId();

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }

        $items = $this->orderService->getItemsWithStock($branchId, $orgId);
        
        return response()->json($items);
    }

    /**
     * Get stewards for AJAX requests
     */
    public function getStewards(Request $request)
    {
        $branchId = $request->get('branch_id');

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }

        $stewards = $this->orderService->getAvailableStewards($branchId);
        
        return response()->json($stewards);
    }

    /**
     * Get stock alerts for AJAX requests
     */
    public function getStockAlerts(Request $request)
    {
        $branchId = $request->get('branch_id');
        $orgId = $this->getOrganizationId();

        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }

        $stockAlerts = $this->orderService->getStockAlerts($branchId, $orgId);
        
        return response()->json($stockAlerts);
    }

    public function getAvailableStewards(Request $request)
    {
        return $this->getStewards($request);
    }
}
