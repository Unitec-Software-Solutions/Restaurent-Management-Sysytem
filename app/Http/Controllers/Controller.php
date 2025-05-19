<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\MenuItem;


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function showAddMenuCategoryForm()
{
    $menuItems = MenuItem::pluck('name', 'id'); // For <select>
    $foodItems = FoodItem::pluck('name', 'id'); // For food item dropdown


    // Group items by category (optional, assuming you need it for $groupedItems)
    $groupedItems = MenuItem::all()->groupBy('category')->map(function ($items) {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
            ];
        });
    });

    return view('menu.add-category', compact('menuItems', 'foodItems', 'timeSlots', 'groupedItems'));
}

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
       
        // Pass the data to the view
        return view('menu.addmenucategory', compact('groupedItems', 'timeSlots'));

        {
            $menuItems = MenuItem::pluck('name', 'id'); // Example: get [id => name] pairs
        
            return view('menu.form', compact('menuItems'));
        }
        
    }
}
