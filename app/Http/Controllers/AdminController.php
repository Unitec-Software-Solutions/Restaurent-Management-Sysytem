<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
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

    /**
     * Display reservations for admin
     */
    public function reservations()
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

    /**
     * Display a listing of admins
     */
    public function index()
    {
        $currentAdmin = Auth::guard('admin')->user();
        
        // Only super admins can view admin list
        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can view admin accounts.');
        }

        $admins = Admin::with(['organization', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.admins.index', compact('admins'));
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

    /**
     * Show the form for editing an admin
     */
    public function edit(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();
        
        // Only super admins can edit other admins
        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can edit admin accounts.');
        }

        // Get roles for the form
        $roles = Role::all();
        
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update an admin
     */
    public function update(Request $request, Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();
        
        // Only super admins can update other admins
        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can update admin accounts.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'organization_id' => 'nullable|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'department' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended,pending',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'organization_id' => $request->organization_id,
            'branch_id' => $request->branch_id,
            'is_super_admin' => $request->boolean('is_super_admin'),
            'is_active' => $request->boolean('is_active'),
            'department' => $request->department,
            'job_title' => $request->job_title,
            'status' => $request->status,
        ];

        // Handle role assignment
        if ($request->filled('role_id')) {
            $data['current_role_id'] = $request->role_id;
            
            // Also assign the role via Spatie
            $role = Role::findOrFail($request->role_id);
            $admin->syncRoles([$role]);
        }

        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admins.index')
            ->with('success', 'Admin updated successfully.');
    }
}
