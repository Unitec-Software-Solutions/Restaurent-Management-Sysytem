<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class reportsgencontroller extends Controller
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

        // Metrics for visualization (example: count, sum, group by)
        $metrics = [
            'total' => $data->count(),
        ];
        // Optionally add more metrics as needed

        // Export if requested
        if ($export === 'csv' || $export === 'excel') {
            $filename = 'report_' . now()->format('Y-m-d_H-i-s') . ($export === 'csv' ? '.csv' : '.xlsx');
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

        // Return data and metrics for visualization
        return response()->json([
            'data' => $data,
            'metrics' => $metrics,
        ]);
    }
}
