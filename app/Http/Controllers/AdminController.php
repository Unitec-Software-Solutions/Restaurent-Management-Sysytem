<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the dashboard.');
        }

        // Basic validation without strict organization checks
        if (!$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Account setup incomplete. Contact support.');
        }

        try {
            $reservations = Reservation::with(['user', 'table'])
                ->where('organization_id', $admin->organization_id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return view('admin.dashboard', compact('reservations'));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('admin.dashboard', ['reservations' => collect()]);
        }
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
