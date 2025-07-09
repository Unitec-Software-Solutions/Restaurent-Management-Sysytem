<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Services\OrganizationAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    protected $organizationAutomationService;
    public function __construct(OrganizationAutomationService $organizationAutomationService)
    {
        $this->organizationAutomationService = $organizationAutomationService;
    }
    public function index()
    {
        // List organizations
        $organizations = Organization::all();
        return view('admin.organizations.index', compact('organizations'));
    }
    public function create()
    {
        // Show create form
        return view('admin.organizations.create');
    }
    public function store(Request $request)
    {
        // Validate and store organization
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ...other rules...
        ]);
        $org = Organization::create($validated);
        return redirect()->route('admin.organizations.index')->with('success', 'Organization created.');
    }
}