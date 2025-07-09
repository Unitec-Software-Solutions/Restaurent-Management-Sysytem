<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BranchController extends Controller
{
    public function index(Organization $organization)
    {
        $branches = Branch::where('organization_id', $organization->id)->get();
        return view('admin.branches.index', compact('organization', 'branches'));
    }

    public function show(Organization $organization, Branch $branch)
    {
        return view('admin.branches.show', compact('organization', 'branch'));
    }

    public function store(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ...other rules...
        ]);
        $branch = $organization->branches()->create($validated);
        return redirect()->route('admin.branches.index', $organization)->with('success', 'Branch created.');
    }

    public function update(Request $request, Organization $organization, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ...other rules...
        ]);
        $branch->update($validated);
        return redirect()->route('admin.branches.show', [$organization, $branch])->with('success', 'Branch updated.');
    }

    public function deactivate(Branch $branch)
    {
        $branch->update(['is_active' => false]);
        return redirect()->back()->with('success', 'Branch deactivated.');
    }

    public function create(Organization $organization)
    {
        return view('admin.branches.create', compact('organization'));
    }

    public function activateAll(Organization $organization)
    {
        $organization->branches()->update(['is_active' => true]);
        return redirect()->back()->with('success', 'All branches activated.');
    }
}
