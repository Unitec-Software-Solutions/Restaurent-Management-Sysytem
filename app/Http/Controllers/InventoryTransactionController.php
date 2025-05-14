<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['item', 'branch', 'user']);
        
        // Date range filter
        switch ($request->date_range) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'custom':
                if ($request->filled(['start_date', 'end_date'])) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                }
                break;
        }

        // Transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Calculate summary statistics
        $summaryQuery = clone $query;
        $summary = $summaryQuery->select([
            DB::raw('SUM(quantity * unit_price) as total_value'),
            DB::raw('SUM(quantity) as total_items'),
            DB::raw("SUM(CASE WHEN transaction_type = 'wastage' THEN quantity * unit_price ELSE 0 END) as total_wastage")
        ])->first();

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('inventory.transactions.index', [
            'transactions' => $transactions,
            'branches' => $branches,
            'totalValue' => $summary->total_value ?? 0,
            'totalItems' => $summary->total_items ?? 0,
            'totalWastage' => $summary->total_wastage ?? 0
        ]);
    }

    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['item.category', 'branch', 'user']);
        return view('inventory.transactions.show', compact('transaction'));
    }
}