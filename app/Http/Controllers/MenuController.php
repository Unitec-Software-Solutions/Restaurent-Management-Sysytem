<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\FoodItem;
use App\Models\TimeSlot;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryCategory;

class MenuController extends Controller
{
    public function showAddMenuCategoryForm()
    {
        // Fetch all menu categories for the dropdown
        $menuCategories = MenuCategory::where('is_active', true)->orderBy('display_order')->get();
        
        return view('menu.addmenucategory', compact('menuCategories'));
    }

    public function addMenuCategory(Request $request)
    {
        // Handle form submission (e.g., save the category to the database)
        $categoryName = $request->input('category_name');
        // Add your logic to save the category here
        return redirect('/menu/addmenucategory')->with('success', 'Category added successfully!');
    }

    public function storeMenuCategory(Request $request)
    {
        // Validate the form data
        $request->validate([
            'category_name' => 'required|string|max:255',
            'time_slots' => 'required|array',
        ]);

        // Save the menu category
        $menuCategory = new MenuCategory();
        $menuCategory->name = $request->input('category_name');
        $menuCategory->save();

        // Attach time slots to the menu category
        $menuCategory->timeSlots()->attach($request->input('time_slots'));

        return redirect()->back()->with('success', 'Menu category added successfully!');
    }

    // New method to fetch items using DB facade (if needed separately)

    public function create()
    {
        $menuItems = DB::table('menu_items')->pluck('name', 'id');
        
    }

    public function addCategory()
    {
        return view('menu.add_category'); // Ensure this view exists
    }
    
    /**
     * Fetch menu items by category ID
     *
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuItemsByCategory($categoryId)
    {
        // Fetch the 'name' column from menu_items table filtered by menu_category_id
        $menuItems = MenuItem::where('menu_category_id', $categoryId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();
        
        return response()->json($menuItems);
    }

    public function index()
    {
        // Fetch data from food_items table
        $menuItems = DB::table('food_items')
            ->select('name', 'price')
            ->get();

        return view('menu', ['menuItems' => $menuItems]);
    }

    public function filterMenu(Request $request)
    {
        $category = $request->input('category');
        
        $query = DB::table('food_items')
            ->select('name', 'price');
        
        if ($category !== 'all') {
            $query->where('category', $category);
        }
        

        
        return view('frontend.menu', compact('foodItems'));
    }

    public function frontend()
    {
        // Fetch data from the database
        $menuData = DB::table('inventory_categories')
            ->leftJoin('food_items', 'inventory_categories.id', '=', 'food_items.id')
            ->select(
                'inventory_categories.id AS category_id',
                'inventory_categories.name AS category_name',
                'food_items.item_id',
                'food_items.name AS food_name',
                'food_items.price'
            )
            ->orderBy('inventory_categories.id')
            ->get();

        // Group the data by category
        $groupedMenuData = $menuData->groupBy('category_name');

        return view('menu.frontend', compact('groupedMenuData'));
    }

    // Admin view
    public function adminIndex()
    {
        return view('menu.admin-index', [
            'isAdmin' => true
        ]);
    }

    // Customer view
    public function customerIndex()
    {
        return view('menu.customer-index', [
            'isAdmin' => false
        ]);
    }


}
