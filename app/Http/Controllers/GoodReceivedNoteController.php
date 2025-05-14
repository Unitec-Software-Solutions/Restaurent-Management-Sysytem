<?php

namespace App\Http\Controllers;

use App\Models\GoodReceivedNote;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Supplier;

class GoodReceivedNoteController extends Controller
{
    public function index(Request $request)
    {
        // Get active branches
        $branches = Branch::where('is_active', true)
                        ->orderBy('name')
                        ->get();
        
        // Get active suppliers
        $suppliers = Supplier::where('is_active', true)
                            ->orderBy('name')
                            ->get();
        
        // Build the query
        $query = GoodReceivedNote::with(['supplier', 'branch', 'receivedBy', 'checkedBy']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->where('grn_number', 'like', '%' . $request->search . '%');
        }
        
        // Apply branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Get results with pagination
        $grns = $query->orderBy('created_at', 'desc')
                    ->paginate(15)
                    ->withQueryString(); 
                
        return view('inventory.grn.index', compact('grns', 'branches', 'suppliers'));
    }

    
    public function create()
    {
        $user = Auth::user();
        $pendingPOs = PurchaseOrder::where('status', 'approved')
            ->where('branch_id', $user->branch_id)
            ->with(['supplier', 'items.inventoryItem'])
            ->orderBy('po_number', 'asc')
            ->get();
        
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $grnNumber = $this->generateGRNNumber(); // Pre-generate GRN number
        
        return view('inventory.grn.create', compact('pendingPOs', 'branches', 'suppliers', 'grnNumber'));
    }

    
        public function store(Request $request)
        {
            $validated = $request->validate([
                'grn_number' => 'required|unique:good_received_notes,grn_number',
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'delivery_note_number' => 'nullable|string',
                'supplier_invoice_number' => 'required|string',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.free_quantity' => 'nullable|numeric|min:0',
                'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();
            try {
                $grn = GoodReceivedNote::create([
                    'grn_number' => $validated['grn_number'],
                    'branch_id' => Auth::user()->branch_id,
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'supplier_id' => $validated['supplier_id'],
                    'received_by' => Auth::id(),
                    'received_date' => now()->toDateString(),
                    'received_time' => now()->toTimeString(),
                    'delivery_note_number' => $validated['delivery_note_number'],
                    'supplier_invoice_number' => $validated['supplier_invoice_number'],
                    'status' => 'pending',
                    'notes' => $validated['notes'],
                    'total_amount' => $request->input('total_amount', 0),
                    'discount_amount' => $request->input('discount_amount', 0),
                    'tax_amount' => $request->input('tax_amount', 0),
                    'ip_address' => $request->ip(),
                ]);

                foreach ($validated['items'] as $item) {
                    $grn->items()->create([
                        'inventory_item_id' => $item['inventory_item_id'],
                        'quantity' => $item['quantity'],
                        'received_quantity' => $item['quantity'],
                        'accepted_quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'free_quantity' => $item['free_quantity'] ?? 0,
                        'discount_percentage' => $item['discount_percentage'] ?? 0,
                    ]);
                }

                DB::commit();
                return redirect()->route('inventory.grn.show', $grn)
                    ->with('success', 'GRN created successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Error creating GRN: ' . $e->getMessage())
                    ->withInput();
            }
        }

    public function show(GoodReceivedNote $grn)
    {
        $grn->load(['items.inventoryItem', 'supplier', 'receivedBy', 'checkedBy']);
        return view('inventory.grn.show', compact('grn'));
    }

    public function finalize(GoodReceivedNote $grn)
    {
        if ($grn->items()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot finalize GRN without items');
        }

        $grn->update([
            'status' => 'checked',
            'checked_by' => Auth::id()
        ]);

        return redirect()->route('inventory.grn.show', $grn)
            ->with('success', 'GRN finalized successfully');
    }

    private function generateGRNNumber()
    {
        $prefix = 'GRN';
        $branch = Auth::user()->branch_id ?? '00';
        $year = date('y');
        $month = date('m');
        
        $latestGrn = GoodReceivedNote::where('grn_number', 'like', "{$prefix}{$branch}{$year}{$month}%")
            ->orderBy('grn_number', 'desc')
            ->first();

        $sequence = $latestGrn ? intval(substr($latestGrn->grn_number, -4)) + 1 : 1;
        return $prefix . str_pad($branch, 2, '0', STR_PAD_LEFT) . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}