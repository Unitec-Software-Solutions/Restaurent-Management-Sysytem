<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    public function index()
    {
        try {
            // Fetch items with their category names, images, and sort by item ID
            $items = DB::connection('test_db')
                ->table('item_master')
                ->join('item_categories', 'item_master.item_category_id', '=', 'item_categories.id')
                ->select(
                    'item_master.id',
                    'item_master.name',
                    'item_master.selling_price',
                    'item_master.image', // Assuming you have an 'image' column
                    'item_categories.name as category_name'
                )
                ->orderBy('item_master.id', 'asc') // Sort by item ID
                ->get();

            // Group items by category
            $groupedItems = $items->groupBy('category_name');

            return view('frontend', compact('groupedItems'));
        } catch (\Exception $e) {
            return back()->withError('Database connection error: ' . $e->getMessage());
        }
    }
} 