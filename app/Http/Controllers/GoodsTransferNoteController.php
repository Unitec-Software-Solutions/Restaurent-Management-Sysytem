<?php

namespace App\Http\Controllers;

use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\Employee;
use App\Models\Organizations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GoodsTransferNoteController extends Controller
{
    public function index()
    {
        $gtns = GoodsTransferNote::with(['fromBranch', 'toBranch', 'createdBy'])->latest()->paginate(15);
        // Add: fetch organization for the current user
        $organization = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->organization_id) {
            $organization = Organizations::find(auth()->guard()->user()->organization_id);
        }
        $branches = \App\Models\Branch::all();
        $items = \App\Models\ItemMaster::all();
        return view('admin.inventory.gtn.index', compact('gtns', 'organization', 'branches', 'items'));
    }

    public function create()
    {
        // Generate next GTN number (simple example: GTN-YYYYMMDD-XXX)
        $lastGtn = \App\Models\GoodsTransferNote::orderByDesc('gtn_id')->first();
        $datePrefix = now()->format('Ymd');
        $nextSeq = 1;
        if ($lastGtn && str_starts_with($lastGtn->gtn_number, 'GTN-' . $datePrefix)) {
            $lastSeq = (int)substr($lastGtn->gtn_number, -3);
            $nextSeq = $lastSeq + 1;
        }
        $nextGtnNumber = 'GTN-' . $datePrefix . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        return view('admin.inventory.gtn.create', [
            'branches' => Branch::all(),
            'items' => ItemMaster::all(),
            'employees' => Employee::all(),
            'organization' => Organizations::first(), // Adjust if multi-org
            'nextGtnNumber' => $nextGtnNumber,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gtn_number' => 'required|unique:gtn_master',
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|different:from_branch_id',
            'transfer_date' => 'required|date',
            'items.*.item_id' => 'required',
            'items.*.transfer_quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if (!$user || !$user->organization_id) {
                    abort(403, 'Unauthorized access - organization not set');
                }
                $organizationId = $user->organization_id;

                // Find or create Employee record for the current user
                $employee = \App\Models\Employee::where('email', $user->email)->first();
                if (!$employee) {
                    // Try to infer branch from from_branch_id, fallback to first branch of org
                    $branchId = $request->from_branch_id;
                    if (!$branchId) {
                        $branchId = \App\Models\Branch::where('organization_id', $organizationId)->value('id');
                    }
                    $employee = \App\Models\Employee::create([
                        'emp_id' => 'AUTO-' . strtoupper(uniqid()),
                        'name' => $user->name ?? 'Auto Employee',
                        'email' => $user->email,
                        'phone' => $user->phone_number ?? 'N/A',
                        'role' => 'manager',
                        'branch_id' => $branchId,
                        'organization_id' => $organizationId,
                        'is_active' => true,
                        'joined_date' => now(),
                        'address' => '',
                        'emergency_contact' => '',
                    ]);
                }

                $gtn = GoodsTransferNote::create([
                    'gtn_number' => $request->gtn_number,
                    'from_branch_id' => $request->from_branch_id,
                    'to_branch_id' => $request->to_branch_id,
                    'created_by' => $employee->id, // Use Employee id
                    'organization_id' => $organizationId,
                    'transfer_date' => $request->transfer_date,
                    'status' => 'Pending',
                    'notes' => $request->notes,
                ]);

                foreach ($request->items as $item) {
                    $itemModel = ItemMaster::findOrFail($item['item_id']);

                    GoodsTransferItem::create([
                        'gtn_id' => $gtn->gtn_id,
                        'item_id' => $itemModel->id,
                        'item_code' => $itemModel->item_code,
                        'item_name' => $itemModel->name, // Use correct property
                        'batch_no' => $item['batch_no'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'transfer_quantity' => $item['transfer_quantity'],
                        'transfer_price' => $item['transfer_price'] ?? 0,
                        'line_total' => ($item['transfer_quantity'] * ($item['transfer_price'] ?? 0)),
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                Log::info('GTN created', ['gtn_id' => $gtn->gtn_id, 'user_id' => $user->id]);
            });

            return redirect()->route('admin.gtn.index')->with('success', 'GTN Created');
        } catch (Exception $e) {
            Log::error('GTN creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()->withErrors('Failed to create GTN. Check logs for details.')->withInput();
        }
    }

    public function show($id)
    {
        $gtn = GoodsTransferNote::with(['fromBranch', 'toBranch', 'items'])->findOrFail($id);
        return view('admin.inventory.gtn.show', compact('gtn'));
    }

    public function edit($id)
    {
        $gtn = GoodsTransferNote::with('items')->findOrFail($id);
        return view('admin.inventory.gtn.edit', [
            'gtn' => $gtn,
            'branches' => Branch::all(),
            'items' => ItemMaster::all(),
            'employees' => Employee::all(),
            'organization' => Organizations::first() // Adjust if multi-org
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'gtn_number' => 'required|unique:gtn_master,gtn_number,' . $id . ',gtn_id',
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|different:from_branch_id',
            'transfer_date' => 'required|date',
            'items.*.item_id' => 'required',
            'items.*.transfer_quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $gtn = GoodsTransferNote::findOrFail($id);
                $gtn->update([
                    'gtn_number' => $request->gtn_number,
                    'from_branch_id' => $request->from_branch_id,
                    'to_branch_id' => $request->to_branch_id,
                    'transfer_date' => $request->transfer_date,
                    'status' => $request->status ?? $gtn->status,
                    'notes' => $request->notes,
                ]);

                $gtn->items()->delete(); // Remove old items
                foreach ($request->items as $item) {
                    $itemModel = ItemMaster::findOrFail($item['item_id']);

                    GoodsTransferItem::create([
                        'gtn_id' => $gtn->gtn_id,
                        'item_id' => $itemModel->id,
                        'item_code' => $itemModel->item_code,
                        'item_name' => $itemModel->name, // Use correct property
                        'batch_no' => $item['batch_no'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'transfer_quantity' => $item['transfer_quantity'],
                        'transfer_price' => $item['transfer_price'] ?? 0,
                        'line_total' => ($item['transfer_quantity'] * ($item['transfer_price'] ?? 0)),
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                Log::info('GTN updated', ['gtn_id' => $gtn->gtn_id, 'user_id' => \Illuminate\Support\Facades\Auth::user()->id]);
            });

            return redirect()->route('admin.gtn.index')->with('success', 'GTN Updated');
        } catch (Exception $e) {
            Log::error('GTN update failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()->withErrors('Failed to update GTN. Check logs for details.')->withInput();
        }
    }
}
