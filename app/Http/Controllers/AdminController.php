<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard'); // Ensure this view exists
    }

    public function index()
    {
        $admin = auth()->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        if (!$admin->branch_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Branch information is missing for this user.');
        }

        $reservations = Reservation::where('branch_id', $admin->branch_id)->get();

        return view('admin.reservations.index', compact('reservations'));
    }
}
