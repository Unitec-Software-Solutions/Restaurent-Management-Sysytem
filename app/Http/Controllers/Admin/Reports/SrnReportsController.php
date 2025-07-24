<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockReleaseNoteMaster;
use App\Models\StockReleaseNoteItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SrnMultiSheetExport;

class SrnReportsController extends Controller
{
    /**
     * Get organization ID for filtering
     */
    protected function getOrganizationId()
    {
        $user = Auth::guard('admin')->user();
        return $user->is_super_admin ? null : $user->organization_id;
    }

    /**
     * Apply organization filter to query
     */
    protected function applyOrganizationFilter($query, $orgId = null)
    {
        $organizationId = $orgId ?? $this->getOrganizationId();
        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }
        return $query;
    }

    /**
     * SRN Reports Main Index
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter options
        $branches = $this->getBranches($orgId);

        return view('admin.reports.srn.index', compact('branches'));
    }

    /**
     * SRN Master Reports
     */
    public function masterReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $status = $request->get('status');
        $releaseType = $request->get('release_type');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);

        // Build SRN Master query with proper relationships
        $query = StockReleaseNoteMaster::with([
            'branch',
            'items.item.category',
            'releasedByUser',
            'receivedByUser',
            'verifiedByUser',
            'createdByUser'
        ])->whereBetween('release_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($query, $orgId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($releaseType) {
            $query->where('release_type', $releaseType);
        }

        $srnMasters = $query->orderBy('release_date', 'desc')->get();

        // Process SRN Master data
        $reportData = $this->processSrnMasterData($srnMasters);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportSrnMasterReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.srn.master', compact(
            'reportData', 'branches',
            'dateFrom', 'dateTo', 'branchId', 'status', 'releaseType'
        ));
    }

    /**
     * SRN Item Reports
     */
    public function itemReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $itemId = $request->get('item_id');
        $releaseType = $request->get('release_type');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        // Build SRN Items query with proper relationships
        $query = StockReleaseNoteItem::with([
            'stockReleaseNoteMaster.branch',
            'item.category'
        ])->whereHas('stockReleaseNoteMaster', function($q) use ($dateFrom, $dateTo, $orgId) {
                $q->whereBetween('release_date', [$dateFrom, $dateTo]);
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            });

        if ($branchId) {
            $query->whereHas('stockReleaseNoteMaster', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        if ($releaseType) {
            $query->whereHas('stockReleaseNoteMaster', function($q) use ($releaseType) {
                $q->where('release_type', $releaseType);
            });
        }

        $srnItems = $query->orderBy('created_at', 'desc')->get();

        // Process item data
        $reportData = $this->processSrnItemData($srnItems);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportSrnItemReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.srn.items', compact(
            'reportData', 'branches', 'items',
            'dateFrom', 'dateTo', 'branchId', 'itemId', 'releaseType'
        ));
    }

    /**
     * Process SRN Master data with values - FIXED
     */
    private function processSrnMasterData($srnMasters)
    {
        $processedData = [];
        $totalValue = 0;
        $totalQuantity = 0;

        foreach ($srnMasters as $srn) {
            // Calculate total quantities and values using correct relationships
            $releasedQty = $srn->items->sum('release_quantity') ?? 0;
            $totalItemValue = $srn->items->sum(function($item) {
                return ($item->release_quantity ?? 0) * ($item->release_price ?? 0);
            });

            $processedData[] = [
                'srn' => $srn,
                'srn_id' => $srn->id,
                'srn_number' => $srn->srn_number,
                'release_date' => $srn->release_date,
                'branch_name' => $srn->branch->name ?? 'N/A',
                'release_type' => $srn->release_type ?? 'N/A',
                'status' => $srn->status ?? 'N/A',
                'total_amount' => $srn->total_amount ?? $totalItemValue,
                'released_quantity' => $releasedQty,
                'items_count' => $srn->items->count(),
                'notes' => $srn->notes ?? 'N/A',
                'created_by' => $srn->createdByUser->name ?? 'N/A',
                'released_by' => $srn->releasedByUser->name ?? 'N/A',
                'received_by' => $srn->receivedByUser->name ?? 'N/A',
                'verified_by' => $srn->verifiedByUser->name ?? 'N/A',
                'released_at' => $srn->released_at,
                'received_at' => $srn->received_at,
                'verified_at' => $srn->verified_at,
            ];

            $totalValue += ($srn->total_amount ?? $totalItemValue);
            $totalQuantity += $releasedQty;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_srns' => count($processedData),
                'total_value' => $totalValue,
                'total_quantity' => $totalQuantity,
                'average_srn_value' => count($processedData) > 0 ? $totalValue / count($processedData) : 0,
                'average_quantity_per_srn' => count($processedData) > 0 ? $totalQuantity / count($processedData) : 0,
            ]
        ];
    }

    /**
     * Process SRN Item data - FIXED
     */
    private function processSrnItemData($srnItems)
    {
        $processedData = [];
        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($srnItems as $item) {
            $releasePrice = $item->release_price ?? 0;
            $lineTotal = $item->line_total ?? (($item->release_quantity ?? 0) * $releasePrice);

            $processedData[] = [
                'srn_item_id' => $item->id,
                'srn_id' => $item->srn_id,
                'srn_number' => $item->stockReleaseNoteMaster->srn_number ?? 'N/A',
                'release_date' => $item->stockReleaseNoteMaster->release_date ?? null,
                'branch_name' => $item->stockReleaseNoteMaster->branch->name ?? 'N/A',
                'release_type' => $item->stockReleaseNoteMaster->release_type ?? 'N/A',
                'item_id' => $item->item_id,
                'item_name' => $item->item->name ?? $item->item_name ?? 'N/A',
                'item_code' => $item->item->item_code ?? $item->item_code ?? 'N/A',
                'item_category' => $item->item->category->name ?? 'N/A',
                'release_quantity' => $item->release_quantity ?? 0,
                'unit_of_measurement' => $item->unit_of_measurement ?? 'N/A',
                'release_price' => $releasePrice,
                'line_total' => $lineTotal,
                'batch_no' => $item->batch_no ?? 'N/A',
                'manufacturing_date' => $item->manufacturing_date ?? null,
                'expiry_date' => $item->expiry_date ?? null,
                'notes' => $item->notes ?? 'N/A',
                'metadata' => $item->metadata ?? 'N/A',
                'srn_status' => $item->stockReleaseNoteMaster->status ?? 'N/A',
            ];

            $totalQuantity += $item->release_quantity ?? 0;
            $totalValue += $lineTotal;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_items' => count($processedData),
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'average_unit_cost' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0,
                'average_line_value' => count($processedData) > 0 ? $totalValue / count($processedData) : 0,
            ]
        ];
    }

    /**
     * Get SRN release type summary for master data
     */
    private function getSrnReleaseTypeSummary($processedData)
    {
        $summary = [];
        foreach ($processedData as $item) {
            $releaseType = $item['release_type'];
            if (!isset($summary[$releaseType])) {
                $summary[$releaseType] = [
                    'count' => 0,
                    'total_value' => 0,
                ];
            }
            $summary[$releaseType]['count']++;
            $summary[$releaseType]['total_value'] += $item['total_value'];
        }
        return $summary;
    }

    /**
     * Get SRN release type summary for item data
     */
    private function getSrnItemReleaseTypeSummary($processedData)
    {
        $summary = [];
        foreach ($processedData as $item) {
            $releaseType = $item['release_type'];
            if (!isset($summary[$releaseType])) {
                $summary[$releaseType] = [
                    'count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                ];
            }
            $summary[$releaseType]['count']++;
            $summary[$releaseType]['total_quantity'] += $item['released_quantity'];
            $summary[$releaseType]['total_value'] += $item['line_total'];
        }
        return $summary;
    }

    /**
     * Export SRN Master Report
     */
    private function exportSrnMasterReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportSrnMasterPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        } elseif ($format === 'excel') {
            return Excel::download(new SrnMultiSheetExport($dateFrom, $dateTo),
                'srn-master-report-' . $dateFrom . '-to-' . $dateTo . '.xlsx');
        }
    }

    /**
     * Export SRN Item Report
     */
    private function exportSrnItemReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportSrnItemPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        }
    }

    /**
     * Export SRN Master PDF
     */
    private function exportSrnMasterPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.srn.pdf.master-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.srn.pdf.master', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('srn-master-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }

    /**
     * Export SRN Item PDF
     */
    private function exportSrnItemPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.srn.pdf.items-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.srn.pdf.items', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('srn-items-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }

    /**
     * Get branches for filter
     */
    private function getBranches($orgId)
    {
        $query = Branch::where('is_active', true);
        $this->applyOrganizationFilter($query, $orgId);
        return $query->orderBy('name')->get();
    }

    /**
     * Get items for filter
     */
    private function getItems($orgId)
    {
        $query = ItemMaster::where('is_active', true);
        $this->applyOrganizationFilter($query, $orgId);
        return $query->orderBy('name')->get();
    }
}
