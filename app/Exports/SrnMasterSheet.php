<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\StockReleaseNoteMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SrnMasterSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $srnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($srnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->srnIds = $srnIds;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);
        $orgId = $isSuperAdmin ? null : ($user->organization_id ?? null);

        $query = StockReleaseNoteMaster::with([
            'branch',
            'organization',
            'items.item.category'
        ]);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $orgId) {
            $query->where('organization_id', $orgId);
        }

        // Apply specific SRN IDs if provided
        if ($this->srnIds) {
            $query->whereIn('id', $this->srnIds);
        }

        // Apply date range
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('release_date', [$this->dateFrom, $this->dateTo]);
        }

        // Apply additional filters, but exclude item_id since it's filtered through relationship in controller
        foreach ($this->filters as $column => $value) {
            if ($column === 'item_id') {
                continue; // Skip item_id as it's handled through the relationship in the controller
            }
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        $result = $query->orderBy('release_date', 'desc')->get();

        // Debug log to help troubleshoot empty exports
        Log::info('SrnMasterSheet export collection', [
            'count' => $result->count(),
            'srnIds' => $this->srnIds,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'filters' => $this->filters,
            'orgId' => $orgId,
            'isSuperAdmin' => $isSuperAdmin
        ]);

        return $result;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'SRN ID',
            'SRN Number',
            'Organization',
            'Branch',
            'Release Type',
            'Release Date',
            'Verified Date',
            'Total Items',
            'Total Quantity',
            'Total Value',
            'Reason/Notes',
            'Status',
            'Created Date'
        ];
    }

    /**
     * @param mixed $srn
     * @return array
     */
    public function map($srn): array
    {
        try {
            return [
                $srn->id,
                $srn->srn_number,
                $srn->organization->name ?? 'N/A',
                $srn->branch->name ?? 'N/A',
                $srn->release_type,
                $srn->release_date ? $srn->release_date->format('Y-m-d') : 'N/A',
                $srn->verified_at ? $srn->verified_at->format('Y-m-d H:i:s') : 'N/A',
                $srn->items->count(),
                $srn->items->sum('release_quantity'),
                $srn->items->sum('line_total'),
                $srn->notes ?? 'N/A',
                $srn->status ?? 'Completed',
                $srn->created_at ? $srn->created_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        } catch (\Exception $e) {
            Log::error('SrnMasterSheet mapping error', [
                'srn_id' => $srn->id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            // Return safe values if there's an error
            return [
                $srn->id ?? 'N/A',
                $srn->srn_number ?? 'N/A',
                'ERROR',
                'ERROR',
                $srn->release_type ?? 'N/A',
                'N/A',
                'N/A',
                0,
                0,
                0,
                'N/A',
                'ERROR',
                'N/A'
            ];
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'SRN Master Data';
    }
}
