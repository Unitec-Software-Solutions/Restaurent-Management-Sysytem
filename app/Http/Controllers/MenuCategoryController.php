<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuCategory;
use App\Models\FoodItem;
use App\Models\MenuItem;

class MenuCategoryController extends Controller
{
    /**
     * Show the form for creating a new menu category.
     */
    public function create()
    {
        // Fetch all food items and menu items
        $foodItems = FoodItem::all();
        $menuItems = MenuItem::all();

        // Group food items and menu items by category (if applicable)
        // Example: Assuming 'category_id' is a field in both tables
        $foodItemsByCategory = $foodItems->groupBy('category_id');
        $menuItemsByCategory = $menuItems->groupBy('category_id');

        return view('menu-categories.create', [
            'foodItemsByCategory' => $foodItemsByCategory,
            'menuItemsByCategory' => $menuItemsByCategory,
        ]);
    }

    /**
     * Store a newly created menu category in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'food_items' => 'nullable|array',
            'menu_items' => 'nullable|array',
        ]);

        // Create the menu category
        $menuCategory = MenuCategory::create([
            'name' => $request->name,
        ]);

        // Attach selected food items to the menu category
        if ($request->has('food_items')) {
            $menuCategory->foodItems()->attach($request->food_items);
        }

        // Attach selected menu items to the menu category
        if ($request->has('menu_items')) {
            $menuCategory->menuItems()->attach($request->menu_items);
        }

        return redirect()->route('menu-categories.create')->with('success', 'Menu category added successfully!');
    }
} 