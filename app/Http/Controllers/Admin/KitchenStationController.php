<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KitchenStation;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitchenStationController extends Controller
{
    /**
     * Display a listing of kitchen stations
     */
    public function index(): View
    {
        $admin = Auth::guard('admin')->user();
        
        $query = KitchenStation::with('branch')
            ->when($admin && !$admin->is_super_admin, function($q) use ($admin) {
                return $q->where('organization_id', $admin->organization_id);
            })
            ->latest();

        $kitchenStations = $query->paginate(12);
        
        return view('admin.kitchen.stations.index', compact('kitchenStations'));
    }

    /**
     * Show the form for creating a new kitchen station
     */
    public function create(): View
    {
        $admin = Auth::guard('admin')->user();
        
        $branches = Branch::when($admin && !$admin->is_super_admin, function($q) use ($admin) {
                return $q->where('organization_id', $admin->organization_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.kitchen.stations.create', compact('branches'));
    }

    /**
     * Store a newly created kitchen station
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'branch_id' => 'required|exists:branches,id',
            'station_type' => 'nullable|string|in:hot_kitchen,cold_kitchen,grill,prep,dessert,serving,other',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:20',
            'priority_order' => 'nullable|integer|min:1',
            'equipment' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'auto_assign_kots' => 'boolean'
        ]);

        // Add organization context
        if ($admin && !$admin->is_super_admin) {
            $validated['organization_id'] = $admin->organization_id;
        }

        // Generate station code if not provided
        if (!isset($validated['station_code'])) {
            $validated['station_code'] = $this->generateStationCode($validated['name']);
        }

        try {
            DB::beginTransaction();
            
            $station = KitchenStation::create($validated);
            
            DB::commit();
            
            return redirect()
                ->route('admin.kitchen.stations.index')
                ->with('success', 'Kitchen station created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create kitchen station: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a kitchen station
     */
    public function edit(KitchenStation $station): View
    {
        $admin = Auth::guard('admin')->user();
        
        // Authorization check
        if ($admin && !$admin->is_super_admin && $station->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access to kitchen station.');
        }
        
        $branches = Branch::when($admin && !$admin->is_super_admin, function($q) use ($admin) {
                return $q->where('organization_id', $admin->organization_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.kitchen.stations.edit', compact('station', 'branches'));
    }

    /**
     * Update the specified kitchen station
     */
    public function update(Request $request, KitchenStation $station): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Authorization check
        if ($admin && !$admin->is_super_admin && $station->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access to kitchen station.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'branch_id' => 'required|exists:branches,id',
            'station_type' => 'nullable|string|in:hot_kitchen,cold_kitchen,grill,prep,dessert,serving,other',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:20',
            'priority_order' => 'nullable|integer|min:1',
            'equipment' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'auto_assign_kots' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $station->update($validated);
            
            DB::commit();
            
            return redirect()
                ->route('admin.kitchen.stations.index')
                ->with('success', 'Kitchen station updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update kitchen station: ' . $e->getMessage());
        }
    }

    /**
     * Toggle station status (activate/deactivate)
     */
    public function toggleStatus(Request $request, KitchenStation $station): JsonResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Authorization check
        if ($admin && !$admin->is_super_admin && $station->organization_id !== $admin->organization_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $newStatus = $request->input('is_active', !$station->is_active);
            $station->update(['is_active' => $newStatus]);
            
            DB::commit();
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "Kitchen station {$statusText} successfully.",
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update station status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified kitchen station
     */
    public function destroy(KitchenStation $station): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Authorization check
        if ($admin && !$admin->is_super_admin && $station->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access to kitchen station.');
        }
        
        try {
            DB::beginTransaction();
            
            // Check if station has active KOTs
            $activeKots = $station->kots()->whereIn('status', ['pending', 'preparing'])->count();
            
            if ($activeKots > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete station with active KOTs. Please complete or reassign them first.');
            }
            
            $station->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.kitchen.stations.index')
                ->with('success', 'Kitchen station deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete kitchen station: ' . $e->getMessage());
        }
    }

    /**
     * Get station details for AJAX requests
     */
    public function show(KitchenStation $station): JsonResponse
    {
        $admin = Auth::guard('admin')->user();
        
        // Authorization check
        if ($admin && !$admin->is_super_admin && $station->organization_id !== $admin->organization_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }
        
        $station->load(['branch', 'kots' => function($query) {
            $query->whereIn('status', ['pending', 'preparing', 'ready']);
        }]);
        
        return response()->json([
            'success' => true,
            'station' => $station,
            'activeKots' => $station->kots->count(),
            'workload' => [
                'pending' => $station->kots->where('status', 'pending')->count(),
                'preparing' => $station->kots->where('status', 'preparing')->count(),
                'ready' => $station->kots->where('status', 'ready')->count(),
            ]
        ]);
    }

    /**
     * Generate a unique station code
     */
    private function generateStationCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $counter = 1;
        
        do {
            $code = $base . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $exists = KitchenStation::where('station_code', $code)->exists();
            $counter++;
        } while ($exists && $counter <= 99);
        
        return $code;
    }

    /**
     * Get stations for API/AJAX calls
     */
    public function getStations(Request $request): JsonResponse
    {
        $admin = Auth::guard('admin')->user();
        
        $query = KitchenStation::with('branch')
            ->when($admin && !$admin->is_super_admin, function($q) use ($admin) {
                return $q->where('organization_id', $admin->organization_id);
            })
            ->where('is_active', true);
            
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        $stations = $query->orderBy('priority_order')->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'stations' => $stations
        ]);
    }
}
