<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Organization $organization)
{
    Gate::authorize('viewAny', [Branch::class, $organization]);
    return $organization->branches()->get();
}

public function store(Request $request, $organizationId)
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

    $branch = Branch::create(array_merge($validated, [
        'organization_id' => $organizationId,
        'activation_key' => Str::random(40),
        'is_active' => true,
    ]));

    return redirect()->route('branches.index', $organizationId)->with('success', 'Branch created successfully.');
}

public function update(Request $request, Branch $branch)
{
    Gate::authorize('update', $branch);
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'is_active' => 'sometimes|boolean'
    ]);
    $branch->update($request->all());
    return response()->json($branch);
}

public function deactivate(Branch $branch)
{
    Gate::authorize('deactivate', $branch);
    $branch->update(['is_active' => false]);
    return response()->json(['message' => 'Branch deactivated']);
}

}
