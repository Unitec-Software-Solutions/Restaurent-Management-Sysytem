<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the dashboard.');
        }

        $isSuperAdmin = $admin->isSuperAdmin();

        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Account setup incomplete. Contact support.');
        }

        try {
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
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can view admin accounts.');
        }

        $admins = Admin::with(['organization', 'branch', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can create admin accounts.');
        }

        // Get organizations and roles for the form
        $organizations = Organization::with('branches')->get();
        $roles = Role::where('guard_name', 'admin')->get();

        return view('admin.admins.create', compact('organizations', 'roles'));
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can create admin accounts.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'organization_id' => 'nullable|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'department' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended,pending',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $admin = Admin::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'organization_id' => $request->input('organization_id'),
                'branch_id' => $request->input('branch_id'),
                'is_super_admin' => $request->boolean('is_super_admin'),
                'is_active' => $request->boolean('is_active', true),
                'department' => $request->input('department'),
                'job_title' => $request->input('job_title'),
                'status' => $request->input('status'),
                'phone' => $request->input('phone'),
            ]);

            // Assign roles using Spatie's role assignment
            if ($request->filled('roles')) {
                $roles = Role::whereIn('id', $request->input('roles'))
                    ->where('guard_name', 'admin')
                    ->get();

                $admin->syncRoles($roles);

                // Set the first role as current_role_id
                if ($roles->isNotEmpty()) {
                    $admin->update(['current_role_id' => $roles->first()->id]);
                }

                // Log role assignment
                Log::info('Admin roles assigned', [
                    'admin_id' => $admin->id,
                    'roles' => $roles->pluck('name')->toArray(),
                    'effective_permissions' => $admin->getAllPermissions()->pluck('name')->toArray()
                ]);
            }

            DB::commit();

            return redirect()->route('admins.index')
                ->with('success', 'Admin created successfully with assigned roles.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    public function edit(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can edit admin accounts.');
        }

        $organizations = Organization::with('branches')->get();
        $roles = Role::where('guard_name', 'admin')->get();

        return view('admin.admins.edit', compact('admin', 'organizations', 'roles'));
    }

    public function update(Request $request, Admin $admin)
    {
        try {
            DB::beginTransaction();

            $currentAdmin = Auth::guard('admin')->user();

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
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
                'password' => 'nullable|string|min:8|confirmed',
                'is_super_admin' => 'boolean',
                'is_active' => 'boolean',
                'department' => 'nullable|string|max:255',
                'job_title' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive,suspended,pending',
                'phone' => 'nullable|string|max:20',
            ]);

            $data = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'organization_id' => $request->input('organization_id'),
                'branch_id' => $request->input('branch_id'),
                'is_super_admin' => $request->boolean('is_super_admin'),
                'is_active' => $request->boolean('is_active'),
                'department' => $request->input('department'),
                'job_title' => $request->input('job_title'),
                'status' => $request->input('status'),
                'phone' => $request->input('phone'),
            ];

            // Handle password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->input('password'));
            }

            $admin->update($data);

            // Handle role assignment using Spatie
            if ($request->has('roles')) {
                $roles = Role::whereIn('id', $request->input('roles'))
                    ->where('guard_name', 'admin')
                    ->get();

                $admin->syncRoles($roles);

                // Update current_role_id
                if ($roles->isNotEmpty()) {
                    $admin->update(['current_role_id' => $roles->first()->id]);
                } else {
                    $admin->update(['current_role_id' => null]);
                }

                // Log role assignment
                Log::info('Admin roles updated', [
                    'admin_id' => $admin->id,
                    'roles' => $roles->pluck('name')->toArray(),
                    'effective_permissions' => $admin->getAllPermissions()->pluck('name')->toArray()
                ]);
            }

            DB::commit();

            return redirect()->route('admins.index')
                ->with('success', 'Admin updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update admin', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified admin from storage
     */
    public function destroy(Admin $admin)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if (!$currentAdmin->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only super admins can delete admin accounts.');
        }

        // Prevent self-deletion
        if ($currentAdmin->id === $admin->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Remove all roles before deletion
        $admin->syncRoles([]);
        $admin->delete();

        return redirect()->route('admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    /**
     * Get branches for an organization (AJAX)
     */
    public function getAdminDetails($adminId)
    {
        try {
            $admin = Admin::with(['organization', 'roles'])->findOrFail($adminId);

            $stats = [
                'last_login' => $admin->last_login_at ? Carbon::parse($admin->last_login_at)->diffForHumans() : 'Never',
                'is_super_admin' => $admin->isSuperAdmin(),
                'role_count' => $admin->roles()->count(),
                'created_ago' => $admin->created_at->diffForHumans(),
            ];

            // Add permission details
            $permissions = method_exists($admin, 'getFormattedPermissions') ? $admin->getFormattedPermissions() : [];

            return response()->json([
                'success' => true,
                'admin' => $admin,
                'stats' => $stats,
                'permissions' => $permissions // Include permission details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ]);
        }
    }
}
