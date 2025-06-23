<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Role;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class EmployeeController extends Controller
{
    use Exportable;protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }
        
        // Super admins don't have organization restrictions
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return null; // Return null to indicate no restriction
        }
        
        if (!$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();
        
        $query = Employee::with(['branch', 'employeeRole']);
        
        // Apply organization filter only if not super admin
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }

        // Include trashed records if requested
        if ($request->boolean('show_deleted')) {
            $query->withTrashed();
        }

        // Apply filters
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('emp_id', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Handle both old role field and new restaurant roles
        if ($request->has('role') && $request->role) {
            if (in_array($request->role, array_keys(Employee::getAvailableRestaurantRoles()))) {
                // New restaurant role filter
                $query->whereHas('employeeRole', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            } else {
                // Legacy role filter
                $query->where('role', $request->role);
            }
        }

        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }        if ($request->has('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Apply filters and search for potential export
        $query = $this->applyFiltersToQuery($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'employees_export.xlsx', [
                'ID', 'Name', 'Email', 'Phone', 'Branch', 'Role', 'Status', 'Join Date', 'Created At'
            ]);
        }

        $employees = $query->orderBy('name')->paginate(15)->appends($request->query());
        $branches = ($orgId !== null) 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $roles = Employee::getAvailableRoles();
        $restaurantRoles = Employee::getAvailableRestaurantRoles();

        return view('admin.employees.index', compact('employees', 'branches', 'roles', 'restaurantRoles'));
    }

    /**
     * Get searchable columns for employees
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'email', 'phone', 'emp_id'];
    }public function create()
    {
        $orgId = $this->getOrganizationId();
        $branches = ($orgId !== null) 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $roles = Employee::getAvailableRoles();
        $restaurantRoles = SpatieRole::where('guard_name', 'web')
            ->whereIn('name', ['host/hostess', 'servers', 'bartenders', 'cashiers', 'chefs', 'dishwashers', 'kitchen-managers'])
            ->get();

        return view('admin.employees.create', compact('branches', 'roles', 'restaurantRoles'));
    }public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->where('organization_id', $orgId)
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|in:' . implode(',', array_keys(Employee::getAvailableRoles())),
            'restaurant_role' => 'required|exists:roles,name', // Make restaurant role required
            'branch_id' => 'required|exists:branches,id',
            'position' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',        ]);

        // Validate branch belongs to organization (skip for super admin)
        if ($orgId !== null) {
            $branch = Branch::where('id', $validated['branch_id'])
                ->where('organization_id', $orgId)
                ->firstOrFail();
        }

        $validated['organization_id'] = $orgId ?: Branch::find($validated['branch_id'])->organization_id;
        $validated['joined_date'] = now();
        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($validated, $request, &$employee) {
            $employee = Employee::create($validated);
            
            // Assign restaurant role if provided
            if ($request->has('restaurant_role') && $request->restaurant_role) {
                $role = SpatieRole::where('name', $request->restaurant_role)->first();
                if ($role) {
                    $employee->role_id = $role->id;
                    $employee->save();
                    $employee->assignRole($role);
                }
            }
        });

        return redirect()
            ->route('admin.employees.index')
            ->with('success', "Employee {$employee->name} created successfully!");
    }    public function show(Employee $employee)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all employees, others need organization match
        if ($orgId !== null && $employee->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $employee->load(['branch', 'orders', 'reservations']);
        
        return view('admin.employees.show', compact('employee'));
    }    public function edit(Employee $employee)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all employees, others need organization match
        if ($orgId !== null && $employee->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $orgId = $this->getOrganizationId();
        $branches = ($orgId !== null) 
            ? Branch::where('organization_id', $orgId)->active()->get()
            : Branch::active()->get();
        $roles = Employee::getAvailableRoles();
        $restaurantRoles = SpatieRole::where('guard_name', 'web')
            ->whereIn('name', ['host/hostess', 'servers', 'bartenders', 'cashiers', 'chefs', 'dishwashers', 'kitchen-managers'])
            ->get();
        $employee->load(['roles', 'employeeRole']);

        return view('admin.employees.edit', compact('employee', 'branches', 'roles', 'restaurantRoles'));
    }public function update(Request $request, Employee $employee)
    {
        if ($employee->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $orgId = $this->getOrganizationId();        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->where('organization_id', $orgId)->ignore($employee->id)
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|in:' . implode(',', array_keys(Employee::getAvailableRoles())),
            'restaurant_role' => 'required|exists:roles,name', // Make restaurant role required
            'branch_id' => 'required|exists:branches,id',
            'position' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',        ]);

        // Validate branch belongs to organization (skip for super admin)
        if ($orgId !== null) {
            $branch = Branch::where('id', $validated['branch_id'])
                ->where('organization_id', $orgId)
                ->firstOrFail();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($validated, $request, $employee, $orgId) {
            $employee->update($validated);
            
            // Update restaurant role
            if ($request->has('restaurant_role') && $request->restaurant_role) {
                $role = SpatieRole::where('name', $request->restaurant_role)->first();
                if ($role) {
                    $employee->role_id = $role->id;
                    $employee->save();
                    $employee->syncRoles([$role]);
                }
            } else {
                $employee->role_id = null;
                $employee->save();
                $employee->syncRoles([]);
            }
        });

        return redirect()
            ->route('admin.employees.index')
            ->with('success', "Employee {$employee->name} updated successfully!");
    }    public function destroy(Employee $employee)
    {
        $orgId = $this->getOrganizationId();
        
        // Super admins can access all employees, others need organization match
        if ($orgId !== null && $employee->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        // Check if employee has orders assigned
        if ($employee->orders()->exists()) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Cannot delete employee with assigned orders. Please reassign orders first.');
        }

        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully!');
    }

    /**
     * Restore a soft-deleted employee
     */
    public function restore($id)
    {
        try {
            DB::beginTransaction();
            
            $employee = Employee::withTrashed()
                ->where('organization_id', $this->getOrganizationId())
                ->findOrFail($id);
            
            if (!$employee->trashed()) {
                return redirect()->route('admin.employees.index')
                    ->with('error', 'Employee is not deleted');
            }
            
            $employee->restore();
            
            DB::commit();
            
            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee restored successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring employee: ' . $e->getMessage());
            
            return redirect()->route('admin.employees.index')
                ->with('error', 'Failed to restore employee');
        }
    }    /**
     * Get stewards for AJAX requests (legacy method)
     */
    public function getStewards(Request $request)
    {
        return $this->getServers($request); // Redirect to servers for backward compatibility
    }

    /**
     * Get servers for AJAX requests
     */
    public function getServers(Request $request)
    {
        $branchId = $request->get('branch_id');
        $orgId = $this->getOrganizationId();

        $servers = Employee::active()
            ->servers()
            ->where('organization_id', $orgId)
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->select('id', 'name', 'emp_id')
            ->orderBy('name')
            ->get();

        return response()->json($servers);
    }
}
