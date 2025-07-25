<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionOrder;
use App\Models\ProductionSession;
use App\Models\ItemMaster;
use App\Models\ProductionRecipe;
use App\Models\Branch;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionController extends Controller
{
    /**
     * Display production dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get pending production requests
        $pendingRequests = $this->getPendingProductionRequests();

        // Get active production orders
        $activeOrders = $this->getActiveProductionOrders();

        // Get today's production sessions
        $todaySessions = $this->getTodayProductionSessions();

        // Get production statistics
        $stats = $this->getProductionStatistics();

        // Get low stock production items
        $lowStockItems = $this->getLowStockProductionItems();

        // Get recent completed orders
        $recentCompletedOrders = $this->getRecentCompletedOrders();

        // Get upcoming production schedule
        $upcomingSchedule = $this->getUpcomingProductionSchedule();

        return view('admin.production.dashboard', compact(
            'pendingRequests',
            'activeOrders',
            'todaySessions',
            'stats',
            'lowStockItems',
            'recentCompletedOrders',
            'upcomingSchedule'
        ));
    }

    /**
     * Get pending production requests from all branches
     */
    private function getPendingProductionRequests()
    {
        $user = Auth::user();

        $query = ProductionRequestMaster::with(['branch', 'items.item', 'createdBy']);

        // Super admins can see all requests, others filter by organization
        if (!$user->is_super_admin) {
            $query->where('organization_id', $user->organization_id);
        }

        return $query->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
            ->orderBy('required_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Get active production orders
     */
    private function getActiveProductionOrders()
    {
        $user = Auth::user();

        $query = ProductionOrder::with(['items.item', 'ProductionRequestMaster.branch']);

        // Super admins can see all orders, others filter by organization
        if (!$user->is_super_admin) {
            $query->where('organization_id', $user->organization_id);
        }

        return $query->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get today's production sessions
     */
    private function getTodayProductionSessions()
    {
        $user = Auth::user();

        $query = ProductionSession::with(['productionOrder.items.item', 'supervisor']);

        // Super admins can see all sessions, others filter by organization
        if (!$user->is_super_admin) {
            $query->where('organization_id', $user->organization_id);
        }

        return $query->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get production statistics
     */
    private function getProductionStatistics()
    {
        $user = Auth::user();
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $organizationFilter = function ($query) use ($user) {
            if (!$user->is_super_admin) {
                $query->where('organization_id', $user->organization_id);
            }
        };

        return [
            'requests' => [
                'pending' => ProductionRequestMaster::where($organizationFilter)
                    ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
                    ->count(),
                'approved_today' => ProductionRequestMaster::where($organizationFilter)
                    ->where('status', ProductionRequestMaster::STATUS_APPROVED)
                    ->whereDate('approved_at', $today)
                    ->count(),
                'weekly_total' => ProductionRequestMaster::where($organizationFilter)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
            ],
            'orders' => [
                'active' => ProductionOrder::where($organizationFilter)
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->count(),
                'completed_today' => ProductionOrder::where($organizationFilter)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->count(),
                'scheduled_tomorrow' => ProductionOrder::where($organizationFilter)
                    ->where('status', 'approved')
                    ->whereDate('production_date', $today->copy()->addDay())
                    ->count(),
            ],
            'sessions' => [
                'active' => ProductionSession::where($organizationFilter)
                    ->whereIn('status', ['in_progress'])
                    ->count(),
                'completed_today' => ProductionSession::where($organizationFilter)
                    ->where('status', 'completed')
                    ->whereDate('end_time', $today)
                    ->count(),
                'monthly_total' => ProductionSession::where($organizationFilter)
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
            ],
            'efficiency' => [
                'current_week' => $this->calculateWeeklyEfficiency(),
                'current_month' => $this->calculateMonthlyEfficiency(),
            ]
        ];
    }

    /**
     * Get low stock production items - FIXED SQL QUERY
     */
    private function getLowStockProductionItems()
    {
        $user = Auth::user();

        // Get HQ branch for stock calculations
        $hqBranch = \App\Models\Branch::where('organization_id', $user->organization_id)
            ->where('is_head_office', true)
            ->first();

        if (!$hqBranch) {
            return collect();
        }

        return ItemMaster::select('item_master.*')
            ->join('item_categories', 'item_master.item_category_id', '=', 'item_categories.id')
            ->where('item_master.organization_id', $user->organization_id)
            ->where('item_categories.name', 'Production Items')
            ->whereNotNull('item_master.reorder_level')
            ->with(['category'])
            ->get()
            ->filter(function ($item) use ($hqBranch) {
                $currentStock = ItemTransaction::stockOnHand($item->id, $hqBranch->id);
                return $currentStock <= $item->reorder_level;
            })
            ->take(10);
    }

    /**
     * Get recent completed orders
     */
    private function getRecentCompletedOrders()
    {
        $user = Auth::user();

        return ProductionOrder::with(['items.item', 'sessions'])
            ->where('organization_id', $user->organization_id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get upcoming production schedule
     */
    private function getUpcomingProductionSchedule()
    {
        $user = Auth::user();
        $nextWeek = now()->addDays(7);

        return ProductionOrder::with(['items.item', 'sessions'])
            ->where('organization_id', $user->organization_id)
            ->where('status', 'approved')
            ->whereBetween('production_date', [now(), $nextWeek])
            ->orderBy('production_date', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Calculate weekly production efficiency
     */
    private function calculateWeeklyEfficiency()
    {
        $user = Auth::user();
        $weekStart = now()->startOfWeek();

        $sessions = ProductionSession::where('organization_id', $user->organization_id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $weekStart)
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalPlanned = 0;
        $totalActual = 0;

        foreach ($sessions as $session) {
            $estimatedTime = $session->estimated_duration ?? 480; // Default 8 hours in minutes
            $actualTime = $session->actual_duration ?? $estimatedTime;

            $totalPlanned += $estimatedTime;
            $totalActual += $actualTime;
        }

        return $totalActual > 0 ? round(($totalPlanned / $totalActual) * 100, 1) : 0;
    }

    /**
     * Calculate monthly production efficiency
     */
    private function calculateMonthlyEfficiency()
    {
        $user = Auth::user();
        $monthStart = now()->startOfMonth();

        $sessions = ProductionSession::where('organization_id', $user->organization_id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalPlanned = 0;
        $totalActual = 0;

        foreach ($sessions as $session) {
            $estimatedTime = $session->estimated_duration ?? 480;
            $actualTime = $session->actual_duration ?? $estimatedTime;

            $totalPlanned += $estimatedTime;
            $totalActual += $actualTime;
        }

        return $totalActual > 0 ? round(($totalPlanned / $totalActual) * 100, 1) : 0;
    }

    /**
     * Get production capacity for a specific date
     */
    public function getProductionCapacity(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date', now()->toDateString());

        $scheduledOrders = ProductionOrder::where('organization_id', $user->organization_id)
            ->whereDate('production_date', $date)
            ->with(['items.item', 'sessions'])
            ->get();

        $totalEstimatedTime = 0;
        $itemBreakdown = [];

        foreach ($scheduledOrders as $order) {
            foreach ($order->items as $item) {
                $recipe = ProductionRecipe::where('production_item_id', $item->item_id)->first();
                if ($recipe) {
                    $multiplier = $item->quantity / $recipe->yield_quantity;
                    $estimatedTime = $recipe->total_time * $multiplier;
                    $totalEstimatedTime += $estimatedTime;

                    $itemBreakdown[] = [
                        'item_name' => $item->item->name,
                        'quantity' => $item->quantity,
                        'estimated_time' => $estimatedTime,
                        'recipe_name' => $recipe->recipe_name,
                    ];
                }
            }
        }

        $activeSessions = ProductionSession::where('organization_id', $user->organization_id)
            ->whereDate('start_time', $date)
            ->count();

        return response()->json([
            'date' => $date,
            'scheduled_orders' => $scheduledOrders->count(),
            'total_estimated_time' => $totalEstimatedTime,
            'item_breakdown' => $itemBreakdown,
            'active_sessions' => $activeSessions,
            'capacity_utilization' => min(100, ($totalEstimatedTime / 480) * 100), // 8-hour workday
        ]);
    }

    /**
     * Get production alerts
     */
    public function getProductionAlerts()
    {
        $user = Auth::user();
        $alerts = [];

        // Low stock alerts
        $lowStockItems = $this->getLowStockProductionItems();
        foreach ($lowStockItems as $item) {
            $alerts[] = [
                'type' => 'low_stock',
                'priority' => 'high',
                'message' => "Low stock alert: {$item->name}",
                'item_id' => $item->id,
                'created_at' => now()
            ];
        }

        // Overdue production requests
        $overdueRequests = ProductionRequestMaster::where('organization_id', $user->organization_id)
            ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
            ->whereDate('required_date', '<', now())
            ->count();

        if ($overdueRequests > 0) {
            $alerts[] = [
                'type' => 'overdue_requests',
                'priority' => 'urgent',
                'message' => "{$overdueRequests} production requests are overdue",
                'count' => $overdueRequests,
                'created_at' => now()
            ];
        }

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Get production summary for dashboard widgets
     */
    public function getProductionSummary(Request $request)
    {
        $period = $request->input('period', 'today');
        $dateRange = $this->getDateRangeForPeriod($period);

        $stats = $this->getProductionStatistics();

        return response()->json([
            'period' => $period,
            'stats' => $stats,
            'date_range' => $dateRange
        ]);
    }

    /**
     * Get date range for specified period
     */
    private function getDateRangeForPeriod($period)
    {
        switch ($period) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
            default:
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
        }
    }
}
