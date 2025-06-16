<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index(Organization $organization)
{
    
    return view('admin.branches.index', compact('organization'));
}

public function store(Request $request, Organization $organization)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:15',
        'opening_time' => 'required|date_format:H:i',
        'closing_time' => 'required|date_format:H:i',
        'total_capacity' => 'required|integer|min:1',
        'reservation_fee' => 'required|numeric|min:0',
        'cancellation_fee' => 'required|numeric|min:0',
    ]);

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

    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'opening_time' => 'required',
        'closing_time' => 'required',
        'total_capacity' => 'required|integer',
        'reservation_fee' => 'required|numeric',
        'cancellation_fee' => 'required|numeric',
        'is_active' => 'required|boolean',
    ]);

    $branch->update($request->all());

    return redirect()->route('admin.branches.index', ['organization' => $organization->id])
        ->with('success', 'Branch updated successfully.');
}

public function deactivate(Branch $branch)
{
    Gate::authorize('deactivate', $branch);
    $branch->update(['is_active' => false]);
    return response()->json(['message' => 'Branch deactivated']);
}
public function create(Organization $organization)
{
    $this->authorize('create', [Branch::class, $organization]);
    return view('admin.branches.create', compact('organization'));
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

    if ($admin->isSuperAdmin()) {
        $branches = Branch::with('organization')->get();
        return view('admin.branches.activate', compact('branches'));
    } elseif ($admin->branch_id) {
        $branch = \App\Models\Branch::with('organization')->find($admin->branch_id);
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

    $branch = Branch::find($request->branch_id);

    if (!$branch || $branch->activation_key !== $request->activation_key) {
        return back()->with('error', 'Invalid activation key.');
    }

    $branch->is_active = true;
    $branch->activated_at = now();
    $branch->activation_key = null; // Optionally clear the key after activation
    $branch->save();

    return back()->with('success', 'Branch activated successfully!');
}

// Show summary
public function summary(Branch $branch)
{
    $branch->load(['organization', 'subscriptions']);
    return view('admin.branches.summary', compact('branch'));
}

public function regenerateKey(Branch $branch)
{
    $this->authorize('update', $branch);
    $branch->activation_key = \Illuminate\Support\Str::random(40);
    $branch->save();

    return back()->with('success', 'Activation key regenerated!');
}
public function destroy(Organization $organization, Branch $branch)
{
    $this->authorize('delete', $branch);

    $branch->delete();

    return redirect()
        ->route('admin.branches.index', ['organization' => $organization->id])
        ->with('success', 'Branch deleted successfully.');
}
}
