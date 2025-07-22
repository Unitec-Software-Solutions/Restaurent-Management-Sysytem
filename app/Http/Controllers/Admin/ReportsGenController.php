<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                'grn_master', 'goods_transfer_notes', 'stock_release_note_master',
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
                $filename = 'report_' . $table . '_' . now()->format('Y-m-d_H-i-s') . ($export === 'csv' ? '.csv' : '.xlsx');
                $headers = [
                    'Content-Type' => $export === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
}
