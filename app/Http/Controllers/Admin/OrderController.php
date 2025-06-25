<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function summary()
    {
        // TODO: Implement summary logic
        return view('admin.summary');
    }

    /**
     * Dashboard view for orders
     */
    public function dashboard()
    {
        return view('admin.orders.dashboard');
    }

    /**
     * Show takeaway orders
     */
    public function takeaway()
    {
        return view('admin.orders.takeaway');
    }

    /**
     * Show reservations related to orders
     */
    public function reservations()
    {
        return view('admin.orders.reservations');
    }

    /**
     * Archive old menus
     */
    public function archiveOldMenus()
    {
        return response()->json(['message' => 'Old menus archived']);
    }

    /**
     * Get menu safety status
     */
    public function menuSafetyStatus()
    {
        return response()->json(['status' => 'safe']);
    }

    /**
     * Update cart
     */
    public function updateCart(Request $request)
    {
        return response()->json(['message' => 'Cart updated']);
    }

}
