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

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $admin->isSuperAdmin();
        
        // Basic validation - super admins don't need organization
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Account setup incomplete. Contact support.');
        }

        try {
            // Super admins can see all reservations, others see their organization's
            $reservationsQuery = Reservation::with(['user', 'table'])
                ->orderBy('created_at', 'desc')
                ->take(10);
                
            if (!$isSuperAdmin && $admin->organization_id) {
                $reservationsQuery->where('organization_id', $admin->organization_id);
            }
            
            $reservations = $reservationsQuery->get();

            return view('admin.dashboard', compact('reservations', 'admin'));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('admin.dashboard', ['reservations' => collect(), 'admin' => $admin]);
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

    /**
     * Get admin details for modal (AJAX)
     */
    public function getAdminDetails($adminId)
    {
        try {
            $admin = \App\Models\Admin::with(['organization', 'roles'])->findOrFail($adminId);
            
            $stats = [
                'last_login' => $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never',
                'is_super_admin' => $admin->isSuperAdmin(),
                'role_count' => $admin->roles()->count(),
                'created_ago' => $admin->created_at->diffForHumans(),
            ];

            return response()->json([
                'success' => true,
                'admin' => $admin,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ]);
        }
    }
}
