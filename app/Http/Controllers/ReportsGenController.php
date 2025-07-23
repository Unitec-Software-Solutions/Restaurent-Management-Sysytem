<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrnMultiSheetExport;
use App\Exports\GtnMultiSheetExport;
use App\Exports\StockMultiSheetExport;
use App\Exports\SrnMultiSheetExport;
use App\Models\GrnMaster;
use App\Models\GoodsTransferNote;
use App\Models\ItemMaster;
use App\Models\StockReleaseNoteMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportsGenController extends Controller
{
    /**
     * Handle multi-sheet export for different report types
     */
    public function handleMultiSheetExport(Request $request, $reportType)
    {
        try {
            // Get the authenticated user
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $isSuperAdmin = $user->is_super_admin ?? false;
            $orgId = $isSuperAdmin ? null : $user->organization_id;

            // Get optional parameters from request
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $filters = $request->get('filters', []);

            Log::info('Multi-sheet export request', [
                'report_type' => $reportType,
                'user_id' => $user->id,
                'organization_id' => $orgId,
                'is_super_admin' => $isSuperAdmin,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'filters' => $filters
            ]);

            // Determine which export class to use based on report type
            switch ($reportType) {
                case 'grn_master':
                    // Pass null for grnIds to get all GRNs for the organization
                    $export = new GrnMultiSheetExport(null, $dateFrom, $dateTo, $filters);
                    $filename = 'grn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'goods_transfer_note':
                    // Pass null for gtnIds to get all GTNs for the organization
                    $export = new GtnMultiSheetExport(null, $dateFrom, $dateTo, $filters);
                    $filename = 'gtn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'stock_levels':
                    // Pass null for itemIds and branchId to get all stock for the organization
                    $branchId = $request->get('branch_id');
                    $export = new StockMultiSheetExport(null, $branchId, $dateFrom, $dateTo, $filters);
                    $filename = 'stock_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'stock_release_note_master':
                    // Pass null for srnIds to get all SRNs for the organization
                    $export = new SrnMultiSheetExport(null, $dateFrom, $dateTo, $filters);
                    $filename = 'srn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }            Log::info('Starting export download', [
                'filename' => $filename,
                'export_class' => get_class($export)
            ]);

            // Generate and download the Excel file
            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            Log::error('Multi-sheet export error', [
                'report_type' => $reportType,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test method to verify export functionality
     */
    public function testExport(Request $request)
    {
        try {
            $user = Auth::guard('admin')->user();

            if (!$user) {
                return response()->json(['error' => 'Please log in as admin'], 401);
            }

            $orgId = $user->organization_id;

            // Test SRN export as it has the most comprehensive data
            $export = new SrnMultiSheetExport(null, null, null, []);
            $sheets = $export->sheets();

            $result = [
                'success' => true,
                'message' => 'Export test completed successfully',
                'organization_id' => $orgId,
                'user_id' => $user->id,
                'sheets_count' => count($sheets),
                'sheets' => []
            ];

            foreach ($sheets as $sheet) {
                $collection = $sheet->collection();
                $result['sheets'][] = [
                    'title' => $sheet->title(),
                    'class' => get_class($sheet),
                    'records_count' => $collection->count()
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Export test error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
