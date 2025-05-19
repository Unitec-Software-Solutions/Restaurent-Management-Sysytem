<?php

namespace App\Http\Controllers;

use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GoodReceivedNoteItemController extends Controller
{
    public function store(GoodReceivedNote $grn, Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'received_quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacturing_date',
            'batch_number' => 'nullable|string',
            'free_quantity' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            DB::transaction(function () use ($grn, $validated, $request) {
                $totalPrice = $validated['received_quantity'] * $validated['unit_price'];

                GoodReceivedNoteItem::create([
                    'good_received_note_id' => $grn->id,
                    'purchase_order_item_id' => $request->purchase_order_item_id,
                    'inventory_item_id' => $validated['inventory_item_id'],
                    'expected_quantity' => $validated['received_quantity'],
                    'received_quantity' => $validated['received_quantity'],
                    'accepted_quantity' => $validated['received_quantity'],
                    'unit_price' => $validated['unit_price'],
                    'total_price' => $totalPrice,
                    'manufacturing_date' => $validated['manufacturing_date'],
                    'expiry_date' => $validated['expiry_date'],
                    'batch_number' => $validated['batch_number'],
                    'free_quantity' => $validated['free_quantity'] ?? 0,
                    'discount_percentage' => $validated['discount_percentage'] ?? 0,
                ]);

                // Update GRN total amount
                $grn->updateTotalAmount();
            });

            return response()->json(['message' => 'Item added successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(GoodReceivedNoteItem $item, Request $request)
    {
        $validated = $request->validate([
            'received_quantity' => 'sometimes|required|numeric|min:0',
            'accepted_quantity' => 'sometimes|required|numeric|min:0',
            'rejected_quantity' => 'sometimes|required|numeric|min:0',
            'rejection_reason' => 'required_if:rejected_quantity,>,0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'quality_check_notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($item, $validated) {
                $item->update($validated);
                
                if (isset($validated['received_quantity']) || isset($validated['unit_price'])) {
                    $item->total_price = $item->received_quantity * $item->unit_price;
                    $item->save();
                    
                    // Update GRN total amount
                    $item->goodReceivedNote->updateTotalAmount();
                }
            });

            return response()->json(['message' => 'Item updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(GoodReceivedNoteItem $item)
    {
        try {
            DB::transaction(function () use ($item) {
                $grn = $item->goodReceivedNote;
                $item->delete();
                $grn->updateTotalAmount();
            });

            return response()->json(['message' => 'Item removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function qualityCheck(GoodReceivedNoteItem $item, Request $request)
    {
        $validated = $request->validate([
            'accepted_quantity' => 'required|numeric|min:0',
            'rejected_quantity' => 'required|numeric|min:0',
            'rejection_reason' => 'required_if:rejected_quantity,>,0',
            'quality_check_notes' => 'nullable|string'
        ]);

        try {
            $item->update([
                'accepted_quantity' => $validated['accepted_quantity'],
                'rejected_quantity' => $validated['rejected_quantity'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
                'quality_check_notes' => $validated['quality_check_notes'],
                'quality_checked' => true
            ]);

            return response()->json(['message' => 'Quality check completed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}