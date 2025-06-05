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

        // Fetch GRN payment status counts
        $grnPaymentStatusCounts = \App\Models\GrnMaster::selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        return view('admin.dashboard', compact('admin', 'grnPaymentStatusCounts'));
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

        $reservations = Reservation::with(['user', 'table'])
            ->where('branch_id', $admin->branch_id)
            ->where('organization_id', $admin->organization_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function profile()
    {
        $admin = Auth::user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access your profile.');
        }

        return view('admin.profile.index', compact('admin'));
    }
}
