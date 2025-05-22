<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = Auth::user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the dashboard.');
        }

        return view('admin.dashboard', compact('admin'));
    }

    public function index()
    {
        $admin = Auth::user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access reservations.');
        }

        if (!$admin->branch_id || !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Incomplete admin details. Contact support.');
        }

        $reservations = Reservation::with(['user', 'table']) // eager loading (adjust based on your model)
            ->where('branch_id', $admin->branch_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.reservations.index', compact('reservations'));
    }
}
