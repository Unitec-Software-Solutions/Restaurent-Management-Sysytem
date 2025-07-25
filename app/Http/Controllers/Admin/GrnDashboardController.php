<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\PurchaseOrder;
use App\Models\ItemMaster;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;

class GrnDashboardController extends Controller
{
    /**
     * Item categories allowed for GRN (Goods Received Note)
     * These are items that can be purchased/received from suppliers
     * Add more categories here as needed
     */
    const ALLOWED_GRN_CATEGORIES = [
        // Add more categories here as needed:
        'Buy & sell',
        'Buy & Sell',
        'Ingredients',
        'Buy_&_Sell',
        'Raw Materials'
    ];

    protected function getOrganizationId()
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // For super admin, return null to allow access to all organizations
        if ($user->is_super_admin) {
            return null;
        }

        if (!$user->organization_id) {
            abort(403, 'No organization assigned');
        }

        return $user->organization_id;
    }

    /**
     * Apply organization filter to query if user is not super admin
     */
    protected function applyOrganizationFilter($query, $orgId)
    {
        if ($orgId !== null) {
            return $query->where('organization_id', $orgId);
        }
        return $query;
    }

    /**
     * Check if user can access record from specific organization
     */
    protected function canAccessOrganization($recordOrgId, $userOrgId)
    {
        return $userOrgId === null || $recordOrgId === $userOrgId;
    }

    /**
     * Create organization validation rule
     */
    protected function createOrganizationValidationRule($table, $orgId)
    {
        return function ($attribute, $value, $fail) use ($table, $orgId) {
            if ($orgId !== null) {
                $exists = DB::table($table)
                    ->where('id', $value)
                    ->where('organization_id', $orgId)
                    ->exists();
                if (!$exists) {
                    $fail("The selected {$attribute} does not belong to your organization.");
                }
            }
        };
    }

    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();

        // Set default date range: 30 days back to today
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = GrnMaster::with(['supplier', 'branch', 'verifiedByUser', 'purchaseOrder']);

        // Apply organization filter for non-super admins
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }

    // Apply search filter (removed the code column search)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('grn_number', 'like', "%{$search}%")
                ->orWhere('delivery_note_number', 'like', "%{$search}%")
                ->orWhere('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                    // Removed: ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%");
                });
        });
    }


        // Apply status filter
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Apply branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Apply payment status filter (new filter)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Apply date range filter
        $query->whereBetween('received_date', [$startDate, $endDate]);

        // Sorting
        $sortBy = $request->input('sort_by', 'received_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'grn_export.xlsx', [
                'GRN Number', 'Supplier', 'Branch', 'Status', 'Payment Status', 'Total Amount', 'Received Date'
            ]);
        }

        $grns = $query->paginate(10);

        // Statistics query
        $statsQuery = GrnMaster::query();
        $this->applyOrganizationFilter($statsQuery, $orgId);
        $statsQuery->whereBetween('received_date', [$startDate, $endDate]);

        $stats = [
            'total_grns' => $statsQuery->count(),
            'pending_verification' => $statsQuery->clone()->where('status', GrnMaster::STATUS_PENDING)->count(),
            'verified_grns' => $statsQuery->clone()->where('status', GrnMaster::STATUS_VERIFIED)->count(),
            'rejected_grns' => $statsQuery->clone()->where('status', GrnMaster::STATUS_REJECTED)->count(),
            'total_amount' => $statsQuery->clone()->sum('total_amount'),
            'monthly_amount' => $statsQuery->clone()
                ->whereBetween('received_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount'),
        ];

        // Get filter options
        $branches = $this->applyOrganizationFilter(Branch::query(), $orgId)->active()->get();
        $suppliers = $this->applyOrganizationFilter(Supplier::query(), $orgId)->active()->get();

        // Status options for filter
        $statusOptions = [
            GrnMaster::STATUS_PENDING => 'Pending',
            GrnMaster::STATUS_VERIFIED => 'Verified',
            GrnMaster::STATUS_REJECTED => 'Rejected',
            GrnMaster::STATUS_PARTIAL => 'Partially Verified',
            GrnMaster::STATUS_COMPLETED => 'Completed'
        ];

        // Payment status options for filter
        $paymentStatusOptions = [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid'
        ];

        return view('admin.suppliers.grn.index', compact(
            'grns',
            'stats',
            'branches',
            'suppliers',
            'startDate',
            'endDate',
            'statusOptions',
            'paymentStatusOptions'
        ));
    }

    public function edit(GrnMaster $grn)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to edit GRNs.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $orgId = $this->getOrganizationId();

        // For non-super admin, check organization access
        if (!$isSuperAdmin && $grn->organization_id !== $orgId) {
            abort(403);
        }

        if (!$grn->isPending()) {
            return redirect()->route('admin.grn.show', $grn)
                ->with('error', 'Only pending GRNs can be edited');
        }

        // Use the GRN's organization for data filtering
        $targetOrgId = $grn->organization_id;

        // Filter items by category for GRN - only show items that can be purchased
        $allowedCategories = self::ALLOWED_GRN_CATEGORIES;
        $items = ItemMaster::where('organization_id', $targetOrgId)
            ->active()
            ->whereHas('category', function($q) use ($allowedCategories) {
                $q->whereIn('name', $allowedCategories);
            })
            ->with('category')
            ->orderBy('name')
            ->get();

        $grn->load(['items.item', 'purchaseOrder.items']);

        $suppliers = Supplier::where('organization_id', $targetOrgId)
            ->active()
            ->get();

        $branches = Branch::where('organization_id', $targetOrgId)
            ->active()
            ->get();

        $purchaseOrders = PurchaseOrder::where('organization_id', $targetOrgId)
            ->where('supplier_id', $grn->supplier_id)
            ->where('status', 'Approved')
            ->with(['items'])
            ->get();

        foreach ($grn->items as $item) {
            if ($item->item) {
                $item->item_name = $item->item->name;
            }
        }

        return view('admin.suppliers.grn.edit', compact(
            'grn',
            'suppliers',
            'branches',
            'purchaseOrders',
            'items'
        ));
    }

    public function update(Request $request, GrnMaster $grn)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to update GRNs.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        $orgId = $this->getOrganizationId();

        // For non-super admin, check organization access
        if (!$isSuperAdmin && $grn->organization_id !== $orgId) {
            abort(403);
        }

        if (!$grn->isPending()) {
            return back()->with('error', 'Only pending GRNs can be updated');
        }

        // Use the GRN's organization for validation
        $targetOrgId = $grn->organization_id;

        Log::info('GRN Update Request Received', ['grn_id' => $grn->grn_id, 'data' => $request->all()]);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'exists:branches,id',
                function ($attribute, $value, $fail) use ($targetOrgId) {
                    if (!Branch::where('id', $value)->where('organization_id', $targetOrgId)->exists()) {
                        $fail('The selected branch does not belong to the organization.');
                    }
                }
            ],
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                function ($attribute, $value, $fail) use ($targetOrgId) {
                    if (!Supplier::where('id', $value)->where('organization_id', $targetOrgId)->exists()) {
                        $fail('The selected supplier does not belong to the organization.');
                    }
                }
            ],
            'received_date' => 'required|date|before_or_equal:today',
            'delivery_note_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => [
                'required',
                'exists:item_master,id',
                function ($attribute, $value, $fail) use ($targetOrgId) {
                    if (!ItemMaster::where('id', $value)->where('organization_id', $targetOrgId)->exists()) {
                        $fail('The selected item does not belong to the organization.');
                    }
                }
            ],
            'items.*.item_code' => 'required|exists:item_master,item_code',
            'items.*.po_detail_id' => 'nullable|exists:po_details,po_detail_id',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $receivedQty = $request->input("items.$index.received_quantity", 0);
                    $rejectedQty = $request->input("items.$index.rejected_quantity", 0);

                    if ($value + $rejectedQty > $receivedQty) {
                        $fail('Accepted quantity plus rejected quantity cannot exceed received quantity.');
                    }
                }
            ],
            'items.*.free_received_quantity' => 'nullable|numeric|min:0',
            'items.*.discount_received' => 'nullable|numeric|min:0',
            'items.*.rejected_quantity' => 'required|numeric|min:0',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.manufacturing_date' => 'nullable|date|before_or_equal:today',
            'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
            'items.*.rejection_reason' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $rejectedQuantity = $request->input("items.$index.rejected_quantity", 0);
                    if ($rejectedQuantity > 0 && empty($value)) {
                        $fail("The rejection reason is required when rejected quantity is greater than 0.");
                    }
                },
            ],
            'grand_discount' => 'nullable|numeric|min:0',
        ]);

        Log::info('GRN Data Validated', ['grn_id' => $grn->grn_id, 'validated' => $validated]);

        // Perform additional calculation validation
        $calculationResult = $this->validateGrnCalculations($validated['items'], $validated['grand_discount'] ?? 0);
        if (!empty($calculationResult['errors'])) {
            return back()->withInput()
                ->with('error', 'Calculation errors: ' . implode(', ', $calculationResult['errors']));
        }

        DB::beginTransaction();
        try {
            $grn->update([
                'branch_id' => $validated['branch_id'],
                'supplier_id' => $validated['supplier_id'],
                'received_date' => $validated['received_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'invoice_number' => $validated['invoice_number'],
                'notes' => $validated['notes'],
                'grand_discount' => $validated['grand_discount'] ?? 0,
            ]);

            Log::info('GRN Master Updated', ['grn_id' => $grn->grn_id]);

            $grn->items()->delete();

            $total = 0;
            $hasCalculationErrors = false;
            $calculationErrors = [];

            foreach ($validated['items'] as $index => $item) {
                // Validate item belongs to organization
                $itemMaster = ItemMaster::where('id', $item['item_id'])
                    ->where('organization_id', $targetOrgId)
                    ->first();

                if (!$itemMaster) {
                    $hasCalculationErrors = true;
                    $calculationErrors[] = "Item at position " . ($index + 1) . " does not belong to the organization.";
                    continue;
                }

                // Validate quantity consistency
                $acceptedQty = $item['accepted_quantity'];
                $rejectedQty = $item['rejected_quantity'];
                $receivedQty = $item['received_quantity'];
                $freeQty = $item['free_received_quantity'] ?? 0;
                $discount = $item['discount_received'] ?? 0;

                if (($acceptedQty + $rejectedQty) > $receivedQty) {
                    $hasCalculationErrors = true;
                    $calculationErrors[] = "Item '{$itemMaster->name}': Accepted + Rejected quantities ({$acceptedQty} + {$rejectedQty}) cannot exceed received quantity ({$receivedQty}).";
                    continue;
                }

                // Calculate line total: (accepted_quantity * buying_price) - discount
                $baseAmount = $acceptedQty * $item['buying_price'];
                $lineTotal = max(0, $baseAmount - $discount); // Ensure non-negative
                $total += $lineTotal;

                GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'po_detail_id' => $item['po_detail_id'] ?? null,
                    'item_id' => $item['item_id'],
                    'item_code' => $itemMaster->item_code,
                    'item_name' => $itemMaster->name,
                    'batch_no' => $item['batch_no'] ?? (date('Y') . '-' . str_pad((GrnItem::max('grn_item_id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)),
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                    'manufacturing_date' => $item['manufacturing_date'],
                    'expiry_date' => $item['expiry_date'],
                    'rejection_reason' => $item['rejection_reason'],
                    'free_received_quantity' => $freeQty,
                    'discount_received' => $discount,
                ]);
            }

            if ($hasCalculationErrors) {
                throw new \Exception('Calculation errors: ' . implode(' ', $calculationErrors));
            }

            Log::info('GRN Items Created', ['grn_id' => $grn->grn_id, 'item_count' => count($validated['items'])]);

            $grn->update(['total_amount' => $total]);

            // Apply grand discount after calculating line totals
            if (($validated['grand_discount'] ?? 0) > 0) {
                $grandDiscountAmount = $total * (($validated['grand_discount'] ?? 0) / 100);
                $finalTotal = max(0, $total - $grandDiscountAmount);
                $grn->update(['total_amount' => $finalTotal, 'grand_discount' => $validated['grand_discount'] ?? 0]);
            } else {
                $grn->update(['grand_discount' => $validated['grand_discount'] ?? 0]);
            }

            DB::commit();
            Log::info('GRN Update Committed', ['grn_id' => $grn->grn_id]);
            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN Update Failed', ['grn_id' => $grn->grn_id, 'error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error updating GRN: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create GRNs.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        $orgId = $this->getOrganizationId();

        // Get organizations for super admin dropdown
        $organizations = $isSuperAdmin ? Organization::active()->get() : collect();

        // Filter items by category for GRN - only show items that can be purchased
        $allowedCategories = self::ALLOWED_GRN_CATEGORIES;
        $items = $this->applyOrganizationFilter(ItemMaster::query(), $orgId)
            ->active()
            ->whereHas('category', function($q) use ($allowedCategories) {
                $q->whereIn('name', $allowedCategories);
            })
            ->with('category')
            ->orderBy('name')
            ->get();

        $suppliers = $this->applyOrganizationFilter(Supplier::query(), $orgId)->active()->get();
        $branches = $this->applyOrganizationFilter(Branch::query(), $orgId)->active()->get();
        $purchaseOrders = $this->applyOrganizationFilter(PurchaseOrder::query(), $orgId)
            ->where('status', 'Approved')
            ->with(['supplier', 'items'])
            ->get();

        return view('admin.suppliers.grn.create', compact(
            'items',
            'suppliers',
            'branches',
            'purchaseOrders',
            'organizations'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create GRNs.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // Basic validation - only non-super admins need organization
        if (!$isSuperAdmin && !$user->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        $orgId = $this->getOrganizationId();

        $validationRules = [
            'branch_id' => [
                'required',
                'exists:branches,id',
                $this->createOrganizationValidationRule('branches', $orgId)
            ],
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                $this->createOrganizationValidationRule('suppliers', $orgId)
            ],
            'received_date' => 'required|date|before_or_equal:today',
            'delivery_note_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => [
                'required',
                'exists:item_master,id',
                $this->createOrganizationValidationRule('item_master', $orgId)
            ],
            'items.*.item_code' => 'nullable|exists:item_master,item_code',
            'items.*.batch_no' => 'nullable|string|max:50',
            'items.*.ordered_quantity' => 'required|numeric|min:0',
            'items.*.received_quantity' => 'required|numeric|min:0|max:999999.99',
            'items.*.buying_price' => 'required|numeric|min:0|max:999999.9999',
            'items.*.discount_received' => 'nullable|numeric|min:0|max:999999.99',
            'items.*.free_received_quantity' => 'nullable|numeric|min:0|max:999999.99',
            'items.*.manufacturing_date' => 'nullable|date|before_or_equal:today',
            'items.*.expiry_date' => 'nullable|date|after:items.*.manufacturing_date',
            'grand_discount' => 'nullable|numeric|min:0|max:99.99',
        ];

        // Super admin must select organization
        if ($isSuperAdmin) {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($validationRules);

        // For super admin, use the selected organization; for others, use their assigned organization
        if ($isSuperAdmin) {
            $targetOrgId = $request->organization_id;
        } else {
            $targetOrgId = $user->organization_id;
        }

        Log::info('GRN Store Request Received', ['org_id' => $targetOrgId, 'data' => $request->all()]);

        DB::beginTransaction();
        try {
            // Additional validation to ensure items belong to organization
            $hasValidationErrors = false;
            $validationErrors = [];

            foreach ($validated['items'] as $index => $item) {
                $itemMaster = ItemMaster::where('id', $item['item_id'])
                    ->where('organization_id', $targetOrgId)
                    ->first();

                if (!$itemMaster) {
                    $hasValidationErrors = true;
                    $validationErrors[] = "Item at position " . ($index + 1) . " does not belong to the selected organization.";
                    continue;
                }

                // Check if received quantity is reasonable
                if ($item['received_quantity'] > ($item['ordered_quantity'] * 1.5)) {
                    $validationErrors[] = "Item '{$itemMaster->name}': Received quantity ({$item['received_quantity']}) seems unusually high compared to ordered quantity ({$item['ordered_quantity']}).";
                }
            }

            if ($hasValidationErrors) {
                throw new \Exception('Validation errors: ' . implode(' ', $validationErrors));
            }

            $userId = $request->input('received_by_user_id');
            $userExists = DB::table('users')->where('id', $userId)->exists();
            $grn = GrnMaster::create([
                'grn_number' => GrnMaster::generateGRNNumber($targetOrgId),
                'branch_id' => $validated['branch_id'],
                'organization_id' => $targetOrgId,
                'supplier_id' => $validated['supplier_id'],
                'received_by_user_id' => $userExists ? $userId : null,
                'received_date' => $validated['received_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'invoice_number' => $validated['invoice_number'],
                'notes' => $validated['notes'],
                'status' => GrnMaster::STATUS_PENDING,
                'is_active' => true,
                'created_by' => Auth::guard('admin')->id(),
                'grand_discount' => $validated['grand_discount'] ?? 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $itemMaster = ItemMaster::where('id', $item['item_id'])
                    ->where('organization_id', $targetOrgId)
                    ->firstOrFail();

                // Correct calculation: (received_quantity * buying_price) - discount
                $baseAmount = $item['received_quantity'] * $item['buying_price'];
                $discountAmount = $item['discount_received'] ?? 0;
                $lineTotal = max(0, $baseAmount - $discountAmount); // Ensure non-negative
                $total += $lineTotal;

                GrnItem::create([
                    'grn_id' => $grn->grn_id,
                    'item_id' => $item['item_id'],
                    'item_code' => $itemMaster->item_code,
                    'item_name' => $itemMaster->name,
                    'batch_no' => $item['batch_no'] ?? (date('Y') . '-' . str_pad((GrnItem::max('grn_item_id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)),
                    'ordered_quantity' => $item['ordered_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => $item['received_quantity'], // Initially all received items are accepted
                    'rejected_quantity' => 0,
                    'buying_price' => $item['buying_price'],
                    'line_total' => $lineTotal,
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'free_received_quantity' => $item['free_received_quantity'] ?? 0,
                    'discount_received' => $discountAmount,
                ]);
            }

            // Apply grand discount to the total
            $grandDiscountAmount = 0;
            if (($validated['grand_discount'] ?? 0) > 0) {
                $grandDiscountAmount = $total * (($validated['grand_discount'] ?? 0) / 100);
            }

            $finalTotal = max(0, $total - $grandDiscountAmount);
            $grn->update(['total_amount' => $finalTotal]);

            DB::commit();
            Log::info('GRN Created Successfully', ['grn_id' => $grn->grn_id, 'total_amount' => $finalTotal]);

            return redirect()->route('admin.grn.show', $grn)
                ->with('success', 'GRN created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN Store Failed', ['org_id' => $targetOrgId, 'error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error creating GRN: ' . $e->getMessage());
        }
    }

    public function show(GrnMaster $grn)
    {
        $orgId = $this->getOrganizationId();
        if (!$this->canAccessOrganization($grn->organization_id, $orgId)) {
            abort(403);
        }

        $grn->load([
            'items.item',
            'items.purchaseOrderDetail',
            'purchaseOrder',
            'supplier',
            'branch',
            'receivedByUser',
            'verifiedByUser',
            'createdByUser'
        ]);

        return view('admin.suppliers.grn.show', compact('grn'));
    }

    public function print(GrnMaster $grn)
    {
        $orgId = $this->getOrganizationId();
        if (!$this->canAccessOrganization($grn->organization_id, $orgId)) {
            abort(403);
        }

        $grn->load([
            'items.item',
            'items.purchaseOrderDetail',
            'purchaseOrder',
            'supplier',
            'branch',
            'receivedByUser',
            'verifiedByUser',
            'createdByUser'
        ]);

        $organization = Organization::find($orgId);
        $printedDate = now()->format('M d, Y h:i A');

        return view('admin.suppliers.grn.print', compact(
            'grn',
            'organization',
            'printedDate'
        ));
    }

    public function verify(Request $request, GrnMaster $grn)
    {
        Log::info('Starting GRN verification', ['grn_id' => $grn->grn_id]);

        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $user->is_super_admin;

        // For non-super admin, check organization access
        if (!$isSuperAdmin && !$this->canAccessOrganization($grn->organization_id, $orgId)) {
            Log::error('Unauthorized access to GRN verification', ['grn_id' => $grn->grn_id, 'user_org' => $orgId, 'grn_org' => $grn->organization_id]);
            abort(403);
        }

        if (!$grn->isPending()) {
            Log::warning('Attempt to verify a non-pending GRN', ['grn_id' => $grn->grn_id, 'status' => $grn->status]);
            return back()->with('error', 'Only pending GRNs can be verified.');
        }

        $validated = $request->validate([
            'status' => 'required|in:' . GrnMaster::STATUS_VERIFIED . ',' . GrnMaster::STATUS_REJECTED,
            'notes' => 'nullable|string|max:1000'
        ]);

        // Validate status transition
        if (!$this->isValidStatusTransition($grn->status, $validated['status'])) {
            return back()->with('error', 'Invalid status transition.');
        }

        // Additional validation for verification
        if ($validated['status'] === GrnMaster::STATUS_VERIFIED) {
            $grn->load('items');

            // Check if GRN has items
            if ($grn->items->isEmpty()) {
                return back()->with('error', 'Cannot verify GRN without items.');
            }

            // Validate all items have valid quantities
            foreach ($grn->items as $item) {
                if ($item->accepted_quantity < 0 || $item->rejected_quantity < 0) {
                    return back()->with('error', 'Invalid quantities found in GRN items.');
                }

                if (($item->accepted_quantity + $item->rejected_quantity) != $item->received_quantity) {
                    return back()->with('error', 'Quantity mismatch found in GRN items. Please review and correct.');
                }
            }
        }

        DB::beginTransaction();
        try {
            Log::info('Updating GRN status', ['grn_id' => $grn->grn_id, 'new_status' => $validated['status']]);

            $grn->verified_by_user_id = Auth::guard('admin')->id();
            $grn->verified_at = now();
            $grn->status = $validated['status'];
            $grn->notes = $validated['notes'] ?? $grn->notes;
            $grn->save();

            if ($validated['status'] === GrnMaster::STATUS_VERIFIED) {
                // Check if this GRN was created from a GTN
                $isFromGTN = (strpos($grn->notes ?? '', 'Internal transfer from GTN #') === 0) ||
                             (strpos($grn->delivery_note_number ?? '', 'GTN-') === 0);

                $transactionType = $isFromGTN ? 'gtn_stock_in' : 'grn_stock_in';
                $sourceType = $isFromGTN ? 'App\\Models\\GoodsTransferNote' : 'supplier';

                Log::info('Determining GRN source', [
                    'grn_id' => $grn->grn_id,
                    'is_from_gtn' => $isFromGTN,
                    'transaction_type' => $transactionType,
                    'notes' => $grn->notes,
                    'delivery_note' => $grn->delivery_note_number
                ]);

                $transactionErrors = [];
                foreach ($grn->items as $grnItem) {
                    $qty = $grnItem->accepted_quantity + ($grnItem->free_received_quantity ?? 0);

                    // Always use a positive value for quantity for stock in
                    $qty = abs($qty);

                    if ($qty > 0) {
                        try {
                            // For GTN-based GRNs, ensure accepted_quantity is set
                            if ($isFromGTN && $grnItem->accepted_quantity <= 0) {
                                $grnItem->accepted_quantity = $grnItem->received_quantity;
                                $grnItem->save();
                                $qty = $grnItem->accepted_quantity + ($grnItem->free_received_quantity ?? 0);

                                Log::info('Updated GTN-based GRN item accepted quantity', [
                                    'grn_id' => $grn->grn_id,
                                    'item_id' => $grnItem->item_id,
                                    'accepted_quantity' => $grnItem->accepted_quantity,
                                    'new_qty' => $qty
                                ]);
                            }

                            // Fix: reference_id must be integer (grn_id), not string (grn_number or batch_no)
                            $referenceId = $grn->grn_id;

                            // Create the item transaction with appropriate values
                            $transaction = ItemTransaction::create([
                                'organization_id' => $grn->organization_id,
                                'branch_id' => $grn->branch_id,
                                'inventory_item_id' => $grnItem->item_id,
                                'transaction_type' => $transactionType,
                                'quantity' => $qty, // Always positive for incoming stock
                                'received_quantity' => $grnItem->received_quantity,
                                'damaged_quantity' => $grnItem->rejected_quantity ?? 0,
                                // Use appropriate cost calculations
                                'cost_price' => $isFromGTN ? 0 : ($grnItem->buying_price * $grnItem->accepted_quantity),
                                'unit_price' => $isFromGTN ? 0 : $grnItem->buying_price,
                                'reference_id' => $referenceId, // Always integer
                                'reference_type' => $sourceType,
                                'created_by_user_id' => Auth::guard('admin')->id(),
                                'notes' => $isFromGTN
                                    ? 'Stock received from GTN #' . $grn->delivery_note_number
                                    : 'Stock added from GRN #' . $grn->grn_number,
                                'is_active' => true,
                            ]);

                            Log::info('Created stock transaction', [
                                'transaction_id' => $transaction->id,
                                'grn_id' => $grn->grn_id,
                                'item_id' => $grnItem->item_id,
                                'qty' => $qty,
                                'transaction_type' => $transactionType,
                                'is_from_gtn' => $isFromGTN
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to create stock transaction', [
                                'grn_id' => $grn->grn_id,
                                'item_id' => $grnItem->item_id,
                                'error' => $e->getMessage()
                            ]);
                            $transactionErrors[] = "Failed to create stock transaction for item {$grnItem->item_name}: " . $e->getMessage();
                        }
                    } else {
                        Log::warning('Skipped creating transaction for zero quantity item', [
                            'grn_id' => $grn->grn_id,
                            'item_id' => $grnItem->item_id,
                            'accepted_quantity' => $grnItem->accepted_quantity,
                            'free_received_quantity' => $grnItem->free_received_quantity ?? 0
                        ]);
                    }
                }

                if (!empty($transactionErrors)) {
                    throw new \Exception('Stock transaction errors: ' . implode(', ', $transactionErrors));
                }

                // Update purchase order status if applicable
                if ($grn->po_id && $grn->purchaseOrder) {
                    $this->updatePurchaseOrderStatus($grn->purchaseOrder);
                }
            }

            DB::commit();
            Log::info('GRN verification completed successfully', ['grn_id' => $grn->grn_id]);

            $message = $validated['status'] === GrnMaster::STATUS_VERIFIED
                ? 'GRN verified successfully.'
                : 'GRN rejected successfully.';

            return redirect()->route('admin.grn.show', $grn)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during GRN verification', [
                'grn_id' => $grn->grn_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error verifying GRN: ' . $e->getMessage());
        }
    }



    protected function updatePurchaseOrderStatus(PurchaseOrder $po)
    {
        if (!$po) {
            Log::warning('Attempted to update status of null purchase order');
            return;
        }

        try {
            $allItemsReceived = $po->items()
                ->get()
                ->every(function ($item) {
                    $receivedQty = $item->grnItems()
                        ->whereHas('grn', function ($q) {
                            $q->where('status', GrnMaster::STATUS_VERIFIED);
                        })
                        ->sum('accepted_quantity');

                    // Consider an item fully received if 95% or more has been received
                    // to account for minor discrepancies
                    $requiredQty = $item->quantity * 0.95;
                    return $receivedQty >= $requiredQty;
                });

            if ($allItemsReceived) {
                $po->status = 'Received';
                $po->save();

                Log::info('Purchase Order status updated to Received', [
                    'po_id' => $po->po_id,
                    'po_number' => $po->po_number ?? 'N/A'
                ]);
            } else {
                // Check if partially received
                $hasAnyReceivedItems = $po->items()
                    ->get()
                    ->some(function ($item) {
                        $receivedQty = $item->grnItems()
                            ->whereHas('grn', function ($q) {
                                $q->where('status', GrnMaster::STATUS_VERIFIED);
                            })
                            ->sum('accepted_quantity');
                        return $receivedQty > 0;
                    });

                if ($hasAnyReceivedItems && $po->status !== 'Partially Received') {
                    $po->status = 'Partially Received';
                    $po->save();

                    Log::info('Purchase Order status updated to Partially Received', [
                        'po_id' => $po->po_id,
                        'po_number' => $po->po_number ?? 'N/A'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating Purchase Order status', [
                'po_id' => $po->po_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate inventory levels before creating GRN
     */
    protected function validateInventoryLevels($items, $branchId)
    {
        $warnings = [];

        foreach ($items as $item) {
            $itemMaster = ItemMaster::find($item['item_id']);
            if (!$itemMaster) {
                continue;
            }

            // Check current stock levels
            $currentStock = $this->getCurrentStockLevel($itemMaster->id, $branchId);
            $reorderLevel = $itemMaster->reorder_level ?? 0;

            // Check if this will create excessive stock
            $newStock = $currentStock + ($item['received_quantity'] ?? 0);
            $maxRecommendedStock = $reorderLevel * 10; // 10x reorder level as max recommended

            if ($maxRecommendedStock > 0 && $newStock > $maxRecommendedStock) {
                $warnings[] = "Item '{$itemMaster->name}': New stock level ({$newStock}) will exceed recommended maximum ({$maxRecommendedStock})";
            }

            // Check for perishable items with short shelf life
            if ($itemMaster->is_perishable && isset($item['expiry_date'])) {
                $expiryDate = Carbon::parse($item['expiry_date']);
                $daysUntilExpiry = now()->diffInDays($expiryDate, false);

                if ($daysUntilExpiry < 7) {
                    $warnings[] = "Item '{$itemMaster->name}': Expires in {$daysUntilExpiry} days";
                } elseif ($daysUntilExpiry < 30) {
                    $warnings[] = "Item '{$itemMaster->name}': Expires in {$daysUntilExpiry} days - consider rotation";
                }
            }
        }

        return $warnings;
    }

    /**
     * Get current stock level for an item at a branch
     */
    protected function getCurrentStockLevel($itemId, $branchId)
    {
        try {
            return ItemTransaction::where('inventory_item_id', $itemId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->sum('quantity');
        } catch (\Exception $e) {
            Log::error('Error calculating stock level', [
                'item_id' => $itemId,
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Validate GRN calculations before saving
     */
    protected function validateGrnCalculations($items, $grandDiscount = 0)
    {
        $errors = [];
        $totalCalculated = 0;

        foreach ($items as $index => $item) {
            $receivedQty = $item['received_quantity'] ?? 0;
            $acceptedQty = $item['accepted_quantity'] ?? $receivedQty;
            $rejectedQty = $item['rejected_quantity'] ?? 0;
            $buyingPrice = $item['buying_price'] ?? 0;
            $discount = $item['discount_received'] ?? 0;

            // Validate quantities
            if ($acceptedQty + $rejectedQty != $receivedQty) {
                $errors[] = "Item " . ($index + 1) . ": Accepted ({$acceptedQty}) + Rejected ({$rejectedQty}) must equal Received ({$receivedQty})";
            }

            if ($buyingPrice < 0) {
                $errors[] = "Item " . ($index + 1) . ": Buying price cannot be negative";
            }

            if ($discount < 0) {
                $errors[] = "Item " . ($index + 1) . ": Discount cannot be negative";
            }

            // Calculate line total
            $baseAmount = $acceptedQty * $buyingPrice;
            if ($discount > $baseAmount) {
                $errors[] = "Item " . ($index + 1) . ": Discount ({$discount}) cannot exceed line total ({$baseAmount})";
            }

            $lineTotal = max(0, $baseAmount - $discount);
            $totalCalculated += $lineTotal;
        }

        // Validate grand discount
        if ($grandDiscount > 100) {
            $errors[] = "Grand discount cannot exceed 100%";
        }

        if ($grandDiscount > 0) {
            $grandDiscountAmount = $totalCalculated * ($grandDiscount / 100);
            if ($grandDiscountAmount > $totalCalculated) {
                $errors[] = "Grand discount amount cannot exceed total amount";
            }
        }

        return [
            'errors' => $errors,
            'calculated_total' => $totalCalculated
        ];
    }

    /**
     * Check if GRN status transition is valid
     */
    protected function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            GrnMaster::STATUS_PENDING => [GrnMaster::STATUS_VERIFIED, GrnMaster::STATUS_REJECTED],
            GrnMaster::STATUS_VERIFIED => [], // No transitions allowed from verified
            GrnMaster::STATUS_REJECTED => [], // No transitions allowed from rejected
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    /**
     * Get GRN statistics for dashboard
     */
    public function getGrnStatistics(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $orgId = $this->getOrganizationId();
            $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $baseQuery = GrnMaster::query();

            // Apply organization filter for non-super admins
            $this->applyOrganizationFilter($baseQuery, $orgId);
            $baseQuery->whereBetween('received_date', [$startDate, $endDate]);

            $stats = [
                'total_grns' => $baseQuery->count(),
                'pending_grns' => $baseQuery->clone()->where('status', GrnMaster::STATUS_PENDING)->count(),
                'verified_grns' => $baseQuery->clone()->where('status', GrnMaster::STATUS_VERIFIED)->count(),
                'rejected_grns' => $baseQuery->clone()->where('status', GrnMaster::STATUS_REJECTED)->count(),
                'total_value' => $baseQuery->clone()->sum('total_amount'),
                'average_value' => $baseQuery->clone()->avg('total_amount'),
                'items_received' => GrnItem::whereHas('grn', function ($q) use ($orgId, $startDate, $endDate) {
                    if ($orgId !== null) {
                        $q->where('organization_id', $orgId);
                    }
                    $q->whereBetween('received_date', [$startDate, $endDate]);
                })->sum('received_quantity'),
                'items_accepted' => GrnItem::whereHas('grn', function ($q) use ($orgId, $startDate, $endDate) {
                    if ($orgId !== null) {
                        $q->where('organization_id', $orgId);
                    }
                    $q->whereBetween('received_date', [$startDate, $endDate]);
                })->sum('accepted_quantity'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error fetching GRN statistics', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }

    /**
     * Get suppliers for organization (API endpoint for super admin)
     */
    public function getSuppliersByOrganization(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user->is_super_admin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $organizationId = $request->input('organization_id');

            if (!$organizationId) {
                return response()->json(['error' => 'Organization ID is required'], 400);
            }

            $suppliers = Supplier::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->select('id', 'name', 'supplier_id')
                ->get();

            return response()->json(['suppliers' => $suppliers]);
        } catch (\Exception $e) {
            Log::error('Error fetching suppliers by organization', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch suppliers'], 500);
        }
    }

    /**
     * Get branches for organization (API endpoint for super admin)
     */
    public function getBranchesByOrganization(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user->is_super_admin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $organizationId = $request->input('organization_id');

            if (!$organizationId) {
                return response()->json(['error' => 'Organization ID is required'], 400);
            }

            $branches = Branch::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->select('id', 'name')
                ->get();

            return response()->json(['branches' => $branches]);
        } catch (\Exception $e) {
            Log::error('Error fetching branches by organization', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch branches'], 500);
        }
    }

    /**
     * Get items for organization (API endpoint for super admin)
     */
    public function getItemsByOrganization(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user->is_super_admin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $organizationId = $request->input('organization_id');

            if (!$organizationId) {
                return response()->json(['error' => 'Organization ID is required'], 400);
            }

            // Filter items by category for GRN - only show items that can be purchased
            $allowedCategories = self::ALLOWED_GRN_CATEGORIES;
            $items = ItemMaster::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->whereHas('category', function($q) use ($allowedCategories) {
                    $q->whereIn('name', $allowedCategories);
                })
                ->with('category')
                ->select('id', 'item_code', 'name', 'buying_price', 'item_category_id')
                ->orderBy('name')
                ->get();

            // Transform items to include category name
            $itemsForResponse = $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'name' => $item->name,
                    'buying_price' => $item->buying_price,
                    'category' => $item->category->name ?? 'N/A'
                ];
            });

            return response()->json(['items' => $itemsForResponse]);
        } catch (\Exception $e) {
            Log::error('Error fetching items by organization', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch items'], 500);
        }
    }

    /**
     * Export GRN data to CSV
     */
    public function exportGrns(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return back()->with('error', 'Unauthorized access');
            }

            $orgId = $this->getOrganizationId();
            $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $query = GrnMaster::with(['supplier', 'branch', 'items.item'])
                ->whereBetween('received_date', [$startDate, $endDate]);

            // Apply organization filter for non-super admins
            $this->applyOrganizationFilter($query, $orgId);

            $grns = $query->get();

            $csvData = [];
            $csvData[] = [
                'GRN Number', 'Organization', 'Supplier', 'Branch', 'Received Date', 'Status',
                'Total Amount', 'Items Count', 'Verified By', 'Verified At'
            ];

            foreach ($grns as $grn) {
                $csvData[] = [
                    $grn->grn_number,
                    $grn->organization->name ?? 'N/A',
                    $grn->supplier->name ?? 'N/A',
                    $grn->branch->name ?? 'N/A',
                    $grn->received_date->format('Y-m-d'),
                    $grn->status,
                    number_format($grn->total_amount, 2),
                    $grn->items->count(),
                    $grn->verifiedByUser->name ?? 'N/A',
                    $grn->verified_at ? $grn->verified_at->format('Y-m-d H:i:s') : 'N/A'
                ];
            }

            $filename = 'grn_export_' . date('Y-m-d_H-i-s') . '.csv';

            return response()->stream(function() use ($csvData) {
                $handle = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting GRNs', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to export GRN data');
        }
    }

    public function statistics()
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $orgId = $this->getOrganizationId();

            $query = GrnMaster::query();
            $this->applyOrganizationFilter($query, $orgId);

            $stats = [
                'total_grns' => $query->count(),
                'pending_grns' => $query->clone()->where('status', GrnMaster::STATUS_PENDING)->count(),
                'verified_grns' => $query->clone()->where('status', GrnMaster::STATUS_VERIFIED)->count(),
                'rejected_grns' => $query->clone()->where('status', GrnMaster::STATUS_REJECTED)->count(),
                'total_value' => $query->clone()->sum('total_amount'),
                'current_month_grns' => $query->clone()
                    ->whereBetween('received_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'current_month_value' => $query->clone()
                    ->whereBetween('received_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('total_amount'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error fetching GRN statistics', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }

}
