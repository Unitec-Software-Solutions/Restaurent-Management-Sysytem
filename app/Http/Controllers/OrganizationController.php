<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Organization::class);
        $organizations = Organization::with(['branches', 'subscriptions'])->get();
        return view('admin.organizations.index', compact('organizations'));
    }

    // Add create method for organization registration
    public function create()
    {
        Gate::authorize('create', Organization::class);
        $plans = \App\Models\SubscriptionPlan::all();
        return view('admin.organizations.create', compact('plans'));
    }

    // Add store method for organization registration
    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email',
            'phone' => 'required|regex:/^\d{10,15}$/',
            'password' => 'required|string|min:6',
            'address' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_person_designation' => 'required|string|max:255',
            'contact_person_phone' => 'required|regex:/^\d{10,15}$/',
            'is_active' => 'required|boolean',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ], [
            'phone.regex' => 'Phone must be 10-15 digits.',
            'contact_person_phone.regex' => 'Contact person phone must be 10-15 digits.',
        ]);

        // Create organization
        $organization = Organization::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
            'address' => $validated['address'],
            'contact_person' => $validated['contact_person'],
            'contact_person_designation' => $validated['contact_person_designation'],
            'contact_person_phone' => $validated['contact_person_phone'],
            'is_active' => $validated['is_active'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
        ]);

        return redirect()->route('admin.organizations.index')->with('success', 'Organization created successfully');
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email,' . $organization->id,
            'phone' => 'required|regex:/^\d{10,15}$/',
            'address' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_person_designation' => 'required|string|max:255',
            'contact_person_phone' => 'required|regex:/^\d{10,15}$/',
            'is_active' => 'required|boolean',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ], [
            'phone.regex' => 'Phone must be 10-15 digits.',
            'contact_person_phone.regex' => 'Contact person phone must be 10-15 digits.',
        ]);

        $organization->update($validated);

        // Update subscription if a new plan is selected
        if ($request->filled('subscription_plan_id')) {
            // Optionally, end previous active subscription
            $organization->subscriptions()->where('is_active', true)->update(['is_active' => false, 'end_date' => now()]);

            // Create new subscription
            $organization->subscriptions()->create([
                'plan_id' => $request->subscription_plan_id,
                'status' => 'active',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ]);
        }

        return redirect()->route('admin.organizations.index')->with('success', 'Organization updated successfully');
    }

    public function deactivate(Organization $organization)
    {
        Gate::authorize('deactivate', $organization);
        $organization->update(['is_active' => false]);
        $organization->branches()->update(['is_active' => false]);
        return response()->json(['message' => 'Organization and branches deactivated']);
    }

    private function createDefaultRoles(Organization $org, Branch $branch)
    {
        $adminRole = $org->roles()->create(['name' => 'Organization Admin']);
        $adminRole->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        $branchRoles = [
            'Branch Manager' => ['manage_reservation', 'manage_order', 'manage_inventory'],
            'Staff' => ['create_order', 'view_inventory']
        ];

        foreach ($branchRoles as $name => $permissions) {
            $role = $org->roles()->create([
                'name' => $name,
                'branch_id' => $branch->id
            ]);
            $role->givePermissionTo($permissions);
        }
    }

    public function edit(Organization $organization)
    {
        $plans = \App\Models\SubscriptionPlan::all();
        return view('admin.organizations.edit', compact('organization', 'plans'));
    }

    public function destroy(Organization $organization)
    {
        // Optional: Add authorization if needed
        $this->authorize('delete', $organization);

        // Delete related branches if needed
        $organization->branches()->delete();

        // Delete the organization
        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }

    public function summary(Organization $organization)
    {
        $organization->load(['plan', 'branches']);
        return view('admin.organizations.summary', compact('organization'));
    }

    public function regenerateKey(Organization $organization)
    {
        $this->authorize('update', $organization);
        $organization->activation_key = \Illuminate\Support\Str::random(40);
        $organization->save();

        return back()->with('success', 'Activation key regenerated!');
    }

    public function showActivationForm()
    {
        $admin = auth('admin')->user();

        if ($admin->isSuperAdmin()) {
            $organizations = \App\Models\Organization::all();
            return view('admin.organizations.activate', compact('organizations'));
        } else {
            $organization = \App\Models\Organization::find($admin->organization_id);
            return view('admin.organizations.activate', compact('organization'));
        }
    }

    public function activateOrganization(Request $request)
    {
        $request->validate([
            'activation_key' => 'required|string',
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $organization = \App\Models\Organization::find($request->organization_id);

        if (!$organization || $organization->activation_key !== $request->activation_key) {
            return back()->with('error', 'Invalid activation key.');
        }

        $organization->is_active = true;
        $organization->activated_at = now();
        $organization->activation_key = null; // Optionally clear the key after activation
        $organization->save();

        return back()->with('success', 'Organization activated successfully!');
    }
}
