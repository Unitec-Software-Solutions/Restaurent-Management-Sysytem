<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use Illuminate\Http\Request;

class ItemTransactionController extends Controller
{
    public function index()
    {
        $transactions = ItemTransaction::with(['item', 'branch'])->latest()->paginate(25);

        return view('admin.inventory.stock.index', compact('transactions'));
    }

    public function stockSummary()
    {
        $items = ItemMaster::with('category')->get();

        $stockData = $items->map(function ($item) {
            $stock = ItemTransaction::stockOnHand($item->id);
            return [
                'name' => $item->name,
                'category' => $item->category->name ?? '-',
                'reorder_level' => $item->reorder_level,
                'stock' => $stock,
                'status' => $stock <= $item->reorder_level ? 'Warning' : 'OK',
            ];
        });

        return view('admin.inventory.stock.summary', compact('stockData'));
    }
}
