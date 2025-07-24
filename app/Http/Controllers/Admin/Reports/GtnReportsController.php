<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\GoodsTransferNote;
use App\Models\GoodsTransferItem;
use App\Models\Branch;
use App\Models\ItemMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GtnMultiSheetExport;

class GtnReportsController extends Controller
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
     * GTN Reports Main Index
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter options
        $branches = $this->getBranches($orgId);

        return view('admin.reports.gtn.index', compact('branches'));
    }

    /**
     * GTN Master Reports
     */
    public function masterReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $fromBranchId = $request->get('from_branch_id');
        $toBranchId = $request->get('to_branch_id');
        $status = $request->get('status');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);

        // Build GTN Master query with proper relationships
        $query = GoodsTransferNote::with([
            'fromBranch',
            'toBranch',
            'items.item.category',
            'createdByUser',
            'receivedByUser',
            'inspectedByUser'
        ])->whereBetween('transfer_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($query, $orgId);

        if ($fromBranchId) {
            $query->where('from_branch_id', $fromBranchId);
        }

        if ($toBranchId) {
            $query->where('to_branch_id', $toBranchId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $gtnMasters = $query->orderBy('transfer_date', 'desc')->get();

        // Process GTN Master data
        $reportData = $this->processGtnMasterData($gtnMasters);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportGtnMasterReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.gtn.master', compact(
            'reportData', 'branches',
            'dateFrom', 'dateTo', 'fromBranchId', 'toBranchId', 'status'
        ));
    }

    /**
     * GTN Item Reports
     */
    public function itemReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $fromBranchId = $request->get('from_branch_id');
        $toBranchId = $request->get('to_branch_id');
        $itemId = $request->get('item_id');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        // Build GTN Items query with proper relationships
        $query = GoodsTransferItem::with([
            'gtn.fromBranch',
            'gtn.toBranch',
            'gtn.createdByUser',
            'item.category'
        ])->whereHas('gtn', function($q) use ($dateFrom, $dateTo, $orgId) {
                $q->whereBetween('transfer_date', [$dateFrom, $dateTo]);
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            });

        if ($fromBranchId) {
            $query->whereHas('gtn', function($q) use ($fromBranchId) {
                $q->where('from_branch_id', $fromBranchId);
            });
        }

        if ($toBranchId) {
            $query->whereHas('gtn', function($q) use ($toBranchId) {
                $q->where('to_branch_id', $toBranchId);
            });
        }

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $gtnItems = $query->orderBy('created_at', 'desc')->get();

        // Process item data
        $reportData = $this->processGtnItemData($gtnItems);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportGtnItemReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.gtn.items', compact(
            'reportData', 'branches', 'items',
            'dateFrom', 'dateTo', 'fromBranchId', 'toBranchId', 'itemId'
        ));
    }

    /**
     * Process GTN Master data with values and discounts - FIXED
     */
    private function processGtnMasterData($gtnMasters)
    {
        $processedData = [];
        $totalValue = 0;
        $totalTransferred = 0;
        $totalReceived = 0;

        foreach ($gtnMasters as $gtn) {
            // Calculate transfer quantities and values using correct relationship
            $transferredQty = $gtn->items->sum('transfer_quantity') ?? 0;
            $receivedQty = $gtn->items->sum('received_quantity') ?? 0;
            $damagedQty = $gtn->items->sum('damaged_quantity') ?? 0;
            $acceptedQty = $gtn->items->sum('quantity_accepted') ?? 0;
            $rejectedQty = $gtn->items->sum('quantity_rejected') ?? 0;

            // Calculate total value using transfer_price from items
            $totalItemValue = $gtn->items->sum('line_total') ?? 0;

            $processedData[] = [
                'gtn' => $gtn,
                'gtn_id' => $gtn->gtn_id,
                'gtn_number' => $gtn->gtn_number,
                'transfer_date' => $gtn->transfer_date,
                'from_branch' => $gtn->fromBranch->name ?? 'N/A',
                'to_branch' => $gtn->toBranch->name ?? 'N/A',
                'status' => $gtn->status,
                'origin_status' => $gtn->origin_status ?? 'N/A',
                'receiver_status' => $gtn->receiver_status ?? 'N/A',
                'total_value' => $gtn->total_value ?? $totalItemValue,
                'transferred_qty' => $transferredQty,
                'received_qty' => $receivedQty,
                'accepted_qty' => $acceptedQty,
                'rejected_qty' => $rejectedQty,
                'damaged_qty' => $damagedQty,
                'loss_percentage' => $transferredQty > 0 ? (($transferredQty - $receivedQty) / $transferredQty) * 100 : 0,
                'items_count' => $gtn->items->count(),
                'created_by' => $gtn->createdByUser->name ?? 'N/A',
                'received_by' => $gtn->receivedByUser->name ?? 'N/A',
                'inspected_by' => $gtn->inspectedByUser->name ?? 'N/A',
                'confirmed_at' => $gtn->confirmed_at,
                'delivered_at' => $gtn->delivered_at,
                'received_at' => $gtn->received_at,
                'created_at' => $gtn->created_at,
                'notes' => $gtn->notes ?? 'N/A',
            ];

            $totalValue += ($gtn->total_value ?? $totalItemValue);
            $totalTransferred += $transferredQty;
            $totalReceived += $receivedQty;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_gtns' => count($processedData),
                'total_value' => $totalValue,
                'total_transferred_qty' => $totalTransferred,
                'total_received_qty' => $totalReceived,
                'total_loss_qty' => $totalTransferred - $totalReceived,
                'overall_loss_percentage' => $totalTransferred > 0 ? (($totalTransferred - $totalReceived) / $totalTransferred) * 100 : 0,
                'average_value_per_gtn' => count($processedData) > 0 ? $totalValue / count($processedData) : 0,
            ]
        ];
    }

    /**
     * Process GTN Item data - FIXED
     */
    private function processGtnItemData($gtnItems)
    {
        $processedData = [];
        $totalTransferred = 0;
        $totalReceived = 0;
        $totalValue = 0;

        foreach ($gtnItems as $item) {
            $transferPrice = $item->transfer_price ?? 0;
            $lineTotal = $item->line_total ?? ($item->transfer_quantity * $transferPrice);
            $lossQty = ($item->transfer_quantity ?? 0) - ($item->received_quantity ?? 0);
            $lossPercentage = ($item->transfer_quantity ?? 0) > 0 ? ($lossQty / $item->transfer_quantity) * 100 : 0;

            $processedData[] = [
                'gtn_item_id' => $item->gtn_item_id,
                'gtn_id' => $item->gtn_id,
                'gtn_number' => $item->gtn->gtn_number ?? 'N/A',
                'transfer_date' => $item->gtn->transfer_date ?? null,
                'from_branch' => $item->gtn->fromBranch->name ?? 'N/A',
                'to_branch' => $item->gtn->toBranch->name ?? 'N/A',
                'item_id' => $item->item_id,
                'item_name' => $item->item->name ?? $item->item_name ?? 'N/A',
                'item_code' => $item->item->item_code ?? $item->item_code ?? 'N/A',
                'item_category' => $item->item->category->name ?? 'N/A',
                'transfer_quantity' => $item->transfer_quantity ?? 0,
                'received_quantity' => $item->received_quantity ?? 0,
                'damaged_quantity' => $item->damaged_quantity ?? 0,
                'quantity_accepted' => $item->quantity_accepted ?? 0,
                'quantity_rejected' => $item->quantity_rejected ?? 0,
                'loss_quantity' => $lossQty,
                'loss_percentage' => $lossPercentage,
                'transfer_price' => $transferPrice,
                'line_total' => $lineTotal,
                'batch_no' => $item->batch_no ?? 'N/A',
                'expiry_date' => $item->expiry_date ?? null,
                'item_status' => $item->item_status ?? 'N/A',
                'quality_notes' => is_array($item->quality_notes) ? implode(', ', $item->quality_notes) : ($item->quality_notes ?? 'N/A'),
                'item_rejection_reason' => $item->item_rejection_reason ?? 'N/A',
                'gtn_status' => $item->gtn->status ?? 'N/A',
                'notes' => $item->notes ?? 'N/A',
            ];

            $totalTransferred += $item->transfer_quantity ?? 0;
            $totalReceived += $item->received_quantity ?? 0;
            $totalValue += $lineTotal;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_items' => count($processedData),
                'total_transferred_qty' => $totalTransferred,
                'total_received_qty' => $totalReceived,
                'total_loss_qty' => $totalTransferred - $totalReceived,
                'total_value' => $totalValue,
                'average_loss_percentage' => $totalTransferred > 0 ? (($totalTransferred - $totalReceived) / $totalTransferred) * 100 : 0,
                'average_transfer_price' => $totalTransferred > 0 ? $totalValue / $totalTransferred : 0,
            ]
        ];
    }

    /**
     * Export GTN Master Report
     */
    private function exportGtnMasterReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportGtnMasterPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        } elseif ($format === 'excel') {
            return Excel::download(new GtnMultiSheetExport($dateFrom, $dateTo),
                'gtn-master-report-' . $dateFrom . '-to-' . $dateTo . '.xlsx');
        }
    }

    /**
     * Export GTN Item Report
     */
    private function exportGtnItemReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportGtnItemPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        }
    }

    /**
     * Export GTN Master PDF
     */
    private function exportGtnMasterPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.gtn.pdf.master-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.gtn.pdf.master', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('gtn-master-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }

    /**
     * Export GTN Item PDF
     */
    private function exportGtnItemPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.gtn.pdf.items-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.gtn.pdf.items', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('gtn-items-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
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
