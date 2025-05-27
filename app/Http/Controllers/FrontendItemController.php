<?php

namespace App\Http\Controllers;

use App\Models\ItemMaster;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

class FrontendItemController extends Controller
{
    public function __construct()
    {
        // Remove or comment out the auth middleware if it exists
        // $this->middleware('auth');
    }

    public function index()
    {
        $items = ItemMaster::with('category')->latest()->get();
        return view('frontend.items', compact('items'));
    }

    public function create()
    {
        $categories = ItemCategory::all();
        return view('frontend.item-form', [
            'categories' => $categories,
            'item' => null, // For create form
            'formAction' => route('frontend.store-item'),
            'method' => 'POST'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_category_id' => 'required|exists:item_categories,id',
            'selling_price' => 'required|numeric|min:0'
        ]);

        ItemMaster::create($validated);
        return redirect()->route('frontend.itemlist')->with('success', 'Item added successfully!');
    }

    public function edit($id)
    {
        $item = ItemMaster::findOrFail($id);
        $categories = ItemCategory::all();
        
        return view('frontend.item-form', [
            'item' => $item,
            'categories' => $categories,
            'formAction' => route('frontend.update-item', $item->id),
            'method' => 'PUT'
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_category_id' => 'required|exists:item_categories,id',
            'selling_price' => 'required|numeric|min:0'
        ]);

        $item = ItemMaster::findOrFail($id);
        $item->update($validated);
        return redirect()->route('frontend.itemlist')->with('success', 'Item updated successfully!');
    }

    public function destroy($id)
    {
        $item = ItemMaster::findOrFail($id);
        $item->delete();
        return redirect()->route('frontend.itemlist')->with('success', 'Item deleted successfully!');
    }
} 