<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class PdfExportService
{
    /**
     * Generate PDF for Stock Report
     */
    public function generateStockReportPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'reportData' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Stock Levels Report',
            'reportSubtitle' => 'Comprehensive inventory stock analysis'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.stock-report', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate PDF for Stock Transactions Report
     */
    public function generateStockTransactionsPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'transactions' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Stock Transactions Report',
            'reportSubtitle' => 'Detailed stock movement history'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.stock-transactions', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate PDF for GTN Report
     */
    public function generateGtnReportPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'gtns' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Goods Transfer Note Report',
            'reportSubtitle' => 'Inter-branch transfer analysis'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.gtn-report', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate PDF for SRN Report
     */
    public function generateSrnReportPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'srns' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Stock Release Note Report',
            'reportSubtitle' => 'Stock release and consumption tracking'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.srn-report', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate PDF for GRN Report
     */
    public function generateGrnReportPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'grns' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Goods Receipt Note Report',
            'reportSubtitle' => 'Purchase and receiving analysis'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.grn-report', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate PDF for Category Report
     */
    public function generateCategoryReportPdf($data, $filters = [], $viewType = 'detailed')
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);

        $viewData = [
            'categories' => $data,
            'filters' => $filters,
            'user' => $user,
            'organization' => $isSuperAdmin ? null : $user->organization,
            'branch' => $user->branch ?? null,
            'generatedAt' => now(),
            'viewType' => $viewType,
            'reportTitle' => 'Category Performance Report',
            'reportSubtitle' => 'Item category analysis and insights'
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.category-report', $viewData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
