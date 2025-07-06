<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    public function index(Organization $organization)
{

    return view('admin.branches.index', compact('organization'));
}

public function show(Organization $organization, Branch $branch)
{
    $this->authorize('view', $branch);
    
    // Load relationships for the branch view
    $branch->load([
        'admins.roles',
        'kitchenStations',
        'organization.subscriptionPlan'
    ]);
    
    // Get branch statistics
    $stats = [
        'admins_count' => $branch->admins()->count(),
        'active_admins_count' => $branch->admins()->where('is_active', true)->count(),
        'kitchen_stations_count' => $branch->kitchenStations()->count(),
        'active_kitchen_stations_count' => $branch->kitchenStations()->where('is_active', true)->count(),
        'orders_count' => $branch->orders()->count(),
        'today_orders_count' => $branch->orders()->whereDate('created_at', today())->count(),
    ];
    
    return view('admin.branches.show', compact('organization', 'branch', 'stats'));
}

public function store(Request $request, Organization $organization)
{
    $this->authorize('create', [Branch::class, $organization]);
    
    $isHeadOffice = $organization->branches()->count() === 0; // or use a flag

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:15',
        'opening_time' => 'required|date_format:H:i',
        'closing_time' => 'required|date_format:H:i',
        'total_capacity' => 'required|integer|min:1',
        'reservation_fee' => 'required|numeric|min:0',
        'cancellation_fee' => 'required|numeric|min:0',
        'contact_person' => $isHeadOffice ? 'nullable' : 'required|string|max:255',
        'contact_person_designation' => $isHeadOffice ? 'nullable' : 'required|string|max:255',
        'contact_person_phone' => $isHeadOffice ? 'nullable' : 'required|string|max:15',
    ]);

    if ($isHeadOffice) {
        $validated['contact_person'] = $organization->contact_person;
        $validated['contact_person_designation'] = $organization->contact_person_designation;
        $validated['contact_person_phone'] = $organization->contact_person_phone;
    }

    // Ensure opening_time and closing_time are always set
    $validated['opening_time'] = $validated['opening_time'] ?? '09:00:00';
    $validated['closing_time'] = $validated['closing_time'] ?? '22:00:00';

    $branch = $organization->branches()->create(array_merge($validated, [
        'activation_key' => Str::random(40),
        'is_active' => false,
    ]));

    return redirect()->route('admin.branches.index', ['organization' => $organization->id])
        ->with('success', 'Branch created successfully.');
}

public function update(Request $request, Organization $organization, Branch $branch)
{
    $this->authorize('update', $branch);

    $admin = auth('admin')->user();
    $isHeadOffice = $branch->id == optional($organization->branches->sortBy('id')->first())->id;
    $isBranchAdmin = $admin->isBranchAdmin() && $admin->branch_id == $branch->id;
    $isOrgAdmin = $admin->isOrganizationAdmin() && $admin->organization_id == $branch->organization_id;
    
    // Define validation rules based on user permissions
    if ($admin->isSuperAdmin()) {
        // Super admin can edit all fields
        $validationRules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'total_capacity' => 'required|integer|min:1',
            'reservation_fee' => 'required|numeric',
            'cancellation_fee' => 'required|numeric',
            'is_active' => 'required|boolean',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_designation' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
        ];
    } elseif ($isOrgAdmin || $isBranchAdmin) {
        // Org admin and branch admin can edit contact details and address/phone
        $validationRules = [
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_designation' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
        ];
    } else {
        abort(403, 'Unauthorized action.');
    }

    $validated = $request->validate($validationRules);

    // For head office, default contact person fields to organization if not set
    if ($isHeadOffice) {
        $validated['contact_person'] = $organization->contact_person;
        $validated['contact_person_designation'] = $organization->contact_person_designation;
        $validated['contact_person_phone'] = $organization->contact_person_phone;
    }

    Log::info('Branch update data', $validated);

    $branch->update($validated);

    return redirect()->route('admin.branches.index', ['organization' => $organization->id])
        ->with('success', 'Branch updated successfully.');
}

public function deactivate(Branch $branch)
{
    $this->authorize('deactivate', $branch);
    $branch->update(['is_active' => false]);
    return response()->json(['message' => 'Branch deactivated']);
}
public function create(Organization $organization)
{
    $this->authorize('create', [Branch::class, $organization]);
    
    $isHeadOffice = $organization->branches()->count() === 0; // or use a flag
    return view('admin.branches.create', compact('organization', 'isHeadOffice'));
}
public function activateAll(Organization $organization)
{
    $organization->branches()->update(['is_active' => true]);
    return back()->with('success', 'All branches activated');
}

public function deactivateAll(Organization $organization)
{
    $organization->branches()->update(['is_active' => false]);
    return back()->with('success', 'All branches deactivated');
}
public function globalIndex()
{
    // You can add authorization here if needed
    $branches = \App\Models\Branch::with('organization')->get();
    return view('admin.branches.index', compact('branches'));
}
public function edit($organizationId, $branchId)
{
    $organization = \App\Models\Organization::findOrFail($organizationId);
    $branch = \App\Models\Branch::where('organization_id', $organizationId)->findOrFail($branchId);

    $this->authorize('update', $branch);

    return view('admin.branches.edit', compact('organization', 'branch'));
}
public function showActivationForm()
{
    $admin = auth('admin')->user();

    if ($admin->is_super_admin) {
        // Super admin can see all branches
        $branches = Branch::with('organization')->get();
        return view('admin.branches.activate', compact('branches'));
    } elseif ($admin->organization_id && !$admin->branch_id) {
        // Organization admin can see their organization's branches
        $branches = Branch::with('organization')
            ->where('organization_id', $admin->organization_id)
            ->get();
        return view('admin.branches.activate', compact('branches'));
    } elseif ($admin->branch_id) {
        // Branch admin can see only their branch
        $branch = Branch::with('organization')->find($admin->branch_id);
        return view('admin.branches.activate', compact('branch'));
    } else {
        abort(403, 'No branch access');
    }
}

