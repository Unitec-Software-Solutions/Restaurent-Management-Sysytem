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

            Log::info('Multi-sheet export request', [
                'report_type' => $reportType,
                'user_id' => $user->id,
                'organization_id' => $orgId,
                'is_super_admin' => $isSuperAdmin
            ]);

            // Determine which export class to use based on report type
            switch ($reportType) {
                case 'grn_master':
                    $export = new GrnMultiSheetExport($orgId);
                    $filename = 'grn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'goods_transfer_note':
                    $export = new GtnMultiSheetExport($orgId);
                    $filename = 'gtn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'stock_levels':
                    $export = new StockMultiSheetExport($orgId);
                    $filename = 'stock_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                case 'stock_release_note_master':
                    $export = new SrnMultiSheetExport($orgId);
                    $filename = 'srn_report_' . now()->format('Y_m_d_His') . '.xlsx';
                    break;

                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }

            Log::info('Starting export download', [
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
            $export = new SrnMultiSheetExport($orgId);
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
