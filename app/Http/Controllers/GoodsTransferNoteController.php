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
        $gtns = GoodsTransferNote::with(['fromBranch', 'toBranch', 'createdBy'])->latest()->get();
        return view('admin.inventory.gtn.index ', compact('gtns'));
    }

    public function create()
    {
        return view('admin.inventory.gtn.create', [
            'branches' => Branch::all(),
            'items' => ItemMaster::all(),
            'employees' => Employee::all(),
            'organization' => Organizations::first() // Adjust if multi-org
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
            $gtn = GoodsTransferNote::create([
                'gtn_number' => $request->gtn_number,
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'created_by' => \Illuminate\Support\Facades\Auth::id(),
                'organization_id' => $request->organization_id,
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
                    'item_name' => $itemModel->item_name,
                    'batch_no' => $item['batch_no'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'transfer_quantity' => $item['transfer_quantity'],
                    'transfer_price' => $item['transfer_price'] ?? 0,
                    'line_total' => ($item['transfer_quantity'] * ($item['transfer_price'] ?? 0)),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            Log::info('GTN created', ['gtn_id' => $gtn->gtn_id, 'user_id' => \Illuminate\Support\Facades\Auth::user()->id]);
        });

        return redirect()->route('admin.inventory.gtn.index')->with('success', 'GTN Created');

    } catch (Exception $e) {
        Log::error('GTN creation failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'input' => $request->all()
        ]);

        return back()->withErrors('Failed to create GTN. Check logs for details.')->withInput();
    }
}
}
