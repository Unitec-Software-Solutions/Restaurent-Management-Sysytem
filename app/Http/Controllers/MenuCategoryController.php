<?php

// app/Http/Controllers/MenuCategoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem; // Assuming you have a MenuItem model
use App\Models\TimeSlot; // Assuming you have a TimeSlot model

class MenuCategoryController extends Controller
{
    public function create()
    {
        // Fetch menu items from the database, filtering by menu_category_id
        $menuItems = MenuItem::select('id', 'name', 'menu_category_id')
            ->whereNotNull('menu_category_id') // Filter items with a non-null menu_category_id
            ->get();

        // Fetch time slots from the database
        $timeSlots = TimeSlot::select('id', 'name')->get();

        return view('menu.addmenucategory', compact('menuItems', 'timeSlots'));
    }

    public function store(Request $request)
    {
        // Your store logic here
        // Validate and store the new menu category
    }
}