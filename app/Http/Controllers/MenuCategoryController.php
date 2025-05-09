<?php

// app/Http/Controllers/MenuCategoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeSlot;
use App\Models\MenuItem;
use App\Models\FoodItem;
use Illuminate\Support\Facades\DB;

class MenuCategoryController extends Controller
{
    public function create()
    {
        // Fetch data from menu_items and food_items tables
        $menuItems = DB::table('menu_items')->get();
        $foodItems = DB::table('food_items')->get();

        // Group items by category (assuming 'category' is a column in both tables)
        $groupedItems = [];
        foreach ($menuItems as $item) {
            $groupedItems[$item->category][] = $item;
        }
        foreach ($foodItems as $item) {
            $groupedItems[$item->category][] = $item;
        }

        // Fetch time slots (assuming you have a time_slots table)
        $timeSlots = DB::table('time_slots')->get();

        // Pass the data to the view
        return view('menu.addmenucategory', compact('groupedItems', 'timeSlots'));
    }

    public function store(Request $request)
    {
        // Handle form submission
        $categoryName = $request->input('category_name');
        $timeSlots = $request->input('time_slots');
        $selectedItems = $request->input('items');

        // Save the new menu category and associate items (logic depends on your database structure)
        // Example: Save to a `menu_categories` table and associate items in a pivot table

        return redirect()->back()->with('success', 'Menu category added successfully!');
    }
}