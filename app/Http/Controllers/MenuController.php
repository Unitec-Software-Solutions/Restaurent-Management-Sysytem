<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuCategory;

class MenuController extends Controller
{
    // Your controller methods here

    public function index()
    {
        $menuCategories = MenuCategory::with('foodItems', 'menuItems')->get();
        return view('menu.index', ['menuCategories' => $menuCategories]);
    }

    public function show($id)
    {
        $menuItem = MenuCategory::find($id);

        if (!$menuItem) {
            abort(404, 'Menu item not found');
        }

        return view('menu.show', ['menuItem' => $menuItem]);
    }

    public function adminFunctions()
    {
        $menuCategories = MenuCategory::with('foodItems', 'menuItems')->get();
        return view('menu.admin.functions', ['menuCategories' => $menuCategories]);
    }

    public function edit($id)
    {
        $menuCategory = MenuCategory::findOrFail($id);
        return view('menu.admin.edit', ['menuCategory' => $menuCategory]);
    }

    public function update(Request $request, $id)
    {
        $menuCategory = MenuCategory::findOrFail($id);
        $menuCategory->update($request->all());
        return redirect()->route('menu.admin.functions')->with('success', 'Menu category updated successfully!');
    }

    public function destroy($id)
    {
        $menuCategory = MenuCategory::findOrFail($id);
        $menuCategory->delete();
        return redirect()->route('menu.admin.functions')->with('success', 'Menu category deleted successfully!');
    }
}
