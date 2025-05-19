<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;

class MenuFrontendController extends Controller
{
    public function index()
    {
        // Assuming you have a FoodItem model
        $foodItems = FoodItem::all(); // Or whatever query you need
        
        return view('frontend', compact('foodItems'));
    }

    public function frontend()
    {
        $foodItems = FoodItem::all();
        return view('frontend', compact('foodItems'));
    }

    public function menu()
    {
        $foodItems = FoodItem::all();
        return view('menu', compact('foodItems'));
    }
}
