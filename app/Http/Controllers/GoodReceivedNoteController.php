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
    $pendingPOs = collect(); 
    
    if ($user && $user->branch_id) {
        $pendingPOs = PurchaseOrder::where('status', 'approved')
            ->where('branch_id', $user->branch_id)
            ->with('supplier')
            ->orderBy('po_number', 'asc') 
            ->get();
    }
    
    $branches = Branch::all(); 
    $suppliers = Supplier::all(); 
    
    return view('inventory.grn.create', compact('pendingPOs', 'branches', 'suppliers'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_note_number' => 'nullable|string',
            'supplier_invoice_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $grn = GoodReceivedNote::create([
                'grn_number' => $this->generateGRNNumber(),
                'branch_id' => Auth::user()->branch_id,
                'purchase_order_id' => $validated['purchase_order_id'],
                'supplier_id' => $validated['supplier_id'],
                'received_by' => Auth::id(),
                'received_date' => Carbon::now()->toDateString(),
                'received_time' => Carbon::now()->toTimeString(),
                'delivery_note_number' => $validated['delivery_note_number'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'],
                'status' => 'pending',
                'notes' => $validated['notes'],
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('inventory.grn.show', $grn)
                ->with('success', 'GRN created successfully');
        } catch (\Exception $e) {
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
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        
        $lastGRN = GoodReceivedNote::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->first();

        $sequence = $lastGRN ? intval(substr($lastGRN->grn_number, -4)) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}