<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\GrnMaster;
use App\Models\GrnItem;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\ItemMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrnMultiSheetExport;

class GrnReportsController extends Controller
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
     * GRN Reports Main Index
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter options
        $branches = $this->getBranches($orgId);
        $suppliers = $this->getSuppliers($orgId);

        return view('admin.reports.grn.index', compact('branches', 'suppliers'));
    }

    /**
     * GRN Master Reports
     */
    public function masterReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $supplierId = $request->get('supplier_id');
        $status = $request->get('status');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $suppliers = $this->getSuppliers($orgId);

        // Build GRN Master query with all necessary relationships
        $query = GrnMaster::with([
            'branch',
            'supplier',
            'items.item',
            'payments',
            'receivedByUser',
            'verifiedByUser',
            'createdByUser',
            'purchaseOrder'
        ])->whereBetween('received_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($query, $orgId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $grnMasters = $query->orderBy('received_date', 'desc')->get();

        // Calculate totals and add discounts
        $reportData = $this->processGrnMasterData($grnMasters);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportGrnMasterReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.grn.master', compact(
            'reportData', 'branches', 'suppliers',
            'dateFrom', 'dateTo', 'branchId', 'supplierId', 'status'
        ));
    }

    /**
     * GRN Item Reports
     */
    public function itemReports(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $supplierId = $request->get('supplier_id');
        $itemId = $request->get('item_id');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $suppliers = $this->getSuppliers($orgId);
        $items = $this->getItems($orgId);

        // Build GRN Items query with proper relationships
        $query = GrnItem::with(['grn.branch', 'grn.supplier', 'grn.receivedByUser', 'item.category'])
            ->whereHas('grn', function($q) use ($dateFrom, $dateTo, $orgId) {
                $q->whereBetween('received_date', [$dateFrom, $dateTo]);
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            });

        if ($branchId) {
            $query->whereHas('grn', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($supplierId) {
            $query->whereHas('grn', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $grnItems = $query->orderBy('created_at', 'desc')->get();

        // Process item data with discounts
        $reportData = $this->processGrnItemData($grnItems);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportGrnItemReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.grn.items', compact(
            'reportData', 'branches', 'suppliers', 'items',
            'dateFrom', 'dateTo', 'branchId', 'supplierId', 'itemId'
        ));
    }

    /**
     * Process GRN Master data with payments and discounts - FIXED
     */
    private function processGrnMasterData($grnMasters)
    {
        $processedData = [];
        $totalValue = 0;
        $totalPaid = 0;
        $totalDiscount = 0;
        $totalGrandDiscount = 0;

        foreach ($grnMasters as $grn) {
            // Calculate item-wise discounts from GRN items
            $itemDiscounts = $grn->items->sum('discount_received') ?? 0;

            // Calculate grand discount amount
            $grandDiscountAmount = ($grn->grand_discount ?? 0) > 0 ?
                (($grn->sub_total - $itemDiscounts) * ($grn->grand_discount / 100)) : 0;

            // Get payment information from payments relationship or paid_amount attribute
            $paidAmount = $grn->paid_amount ?? $grn->payments->sum('amount') ?? 0;
            $outstandingAmount = $grn->total_amount - $paidAmount;

            // Calculate payment status
            $paymentStatus = $this->getPaymentStatus($grn->total_amount, $paidAmount);

            $processedData[] = [
                'grn' => $grn,
                'grn_id' => $grn->grn_id,
                'grn_number' => $grn->grn_number,
                'received_date' => $grn->received_date,
                'supplier_name' => $grn->supplier->name ?? 'N/A',
                'branch_name' => $grn->branch->name ?? 'N/A',
                'status' => $grn->status,
                'verification_status' => $grn->verification_status ?? $grn->status,
                'sub_total' => $grn->sub_total ?? 0,
                'total_amount' => $grn->total_amount,
                'item_discounts' => $itemDiscounts,
                'grand_discount_percentage' => $grn->grand_discount ?? 0,
                'grand_discount_amount' => $grandDiscountAmount,
                'net_amount' => $grn->total_amount,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => $paymentStatus,
                'items_count' => $grn->items->count(),
                'po_number' => $grn->purchaseOrder->po_number ?? 'N/A',
                'delivery_note_number' => $grn->delivery_note_number ?? 'N/A',
                'invoice_number' => $grn->invoice_number ?? 'N/A',
                'received_by' => $grn->receivedByUser->name ?? 'N/A',
                'verified_by' => $grn->verifiedByUser->name ?? 'N/A',
                'created_by' => $grn->createdByUser->name ?? 'N/A',
            ];

            $totalValue += $grn->total_amount;
            $totalPaid += $paidAmount;
            $totalDiscount += $itemDiscounts;
            $totalGrandDiscount += $grandDiscountAmount;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_grns' => count($processedData),
                'total_value' => $totalValue,
                'total_item_discounts' => $totalDiscount,
                'total_grand_discounts' => $totalGrandDiscount,
                'total_all_discounts' => $totalDiscount + $totalGrandDiscount,
                'net_value' => $totalValue,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalValue - $totalPaid,
                'payment_completion_rate' => $totalValue > 0 ? ($totalPaid / $totalValue) * 100 : 0,
            ]
        ];
    }
    /**
     * Process GRN Item data with discounts - FIXED
     */
    private function processGrnItemData($grnItems)
    {
        $processedData = [];
        $totalQuantity = 0;
        $totalValue = 0;
        $totalDiscount = 0;

        foreach ($grnItems as $item) {
            // Use proper GRN item relationships and calculations
            $lineTotal = ($item->accepted_quantity ?? 0) * ($item->buying_price ?? 0);
            $discountAmount = $item->discount_received ?? 0;
            $netAmount = $lineTotal - $discountAmount;

            $processedData[] = [
                'grn_item_id' => $item->grn_item_id,
                'grn_id' => $item->grn_id,
                'grn_number' => $item->grn->grn_number ?? 'N/A',
                'received_date' => $item->grn->received_date ?? null,
                'supplier_name' => $item->grn->supplier->name ?? 'N/A',
                'branch_name' => $item->grn->branch->name ?? 'N/A',
                'item_id' => $item->item_id,
                'item_name' => $item->item->name ?? $item->item_name ?? 'N/A',
                'item_code' => $item->item->item_code ?? $item->item_code ?? 'N/A',
                'item_category' => $item->item->category->name ?? 'N/A',
                'ordered_quantity' => $item->ordered_quantity ?? 0,
                'received_quantity' => $item->received_quantity ?? 0,
                'accepted_quantity' => $item->accepted_quantity ?? 0,
                'rejected_quantity' => $item->rejected_quantity ?? 0,
                'free_received_quantity' => $item->free_received_quantity ?? 0,
                'total_to_stock' => $item->total_to_stock ?? 0,
                'buying_price' => $item->buying_price ?? 0,
                'line_total' => $lineTotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $lineTotal > 0 ? ($discountAmount / $lineTotal) * 100 : 0,
                'net_amount' => $netAmount,
                'batch_no' => $item->batch_no ?? 'N/A',
                'manufacturing_date' => $item->manufacturing_date ?? null,
                'expiry_date' => $item->expiry_date ?? null,
                'rejection_reason' => $item->rejection_reason ?? 'N/A',
                'grn_status' => $item->grn->status ?? 'N/A',
            ];

            $totalQuantity += $item->accepted_quantity ?? 0;
            $totalValue += $lineTotal;
            $totalDiscount += $discountAmount;
        }

        return [
            'data' => $processedData,
            'summary' => [
                'total_items' => count($processedData),
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'total_discounts' => $totalDiscount,
                'net_value' => $totalValue - $totalDiscount,
                'average_discount_percentage' => $totalValue > 0 ? ($totalDiscount / $totalValue) * 100 : 0,
                'average_buying_price' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0,
            ]
        ];
    }

    /**
     * Export GRN Master Report
     */
    private function exportGrnMasterReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportGrnMasterPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        } elseif ($format === 'excel') {
            return Excel::download(new GrnMultiSheetExport($dateFrom, $dateTo),
                'grn-master-report-' . $dateFrom . '-to-' . $dateTo . '.xlsx');
        }
    }

    /**
     * Export GRN Item Report
     */
    private function exportGrnItemReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportGrnItemPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        }
    }

    /**
     * Export GRN Master PDF
     */
    private function exportGrnMasterPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.grn.pdf.master-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.grn.pdf.master', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('grn-master-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }

    /**
     * Export GRN Item PDF
     */
    private function exportGrnItemPdf($reportData, $dateFrom, $dateTo, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.grn.pdf.items-preview', compact('reportData', 'dateFrom', 'dateTo'));
        }

        $pdf = PDF::loadView('admin.reports.grn.pdf.items', compact('reportData', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('grn-items-report-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }

    /**
     * Get payment status
     */
    private function getPaymentStatus($totalAmount, $paidAmount)
    {
        if ($paidAmount == 0) {
            return 'Pending';
        } elseif ($paidAmount >= $totalAmount) {
            return 'Paid';
        } else {
            return 'Partial';
        }
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
     * Get suppliers for filter
     */
    private function getSuppliers($orgId)
    {
        $query = Supplier::where('is_active', true);
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
