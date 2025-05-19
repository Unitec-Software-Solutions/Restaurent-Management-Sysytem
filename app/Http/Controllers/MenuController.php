<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\FoodItem;
use App\Models\TimeSlot;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function showAddMenuCategoryForm()
    {
        // Fetch data from menu_items and food_items tables
        $menuItems = MenuItem::all();
        $foodItems = FoodItem::all();

        // Combine the data
        $items = $menuItems->merge($foodItems);

        // Group items by category (assuming there's a 'category' column in both tables)
        $groupedItems = $items->groupBy('category');

        // Fetch data from the time_slots table
        $timeSlots = TimeSlot::all();

        return view('menu.addmenucategory', compact('groupedItems', 'timeSlots'));
    }

    public function storeMenuCategory(Request $request)
    {
        // Validate the form data
        $request->validate([
            'category_name' => 'required|string|max:255',
            'time_slots' => 'required|array',
            'items' => 'required|array',
        ]);

        // Save the menu category
        $menuCategory = new MenuCategory();
        $menuCategory->name = $request->input('category_name');
        $menuCategory->save();

        // Attach time slots and items to the menu category
        $menuCategory->timeSlots()->attach($request->input('time_slots'));
        $menuCategory->items()->attach($request->input('items'));

        return redirect()->back()->with('success', 'Menu category added successfully!');
    }

    // New method to fetch items using DB facade (if needed separately)
}
