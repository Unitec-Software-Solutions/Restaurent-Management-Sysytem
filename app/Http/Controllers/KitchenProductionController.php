<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionOrder;
use App\Models\ProductionSession;
use App\Models\ItemMaster;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitchenProductionController extends Controller
{
    /**
     * Display kitchen production dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Production requests summary
        $productionRequests = [
            'pending' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                ->whereIn('status', ['submitted'])
                ->count(),
            'approved' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                ->where('status', 'approved')
                ->count(),
            'in_production' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                ->where('status', 'in_production')
                ->count(),
        ];

        // Production orders summary
        $productionOrders = [
            'scheduled' => ProductionOrder::where('organization_id', $user->organization_id)
                ->where('status', 'approved')
                ->count(),
            'in_progress' => ProductionOrder::where('organization_id', $user->organization_id)
                ->where('status', 'in_progress')
                ->count(),
            'completed_today' => ProductionOrder::where('organization_id', $user->organization_id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
        ];

        // Active production sessions
        $activeSessions = ProductionSession::where('organization_id', $user->organization_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['productionOrder', 'supervisor'])
            ->latest()
            ->take(5)
            ->get();

        // Recent production requests
        $recentRequests = ProductionRequestMaster::where('organization_id', $user->organization_id)
            ->with(['branch', 'items.item'])
            ->latest()
            ->take(5)
            ->get();

        // Production items with low stock
        $lowStockItems = ItemMaster::where('organization_id', $user->organization_id)
            ->productionItems()
            ->where(function($query) {
                $query->whereNotNull('reorder_level')
                    ->whereRaw('(
                        SELECT COALESCE(SUM(CASE
                            WHEN transaction_type IN ("purchase", "production", "adjustment_increase") THEN quantity
                            WHEN transaction_type IN ("sale", "consumption", "waste", "adjustment_decrease") THEN -quantity
                            ELSE 0
                        END), 0)
                        FROM item_transactions
                        WHERE inventory_item_id = item_master.id
                    ) <= reorder_level');
            })
            ->take(10)
            ->get();

        return view('production.dashboard', compact(
            'productionRequests',
            'productionOrders',
            'activeSessions',
            'recentRequests',
            'lowStockItems'
        ));
    }

    /**
     * Quick actions for production management
     */
    public function quickActions()
    {
        return view('production.quick-actions');
    }

    /**
     * Production planning view
     */
    public function planning(Request $request)
    {
        $user = Auth::user();

        // Get date range
        $dateFrom = $request->input('date_from', now()->toDateString());
        $dateTo = $request->input('date_to', now()->addDays(7)->toDateString());

        // Get production orders for the period
        $productionOrders = ProductionOrder::where('organization_id', $user->organization_id)
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->with(['items.item', 'sessions'])
            ->orderBy('production_date')
            ->get();

        // Get production requests pending for the period
        $pendingRequests = ProductionRequestMaster::where('organization_id', $user->organization_id)
            ->where('status', 'approved')
            ->whereBetween('required_date', [$dateFrom, $dateTo])
            ->with(['branch', 'items.item'])
            ->orderBy('required_date')
            ->get();

        return view('production.planning', compact(
            'productionOrders',
            'pendingRequests',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Get production capacity and workload
     */
    public function capacity(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date', now()->toDateString());

        // Get scheduled production orders for the date
        $scheduledOrders = ProductionOrder::where('organization_id', $user->organization_id)
            ->whereDate('production_date', $date)
            ->with(['items.item', 'sessions'])
            ->get();

        // Calculate total estimated production time
        $totalEstimatedTime = 0;
        $itemBreakdown = [];

        foreach ($scheduledOrders as $order) {
            foreach ($order->items as $item) {
                $recipe = Recipe::where('production_item_id', $item->item_id)->first();
                if ($recipe) {
                    $multiplier = $item->quantity_to_produce / $recipe->yield_quantity;
                    $estimatedTime = $recipe->total_time * $multiplier;
                    $totalEstimatedTime += $estimatedTime;

                    $itemBreakdown[] = [
                        'item_name' => $item->item->name,
                        'quantity' => $item->quantity_to_produce,
                        'estimated_time' => $estimatedTime,
                        'recipe_name' => $recipe->recipe_name,
                    ];
                }
            }
        }

        // Get active sessions for the date
        $activeSessions = ProductionSession::where('organization_id', $user->organization_id)
            ->whereDate('start_time', $date)
            ->with(['productionOrder', 'supervisor'])
            ->get();

        return response()->json([
            'date' => $date,
            'scheduled_orders' => $scheduledOrders->count(),
            'total_estimated_time' => $totalEstimatedTime,
            'item_breakdown' => $itemBreakdown,
            'active_sessions' => $activeSessions->count(),
            'capacity_utilization' => min(100, ($totalEstimatedTime / 480) * 100), // Assuming 8-hour workday
        ]);
    }

    /**
     * Aggregate production requirements
     */
    public function aggregateRequirements(Request $request)
    {
        $user = Auth::user();

        // Get approved production requests
        $query = ProductionRequestMaster::where('organization_id', $user->organization_id)
            ->where('status', 'approved')
            ->with(['items.item', 'branch']);

        if ($request->filled('required_date_from')) {
            $query->whereDate('required_date', '>=', $request->required_date_from);
        }

        if ($request->filled('required_date_to')) {
            $query->whereDate('required_date', '<=', $request->required_date_to);
        }

        $requests = $query->get();

        // Aggregate by item
        $aggregated = [];
        foreach ($requests as $request) {
            foreach ($request->items as $item) {
                $itemId = $item->item_id;
                if (!isset($aggregated[$itemId])) {
                    $aggregated[$itemId] = [
                        'item' => $item->item,
                        'total_requested' => 0,
                        'total_approved' => 0,
                        'total_produced' => 0,
                        'branches' => [],
                        'requests' => [],
                    ];
                }

                $aggregated[$itemId]['total_requested'] += $item->quantity_requested;
                $aggregated[$itemId]['total_approved'] += $item->quantity_approved;
                $aggregated[$itemId]['total_produced'] += $item->quantity_produced;
                $aggregated[$itemId]['branches'][$request->branch->name] =
                    ($aggregated[$itemId]['branches'][$request->branch->name] ?? 0) + $item->quantity_approved;
                $aggregated[$itemId]['requests'][] = $request;
            }
        }

        return view('production.aggregate-requirements', compact('aggregated', 'requests'));
    }

    /**
     * Get production reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', 'week');

        switch ($period) {
            case 'today':
                $dateFrom = now()->startOfDay();
                $dateTo = now()->endOfDay();
                break;
            case 'week':
                $dateFrom = now()->startOfWeek();
                $dateTo = now()->endOfWeek();
                break;
            case 'month':
                $dateFrom = now()->startOfMonth();
                $dateTo = now()->endOfMonth();
                break;
            default:
                $dateFrom = $request->input('date_from', now()->startOfWeek());
                $dateTo = $request->input('date_to', now()->endOfWeek());
        }

        // Production statistics
        $stats = [
            'total_requests' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'completed_orders' => ProductionOrder::where('organization_id', $user->organization_id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$dateFrom, $dateTo])
                ->count(),
            'total_sessions' => ProductionSession::where('organization_id', $user->organization_id)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'efficiency' => $this->calculateProductionEfficiency($user->organization_id, $dateFrom, $dateTo),
        ];

        return view('production.reports', compact('stats', 'period', 'dateFrom', 'dateTo'));
    }

    /**
     * Calculate production efficiency
     */
    private function calculateProductionEfficiency($organizationId, $dateFrom, $dateTo)
    {
        $sessions = ProductionSession::where('organization_id', $organizationId)
            ->where('status', 'completed')
            ->whereBetween('end_time', [$dateFrom, $dateTo])
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalEstimated = 0;
        $totalActual = 0;

        foreach ($sessions as $session) {
            $estimated = $session->productionOrder->getEstimatedDuration();
            $actual = $session->getDuration();

            if ($estimated && $actual) {
                $totalEstimated += $estimated;
                $totalActual += $actual;
            }
        }

        return $totalEstimated > 0 ? min(100, ($totalEstimated / $totalActual) * 100) : 0;
    }
}
