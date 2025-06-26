<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionRequestMaster;
use App\Models\ProductionOrder;
use App\Models\ProductionSession;
use App\Models\ItemMaster;
use App\Models\Recipe;
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

        return ProductionRequestMaster::with(['branch', 'items.item', 'createdBy'])
            ->where('organization_id', $user->organization_id)
            ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
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

        return ProductionOrder::with(['items.item', 'sessions', 'createdBy'])
            ->where('organization_id', $user->organization_id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('production_date', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Get today's production sessions
     */
    private function getTodayProductionSessions()
    {
        $user = Auth::user();

        return ProductionSession::with(['productionOrder.items.item', 'supervisor'])
            ->where('organization_id', $user->organization_id)
            ->whereDate('created_at', today())
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

        return [
            'requests' => [
                'pending' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                    ->where('status', ProductionRequestMaster::STATUS_SUBMITTED)
                    ->count(),
                'approved_today' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                    ->where('status', ProductionRequestMaster::STATUS_APPROVED)
                    ->whereDate('approved_at', $today)
                    ->count(),
                'weekly_total' => ProductionRequestMaster::where('organization_id', $user->organization_id)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
            ],
            'orders' => [
                'active' => ProductionOrder::where('organization_id', $user->organization_id)
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->count(),
                'completed_today' => ProductionOrder::where('organization_id', $user->organization_id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->count(),
                'scheduled_tomorrow' => ProductionOrder::where('organization_id', $user->organization_id)
                    ->where('status', 'approved')
                    ->whereDate('production_date', $today->copy()->addDay())
                    ->count(),
            ],
            'sessions' => [
                'active' => ProductionSession::where('organization_id', $user->organization_id)
                    ->whereIn('status', ['in_progress'])
                    ->count(),
                'completed_today' => ProductionSession::where('organization_id', $user->organization_id)
                    ->where('status', 'completed')
                    ->whereDate('end_time', $today)
                    ->count(),
                'monthly_total' => ProductionSession::where('organization_id', $user->organization_id)
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

        return ItemMaster::select('item_master.*')
            ->join('item_categories', 'item_master.item_category_id', '=', 'item_categories.id')
            ->where('item_master.organization_id', $user->organization_id)
            ->where('item_categories.name', 'Production Items')
            ->whereNotNull('item_master.reorder_level')
            ->whereRaw('(
                SELECT COALESCE(SUM(CASE
                    WHEN transaction_type IN (?, ?, ?) THEN quantity
                    WHEN transaction_type IN (?, ?, ?, ?) THEN -quantity
                    ELSE 0
                END), 0)
                FROM item_transactions
                WHERE inventory_item_id = item_master.id
            ) <= item_master.reorder_level', [
                'purchase', 'production_in', 'adjustment_increase',
                'sale', 'consumption', 'waste', 'adjustment_decrease'
            ])
            ->with(['category'])
            ->orderBy('reorder_level', 'desc')
            ->limit(10)
            ->get();
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
                $recipe = Recipe::where('production_item_id', $item->item_id)->first();
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
