<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrnMultiSheetExport;
use App\Exports\GtnMultiSheetExport;
use App\Exports\StockMultiSheetExport;
use App\Exports\SrnMultiSheetExport;
use App\Models\GrnMaster;
use App\Models\GoodsTransferNote;
use App\Models\ItemMaster;
use App\Models\StockReleaseNoteMaster;

class ReportsGenController extends Controller
{
    /**
     * Generate a report based on input columns, filters, and date range.
     * Supports output for visualizations and export (Excel/CSV).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generate(Request $request)
    {
        try {
            $table = $request->input('table');
            $columns = $request->input('columns', []);
            $filters = $request->input('filters', []);
            $dateColumn = $request->input('date_column', 'created_at');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $export = $request->input('export'); // 'csv' or 'excel'

            if (!$table || empty($columns)) {
                return response()->json([
                    'error' => 'Table and columns are required.'
                ], 422);
            }

            // Validate table name to prevent SQL injection
            $allowedTables = [
                'grn_master', 'gtn_master', 'stock_release_note_master',
                'item_transactions', 'item_master', 'branches', 'suppliers'
            ];

            if (!in_array($table, $allowedTables)) {
                return response()->json([
                    'error' => 'Invalid table specified.'
                ], 422);
            }

            $query = DB::table($table)->select($columns);

            // Apply filters
            foreach ($filters as $col => $val) {
                if (is_array($val)) {
                    $query->whereIn($col, $val);
                } else {
                    $query->where($col, $val);
                }
            }

            // Apply date range
            if ($dateFrom && $dateTo) {
                $query->whereBetween($dateColumn, [$dateFrom, $dateTo]);
            }

            // Fetch data
            $data = $query->get();

            // Metrics for visualization
            $metrics = [
                'total' => $data->count(),
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ]
            ];

            // Add table-specific metrics
            switch ($table) {
                case 'grn_master':
                    $metrics['total_amount'] = $data->sum('total_amount');
                    break;
                case 'goods_transfer_notes':
                    $metrics['total_items'] = $data->count();
                    break;
                case 'stock_release_note_master':
                    $metrics['total_releases'] = $data->count();
                    break;
            }

            // Export if requested
            if ($export === 'csv' || $export === 'excel') {
                return $this->handleMultiSheetExport($request, $table, $filters, $dateFrom, $dateTo);
            }

            // Return data and metrics for visualization
            return response()->json([
                'success' => true,
                'data' => $data,
                'metrics' => $metrics,
            ]);

        } catch (\Exception $e) {
            Log::error('Report generation failed', [
                'error' => $e->getMessage(),
                'table' => $request->input('table'),
                'columns' => $request->input('columns')
            ]);

            return response()->json([
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle multi-sheet exports for GRN, GTN, and Stock reports
     *
     * @param Request $request
     * @param string $table
     * @param array $filters
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function handleMultiSheetExport(Request $request, string $table, array $filters, $dateFrom = null, $dateTo = null)
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user->is_super_admin ?? false;
        $orgId = $isSuperAdmin ? null : $user->organization_id;

        // Get specific IDs if provided in filters
        $specificIds = $filters['ids'] ?? null;
        $branchId = $filters['branch_id'] ?? null;

        $filename = 'report_' . $table . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        switch ($table) {
            case 'grn_master':
                // Filter GRN IDs based on organization if not super admin
                if ($specificIds && !$isSuperAdmin && $orgId) {
                    $validIds = GrnMaster::whereIn('grn_id', $specificIds)
                        ->where('organization_id', $orgId)
                        ->pluck('grn_id')
                        ->toArray();
                    $specificIds = $validIds;
                }

                return Excel::download(
                    new GrnMultiSheetExport($specificIds, $dateFrom, $dateTo, $filters),
                    $filename
                );

            case 'gtn_master':
                // Filter GTN IDs based on organization if not super admin
                if ($specificIds && !$isSuperAdmin && $orgId) {
                    $validIds = GoodsTransferNote::whereIn('gtn_id', $specificIds)
                        ->where('organization_id', $orgId)
                        ->pluck('gtn_id')
                        ->toArray();
                    $specificIds = $validIds;
                }

                return Excel::download(
                    new GtnMultiSheetExport($specificIds, $dateFrom, $dateTo, $filters),
                    $filename
                );

            case 'item_master':
            case 'item_transactions':
                // Filter Item IDs based on organization if not super admin
                if ($specificIds && !$isSuperAdmin && $orgId) {
                    $validIds = ItemMaster::whereIn('id', $specificIds)
                        ->where('organization_id', $orgId)
                        ->pluck('id')
                        ->toArray();
                    $specificIds = $validIds;
                }

                return Excel::download(
                    new StockMultiSheetExport($specificIds, $branchId, $dateFrom, $dateTo, $filters),
                    $filename
                );

            case 'stock_release_note_master':
                // Filter SRN IDs based on organization if not super admin
                if ($specificIds && !$isSuperAdmin && $orgId) {
                    $validIds = StockReleaseNoteMaster::whereIn('id', $specificIds)
                        ->where('organization_id', $orgId)
                        ->pluck('id')
                        ->toArray();
                    $specificIds = $validIds;
                }

                return Excel::download(
                    new SrnMultiSheetExport($specificIds, $dateFrom, $dateTo, $filters),
                    $filename
                );

            default:
                // Fallback to simple CSV export for other tables
                return $this->handleSimpleExport($request, $table, $filters, $dateFrom, $dateTo);
        }
    }

    /**
     * Handle simple CSV export for tables that don't require multi-sheet functionality
     *
     * @param Request $request
     * @param string $table
     * @param array $filters
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return \Illuminate\Http\StreamedResponse
     */
    protected function handleSimpleExport(Request $request, string $table, array $filters, $dateFrom = null, $dateTo = null)
    {
        $columns = $request->input('columns', []);
        $query = DB::table($table)->select($columns);

        // Apply filters
        foreach ($filters as $col => $val) {
            if ($col === 'ids' || $col === 'branch_id') continue; // Skip special filters
            if (is_array($val)) {
                $query->whereIn($col, $val);
            } else {
                $query->where($col, $val);
            }
        }

        // Apply date range
        if ($dateFrom && $dateTo) {
            $dateColumn = $request->input('date_column', 'created_at');
            $query->whereBetween($dateColumn, [$dateFrom, $dateTo]);
        }

        $data = $query->get();
        $filename = 'report_' . $table . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $col) {
                    $rowData[] = $row->$col ?? '';
                }
                fputcsv($file, $rowData);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
