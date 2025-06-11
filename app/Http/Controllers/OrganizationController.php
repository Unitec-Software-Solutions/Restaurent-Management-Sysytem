<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Organization::class);
        return Organization::with(['branches', 'subscriptions'])->get();
    }

    // Add create method for organization registration
    public function create()
    {
        Gate::authorize('create', Organization::class);
        return view('organizations.create');
    }

    // Add store method for organization registration
    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email',
            'password' => 'required|string|min:8',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15', // Ensure phone is validated
        ]);

        $organization = Organization::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'address' => $validated['address'],
            'phone' => $validated['phone'], // Include phone in creation
        ]);

        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    public function update(Request $request, Organization $organization)
    {
        Gate::authorize('update', $organization);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean'
        ]);
        $organization->update($request->all());
        return response()->json($organization);
    }

    public function deactivate(Organization $organization)
    {
        Gate::authorize('deactivate', $organization);
        $organization->update(['is_active' => false]);
        $organization->branches()->update(['is_active' => false]);
        return response()->json(['message' => 'Organization and branches deactivated']);
    }
}
