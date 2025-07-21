<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function salesReport()
    {
        return view('admin.reports.sales.index');
    }

    public function inventoryReport()
    {
        return view('admin.reports.inventory.index');
    }

    public function inventoryGrn(Request $request)
    {
        // Get user and check super admin
        $user = Auth::user();
        $isSuperAdmin = $user && isset($user->is_super_admin) && $user->is_super_admin;

        // Filters
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $dateFrom = $request->input('start_date');
        $dateTo = $request->input('end_date');
        $export = $request->input('export');

        $query = DB::table('grn_master');

        // Only filter by branch if not super admin and branch_id is present and column exists
        if (!$isSuperAdmin && $branchId && Schema::hasColumn('grn_master', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Date range filter
        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        // Metrics
        $grnCount = (clone $query)->count();
        $totalAmount = (clone $query)->sum('total_amount');
        $pendingCount = (clone $query)->where('status', 'Pending')->count();
        $verifiedCount = (clone $query)->where('status', 'Verified')->count();
        $rejectedCount = (clone $query)->where('status', 'Rejected')->count();

        // Payment status breakdown
        $paymentStatusCounts = (clone $query)
            ->select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status');

        // Recent GRNs
        $recentGrns = (clone $query)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Export logic
        if ($export === 'csv' || $export === 'excel') {
            $columns = ['grn_number', 'received_date', 'total_amount', 'status', 'payment_status'];
            $data = (clone $query)->select($columns)->get();
            $filename = 'grn_report_' . now()->format('Y-m-d_H-i-s') . ($export === 'csv' ? '.csv' : '.xlsx');
            $headers = [
                'Content-Type' => $export === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            $callback = function () use ($data, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                foreach ($data as $row) {
                    fputcsv($file, array_map(fn($col) => $row->$col, $columns));
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        // Branches for filter dropdown (avoid org_id if not present)
        $branches = [];
        if (Schema::hasTable('branches')) {
            $branchQuery = DB::table('branches')->select('id', 'name');
            $branches = $branchQuery->get();
        }

        // Status options
        $statusOptions = [
            'Pending' => 'Pending',
            'Verified' => 'Verified',
            'Rejected' => 'Rejected',
        ];

        return view('admin.reports.inventory.grn.dashboard', [
            'grnCount' => $grnCount,
            'totalAmount' => $totalAmount,
            'pendingCount' => $pendingCount,
            'verifiedCount' => $verifiedCount,
            'rejectedCount' => $rejectedCount,
            'paymentStatusCounts' => $paymentStatusCounts,
            'recentGrns' => $recentGrns,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'statusOptions' => $statusOptions,
            'selectedStatus' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function inventoryGtn()
    {
        return view('admin.reports.inventory.gtn.index');
    }

    public function inventorySrn()
    {
        return view('admin.reports.inventory.srn.index');
    }

    public function inventoryStock()
    {
        return view('admin.reports.inventory.stock.index');
    }


}
