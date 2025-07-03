<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations
     */
    public function index()
    {
        $organizations = Organization::with(['branches', 'users', 'subscriptionPlan'])
            ->withCount(['branches', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization
     */
    public function create()
    {
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->get();
        
        return view('admin.organizations.create', compact('subscriptionPlans'));
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organizations',
            'email' => 'required|email|unique:organizations',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        
        try {
            // Create organization - DEFAULT to INACTIVE
            $organization = Organization::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'subscription_plan_id' => $request->subscription_plan_id,
                'activation_key' => Str::random(40),
                'is_active' => false, // Organizations must be activated by super admin
            ]);

            // Create admin user
            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'organization_id' => $organization->id,
                'is_admin' => true,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Create default head office branch
            $headOfficeBranch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $organization->name . ' - Head Office',
                'address' => $organization->address,
                'phone' => $organization->phone,
                'email' => $organization->email,
                'is_head_office' => true,
                'is_active' => false, // Requires activation
                'activation_key' => Str::random(40),
                'type' => 'restaurant',
                'status' => 'inactive',
            ]);

            DB::commit();

            Log::info('Organization created successfully', [
                'organization_id' => $organization->id,
                'admin_user_id' => $admin->id,
                'head_office_branch_id' => $headOfficeBranch->id
            ]);

            return redirect()->route('admin.organizations.index')
                ->with('success', 'Organization created successfully with head office branch.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to create organization', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['admin_password', 'admin_password_confirmation'])
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create organization: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization)
    {
        $organization->load([
            'branches.users',
            'users.roles',
            'subscriptionPlan'
        ]);

        $stats = [
            'total_branches' => $organization->branches()->count(),
            'active_branches' => $organization->branches()->where('is_active', true)->count(),
            'total_users' => $organization->users()->count(),
            'registered_users' => $organization->users()->where('is_registered', true)->count(),
            'subscription_days_left' => $organization->subscription_end_date ? 
                now()->diffInDays($organization->subscription_end_date, false) : 0,
        ];

        return view('admin.organizations.summary', compact('organization', 'stats'));
    }

    /**
     * Show the form for editing the specified organization
     */
    public function edit(Organization $organization)
    {
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->get();
        
        // Debug: Check if subscriptionPlans is loaded
        if ($subscriptionPlans->isEmpty()) {
            Log::warning('No active subscription plans found for organization edit');
        }
        
        return view('admin.organizations.edit', compact('organization', 'subscriptionPlans'));
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organizations')->ignore($organization->id)],
            'email' => ['required', 'email', Rule::unique('organizations')->ignore($organization->id)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'is_active' => 'required|boolean',
            'contact_person' => 'required|string|max:255',
            'contact_person_designation' => 'required|string|max:255',
            'contact_person_phone' => 'required|string|max:20',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $wasInactive = !$organization->is_active;
        $willBeActive = $request->boolean('is_active');

        DB::beginTransaction();
        
        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'subscription_plan_id' => $request->subscription_plan_id,
                'is_active' => $willBeActive,
                'contact_person' => $request->contact_person,
                'contact_person_designation' => $request->contact_person_designation,
                'contact_person_phone' => $request->contact_person_phone,
                'discount_percentage' => $request->discount_percentage,
            ];
            
            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            $organization->update($updateData);

            // If organization is being deactivated, deactivate all branches
            if (!$willBeActive && $organization->is_active) {
                $organization->branches()->update(['is_active' => false]);
            }

            DB::commit();

            Log::info('Organization updated successfully', [
                'organization_id' => $organization->id,
                'changes' => $organization->getChanges()
            ]);

            return redirect()->route('admin.organizations.index')
                ->with('success', 'Organization updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update organization: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization)
    {
        try {
            // Check if organization has active branches or users
            $branchCount = $organization->branches()->count();
            $userCount = $organization->users()->count();
            
            if ($branchCount > 0 || $userCount > 0) {
                return redirect()->back()
                    ->with('error', "Cannot delete organization with {$branchCount} branches and {$userCount} users. Please remove them first.");
            }

            $organizationName = $organization->name;
            $organization->delete();

            Log::info('Organization deleted successfully', [
                'organization_name' => $organizationName,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('admin.organizations.index')
                ->with('success', 'Organization deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete organization: ' . $e->getMessage());
        }
    }

    /**
     * Show organization summary
     */
    public function summary(Organization $organization)
    {
        $organization->load([
            'branches.users',
            'users.roles',
            'subscriptionPlan'
        ]);

        $stats = [
            'total_branches' => $organization->branches()->count(),
            'active_branches' => $organization->branches()->where('is_active', true)->count(),
            'total_users' => $organization->users()->count(),
            'registered_users' => $organization->users()->where('is_registered', true)->count(),
            'subscription_days_left' => $organization->subscription_end_date ? 
                now()->diffInDays($organization->subscription_end_date, false) : 0,
        ];

        // Get recent activity
        $recentActivity = [
            'orders' => $this->getRecentOrders($organization),
            'reservations' => $this->getRecentReservations($organization),
            'users' => $this->getRecentUsers($organization),
        ];

        return view('admin.organizations.summary', compact('organization', 'stats', 'recentActivity'));
    }

    /**
     * Regenerate activation key
     */
    public function regenerateKey(Organization $organization)
    {
        $admin = Auth::guard('admin')->user();
        
        // Only super admins can regenerate activation keys
        if (!$admin || !$admin->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'You do not have permission to regenerate activation keys.');
        }

        $oldKey = $organization->activation_key;
        $newKey = Str::uuid();
        
        $organization->update([
            'activation_key' => $newKey
        ]);

        Log::info('Organization activation key regenerated', [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'old_key_preview' => substr($oldKey, 0, 8) . '...',
            'new_key_preview' => substr($newKey, 0, 8) . '...',
            'regenerated_by' => $admin->id,
            'regenerated_by_name' => $admin->name
        ]);

        return redirect()->back()
            ->with('success', 'Activation key regenerated successfully.');
    }

    /**
     * Show activation form
     */
    public function showActivationForm()
    {
        return view('admin.organizations.activate');
    }

    /**
     * Activate organization with key
     */
    public function activateOrganization(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);

        $organization = Organization::where('api_key', $request->api_key)->first();

        if (!$organization) {
            return redirect()->back()
                ->with('error', 'Invalid API key.');
        }

        if (!$organization->is_active) {
            return redirect()->back()
                ->with('error', 'Organization is not active.');
        }

        // Update user's organization
        $user = Auth::user();
        $user->update([
            'organization_id' => $organization->id
        ]);

        Log::info('User activated organization', [
            'user_id' => $user->id,
            'organization_id' => $organization->id
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Organization activated successfully.');
    }

    /**
     * Show organization activation index - different view based on user role
     */
    public function activationIndex()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        if ($admin->isSuperAdmin()) {
            // Super admin can see all organizations
            $organizations = Organization::with(['branches', 'users', 'subscriptionPlan'])
                ->orderBy('is_active', 'asc') // Show inactive first
                ->orderBy('name', 'asc')
                ->get();
        } else {
            // Organization admin can only see their own organization
            if (!$admin->organization_id) {
                abort(403, 'No organization assigned to this admin');
            }
            
            $organizations = Organization::with(['branches', 'users', 'subscriptionPlan'])
                ->where('id', $admin->organization_id)
                ->get();
        }

        return view('admin.organizations.activation.index', compact('organizations'));
    }

    /**
     * Activate organization by providing activation key
     */
    public function activateByKey(Request $request, Organization $organization)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        // Check permissions: Super admin can activate any org, org admin can only activate their own
        if (!$admin->isSuperAdmin() && $admin->organization_id !== $organization->id) {
            abort(403, 'You can only activate your own organization');
        }

        $request->validate([
            'activation_key' => 'required|string',
        ]);

        // Verify activation key
        if ($request->activation_key !== $organization->activation_key) {
            return redirect()->back()
                ->with('error', 'Invalid activation key provided.');
        }

        // Check if already active
        if ($organization->is_active) {
            return redirect()->back()
                ->with('info', 'Organization is already active.');
        }

        DB::beginTransaction();
        
        try {
            // Activate organization
            $organization->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Log the activation
            Log::info('Organization activated by admin', [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'activated_by' => $admin->id,
                'admin_name' => $admin->name,
                'admin_type' => $admin->isSuperAdmin() ? 'super_admin' : 'organization_admin',
                'activated_at' => now(),
            ]);

            DB::commit();
            
            return redirect()->route('admin.organizations.activation.index')
                ->with('success', "Organization '{$organization->name}' activated successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to activate organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to activate organization: ' . $e->getMessage());
        }
    }

    /**
     * Get recent orders for organization
     */
    private function getRecentOrders(Organization $organization)
    {
        if (!class_exists(\App\Models\Order::class)) {
            return collect();
        }

        return \App\Models\Order::where('organization_id', $organization->id)
            ->with(['customer', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent reservations for organization
     */
    private function getRecentReservations(Organization $organization)
    {
        if (!class_exists(\App\Models\Reservation::class)) {
            return collect();
        }

        return \App\Models\Reservation::where('organization_id', $organization->id)
            ->with(['customer', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent users for organization
     */
    private function getRecentUsers(Organization $organization)
    {
        return $organization->users()
            ->with(['roles', 'branch'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get organization statistics
     */
    public function getStats(Organization $organization)
    {
        $stats = [
            'branches' => [
                'total' => $organization->branches()->count(),
                'active' => $organization->branches()->where('is_active', true)->count(),
                'inactive' => $organization->branches()->where('is_active', false)->count(),
            ],
            'users' => [
                'total' => $organization->users()->count(),
                'registered' => $organization->users()->where('is_registered', true)->count(),
                'unregistered' => $organization->users()->where('is_registered', false)->count(),
            ],
            'subscription' => [
                'plan' => $organization->subscriptionPlan->name ?? 'No Plan',
                'status' => $organization->subscription_end_date > now() ? 'Active' : 'Expired',
                'days_left' => $organization->subscription_end_date ? 
                    now()->diffInDays($organization->subscription_end_date, false) : 0,
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Activate all branches for organization
     */
    public function activateAllBranches(Organization $organization)
    {
        if (!$organization->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot activate branches. Organization is not active.');
        }

        $count = $organization->branches()->update(['is_active' => true]);

        Log::info('All branches activated for organization', [
            'organization_id' => $organization->id,
            'branches_activated' => $count,
            'activated_by' => Auth::id()
        ]);

        return redirect()->back()
            ->with('success', "{$count} branches activated successfully.");
    }

    /**
     * Deactivate all branches for organization
     */
    public function deactivateAllBranches(Organization $organization)
    {
        $count = $organization->branches()->update(['is_active' => false]);

        Log::info('All branches deactivated for organization', [
            'organization_id' => $organization->id,
            'branches_deactivated' => $count,
            'deactivated_by' => Auth::id()
        ]);

        return redirect()->back()
            ->with('success', "{$count} branches deactivated successfully.");
    }

    /**
     * Show activation form for super admin
     */
    public function showActivateForm(Organization $organization)
    {
        return view('admin.organizations.activate', compact('organization'));
    }

    /**
     * Activate/Deactivate organization by super admin
     */
    public function activate(Request $request, Organization $organization)
    {
        // Check permissions based on action
        if ($request->action === 'deactivate') {
            // Only super admins can deactivate organizations
            if (!Auth::guard('admin')->user()->isSuperAdmin()) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to deactivate organizations.');
            }
        } else {
            // For activation, both super admins and organization admins can activate
            if (!Auth::guard('admin')->user()->isSuperAdmin() && 
                !Auth::guard('admin')->user()->canManageOrganization($organization)) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to activate this organization.');
            }
        }

        $request->validate([
            'action' => 'required|in:activate,deactivate',
            'activation_key' => 'required_if:action,activate|string',
        ]);

        DB::beginTransaction();
        
        try {
            if ($request->action === 'activate') {
                // Verify activation key if provided
                if ($request->filled('activation_key') && $request->activation_key !== $organization->activation_key) {
                    return redirect()->back()
                        ->with('error', 'Invalid activation key provided.');
                }

                $organization->update([
                    'is_active' => true,
                    'activated_at' => now(),
                ]);

                $message = 'Organization activated successfully.';
                
                // Log the activation
                Log::info('Organization activated by super admin', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'activated_by' => Auth::id(),
                    'activated_at' => now(),
                ]);

            } else {
                // Deactivate organization
                $organization->update([
                    'is_active' => false,
                ]);

                // Also deactivate all branches when organization is deactivated
                $organization->branches()->update(['is_active' => false]);

                $message = 'Organization deactivated successfully.';
                
                // Log the deactivation
                Log::info('Organization deactivated by super admin', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'deactivated_by' => Auth::id(),
                    'deactivated_at' => now(),
                ]);
            }

            DB::commit();
            
            return redirect()->route('admin.organizations.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to change organization status', [
                'organization_id' => $organization->id,
                'action' => $request->action,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to change organization status: ' . $e->getMessage());
        }
    }
}
