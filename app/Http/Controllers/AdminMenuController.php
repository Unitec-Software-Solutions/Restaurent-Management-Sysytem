<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $beverages = MenuItem::where('category', 'Beverages')->get();
        $dairyProducts = MenuItem::where('category', 'Dairy Products')->get();
        
        return view('admin.menu.index', compact('beverages', 'dairyProducts'));
    }

    public function create()
    {
        return view('admin.menu.create');
    }

    public function store(Request $request)
    {
        // Validation and store logic
    }

    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        return view('admin.menu.edit', compact('menuItem'));
    }

    public function update(Request $request, $id)
    {
        // Validation and update logic
    }

    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->delete();
        
        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted successfully');
    }
}