public function activateBranch(Request $request)
{
    $request->validate([
        'activation_key' => 'required|string',
        'branch_id' => 'required|exists:branches,id'
    ]);

    $branch = Branch::with('organization')->find($request->branch_id);
    
    // Use policy for authorization
    $this->authorize('activate', $branch);

    // Only allow activation if organization is active
    if (!$branch->organization->is_active) {
        return back()->with('error', 'Cannot activate branch. The parent organization is not active.');
    }

    if ($branch->activation_key !== $request->activation_key) {
        return back()->with('error', 'Invalid activation key.');
    }

    $branch->update([
        'is_active' => true,
        'activated_at' => now(),
        'activation_key' => null,
    ]);

    return back()->with('success', 'Branch activated successfully!');
}

// Show summary
public function summary(Branch $branch)
{
    $branch->load([
        'organization.subscriptionPlan',
        'admins.roles',
        'kitchenStations',
        'users.roles',
        'subscriptions',
        'tables',
        'menuCategories',
        'menuItems'
    ]);

    // Calculate branch statistics
    $stats = [
        'total_admins' => $branch->admins()->count(),
        'active_admins' => $branch->admins()->where('is_active', true)->count(),
        'total_users' => $branch->users()->count(),
        'active_users' => $branch->users()->where('is_active', true)->count(),
        'kitchen_stations' => $branch->kitchenStations()->count(),
        'active_kitchen_stations' => $branch->kitchenStations()->where('is_active', true)->count(),
        'total_tables' => $branch->tables()->count(),
        'menu_categories' => $branch->menuCategories()->count(),
        'menu_items' => $branch->menuItems()->count(),
        'available_modules' => $branch->organization->subscriptionPlan ? 
            $branch->organization->subscriptionPlan->getModulesWithNames() : [],
    ];

    return view('admin.branches.summary', compact('branch', 'stats'));
}

public function regenerateKey(Branch $branch)
{
    $this->authorize('regenerateKey', $branch);
    $branch->activation_key = \Illuminate\Support\Str::random(40);
    $branch->save();

    return back()->with('success', 'Activation key regenerated!');
}
public function destroy(Organization $organization, Branch $branch)
{
    $admin = Auth::guard('admin')->user();

    // Only super admins can delete branches
    if (!$admin || !$admin->isSuperAdmin()) {
        return redirect()->back()
            ->with('error', 'Only super administrators can delete branches.');
    }

    // Only inactive branches can be deleted
    if ($branch->is_active) {
        return redirect()->back()
            ->with('error', 'Cannot delete active branch. Please deactivate it first.');
    }

    // Check if branch belongs to the organization
    if ($branch->organization_id !== $organization->id) {
        return redirect()->back()
            ->with('error', 'Branch does not belong to this organization.');
    }

    try {
        DB::beginTransaction();

        $branchName = $branch->name;
        $userCount = $branch->users()->count();
        $orderCount = $branch->orders()->count();
        $kitchenStationCount = $branch->kitchenStations()->count();
        $reservationCount = $branch->reservations()->count();
        $menuItemCount = $branch->menuItems()->count();

        // Soft delete related users
        $branch->users()->delete();

        // Note: Orders and reservations should NOT be deleted as they are historical data
        // Kitchen stations and menu items can be soft deleted
        $branch->kitchenStations()->delete();
        $branch->menuItems()->delete();
        $branch->menuCategories()->delete();

        // Soft delete the branch itself
        $branch->delete();

        DB::commit();

        Log::info('Branch soft deleted by super admin', [
            'branch_name' => $branchName,
            'branch_id' => $branch->id,
            'organization_id' => $organization->id,
            'deleted_by' => $admin->id,
            'deleted_by_name' => $admin->name,
            'users_deleted' => $userCount,
            'orders_preserved' => $orderCount,
            'reservations_preserved' => $reservationCount,
            'kitchen_stations_deleted' => $kitchenStationCount,
            'menu_items_deleted' => $menuItemCount,
            'was_active' => false
        ]);

        return redirect()
            ->route('admin.branches.index', ['organization' => $organization->id])
            ->with('success', "Branch '{$branchName}' and related data soft deleted successfully. Orders and reservations preserved.");

    } catch (\Exception $e) {
        Log::error('Failed to delete branch', [
            'branch_id' => $branch->id,
            'organization_id' => $organization->id,
            'error' => $e->getMessage(),
            'deleted_by' => $admin->id
        ]);

        return redirect()->back()
            ->with('error', 'Failed to delete branch: ' . $e->getMessage());
    }
}

/**
     * Get branch details for modal (AJAX)
     */
    public function getBranchDetails(Branch $branch)
    {
        $branch->load([
            'organization',
            'kitchenStations',
            'users' => function($query) {
                $query->where('is_active', true);
            }
        ]);

        $stats = [
            'kitchen_stations' => $branch->kitchenStations()->count(),
            'active_users' => $branch->users()->where('is_active', true)->count(),
            'todays_orders' => $branch->orders()->whereDate('created_at', today())->count(),
            'todays_reservations' => $branch->reservations()->whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'branch' => $branch,
            'stats' => $stats
        ]);
    }
}
