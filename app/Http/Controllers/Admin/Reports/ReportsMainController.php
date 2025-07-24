<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GrnMaster;
use App\Models\GoodsTransferNote;
use App\Models\StockReleaseNoteMaster;
use App\Models\ItemTransaction;

class ReportsMainController extends Controller
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
     * Main Reports Dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get report summaries
        $reportSummary = [
            'grn' => $this->getGrnSummary($orgId),
            'gtn' => $this->getGtnSummary($orgId),
            'srn' => $this->getSrnSummary($orgId),
            'stock' => $this->getStockSummary($orgId),
        ];

        return view('admin.reports.main.index', compact('reportSummary'));
    }

    /**
     * Get GRN report summary
     */
    private function getGrnSummary($orgId)
    {
        $query = GrnMaster::query();
        $this->applyOrganizationFilter($query, $orgId);

        return [
            'total_count' => $query->count(),
            'pending_count' => $query->where('status', 'Pending')->count(),
            'verified_count' => $query->where('status', 'Verified')->count(),
            'total_value' => $query->sum('total_amount') ?? 0,
        ];
    }

    /**
     * Get GTN report summary
     */
    private function getGtnSummary($orgId)
    {
        $query = GoodsTransferNote::query();
        $this->applyOrganizationFilter($query, $orgId);

        return [
            'total_count' => $query->count(),
            'pending_count' => $query->where('status', 'pending')->count(),
            'completed_count' => $query->where('status', 'completed')->count(),
            'total_value' => $query->sum('total_value') ?? 0,
        ];
    }

    /**
     * Get SRN report summary
     */
    private function getSrnSummary($orgId)
    {
        $query = StockReleaseNoteMaster::query();
        $this->applyOrganizationFilter($query, $orgId);

        return [
            'total_count' => $query->count(),
            'pending_count' => $query->where('status', 'pending')->count(),
            'completed_count' => $query->where('status', 'completed')->count(),
            'total_value' => $query->sum('total_value') ?? 0,
        ];
    }

    /**
     * Get Stock report summary
     */
    private function getStockSummary($orgId)
    {
        $query = ItemTransaction::query();
        $this->applyOrganizationFilter($query, $orgId);

        return [
            'total_transactions' => $query->count(),
            'stock_in_value' => $query->where('quantity', '>', 0)->sum('total_amount') ?? 0,
            'stock_out_value' => $query->where('quantity', '<', 0)->sum('total_amount') ?? 0,
            'net_value' => $query->sum('total_amount') ?? 0,
        ];
    }
}
