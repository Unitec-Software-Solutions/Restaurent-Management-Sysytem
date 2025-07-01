<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use App\Models\ProductionRequestItem;
use App\Models\ProductionRequestMaster;
use Illuminate\Support\Facades\Auth;

class ProductionRequestItemController extends Controller
{
    /**
     * Update quantity approved for a production request item (Can exceed requested)
     */
    public function updateApproved(Request $request, ProductionRequestItem $item)
    {
        $request->validate([
            'quantity_approved' => 'required|numeric|min:0' // Removed max constraint
        ]);

        $item->update([
            'quantity_approved' => $request->quantity_approved
        ]);

        return redirect()->back()->with('success', 'Approved quantity updated successfully.');
    }

    /**
     * Update production status for an item
     */
    public function updateProduction(Request $request, ProductionRequestItem $item)
    {
        $request->validate([
            'quantity_produced' => 'required|numeric|min:0|max:' . $item->quantity_approved,
            'notes' => 'nullable|string|max:500'
        ]);

        $item->update([
            'quantity_produced' => $request->quantity_produced,
            'notes' => $request->notes
        ]);

        // Check if all items in the request are fully produced
        $this->checkRequestCompletion($item->productionRequestMaster);

        return redirect()->back()->with('success', 'Production quantity updated successfully.');
    }

    /**
     * Update distribution status for an item
     */
    public function updateDistribution(Request $request, ProductionRequestItem $item)
    {
        $request->validate([
            'quantity_distributed' => 'required|numeric|min:0|max:' . $item->quantity_produced
        ]);

        $item->update([
            'quantity_distributed' => $request->quantity_distributed
        ]);

        // Check if all items are fully distributed
        $this->checkRequestCompletion($item->productionRequestMaster);

        return redirect()->back()->with('success', 'Distribution quantity updated successfully.');
    }

    /**
     * Check if production request is completed
     */
    private function checkRequestCompletion(ProductionRequestMaster $request)
    {
        $allItemsProduced = $request->items->every(function ($item) {
            return $item->quantity_produced >= $item->quantity_approved;
        });

        $allItemsDistributed = $request->items->every(function ($item) {
            return $item->quantity_distributed >= $item->quantity_produced;
        });

        if ($allItemsProduced && $request->status === 'in_production') {
            $request->update(['status' => 'completed']);
        }

        if ($allItemsDistributed && $request->status === 'completed') {
            // Items have been distributed, can create transfer notes
            // This would trigger transfer note creation logic
        }
    }

    /**
     * Get production items for a specific request
     */
    public function getItemsForRequest(ProductionRequestMaster $request)
    {
        $items = $request->items()->with('item')->get();

        return response()->json([
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item->name,
                    'quantity_requested' => $item->quantity_requested,
                    'quantity_approved' => $item->quantity_approved,
                    'quantity_produced' => $item->quantity_produced,
                    'quantity_distributed' => $item->quantity_distributed,
                    'notes' => $item->notes
                ];
            })
        ]);
    }
}
