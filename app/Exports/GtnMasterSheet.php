<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\GoodsTransferNote;
use Illuminate\Support\Facades\Auth;

class GtnMasterSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $gtnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($gtnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->gtnIds = $gtnIds;
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

        $query = GoodsTransferNote::with([
            'fromBranch',
            'toBranch',
            'organization',
            'createdBy',
            'approvedBy',
            'rejectedBy',
            'verifiedBy',
            'receivedBy'
        ]);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $orgId) {
            $query->where('organization_id', $orgId);
        }

        // Apply specific GTN IDs if provided
        if ($this->gtnIds) {
            $query->whereIn('gtn_id', $this->gtnIds);
        }

        // Apply date range
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        }

        // Apply additional filters with null/empty value checks
        foreach ($this->filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'GTN ID',
            'GTN Number',
            'Organization',
            'From Branch',
            'To Branch',
            'Origin Status',
            'Receiver Status',
            'Transfer Date',
            'Total Items',
            'Total Value',
            'Created By',
            'Approved By',
            'Received By',
            'Verified By',
            'Rejected By',
            'Approved Date',
            'Received Date',
            'Verified Date',
            'Rejected Date',
            'Created Date',
            'Notes',
            'Rejection Reason'
        ];
    }

    /**
     * @param mixed $gtn
     * @return array
     */
    public function map($gtn): array
    {
        return [
            $gtn->gtn_id,
            $gtn->gtn_number,
            $gtn->organization->name ?? 'N/A',
            $gtn->fromBranch->name ?? 'N/A',
            $gtn->toBranch->name ?? 'N/A',
            $gtn->origin_status,
            $gtn->receiver_status,
            $gtn->transfer_date ? $gtn->transfer_date->format('Y-m-d') : 'N/A',
            $gtn->items->count(),
            $gtn->items->sum('line_total'),
            $gtn->createdBy->name ?? 'N/A',
            $gtn->approvedBy->name ?? 'N/A',
            $gtn->receivedBy->name ?? 'N/A',
            $gtn->verifiedBy->name ?? 'N/A',
            $gtn->rejectedBy->name ?? 'N/A',
            $gtn->approved_date ? $gtn->approved_date->format('Y-m-d H:i:s') : 'N/A',
            $gtn->received_date ? $gtn->received_date->format('Y-m-d H:i:s') : 'N/A',
            $gtn->verified_date ? $gtn->verified_date->format('Y-m-d H:i:s') : 'N/A',
            $gtn->rejected_date ? $gtn->rejected_date->format('Y-m-d H:i:s') : 'N/A',
            $gtn->created_at ? $gtn->created_at->format('Y-m-d H:i:s') : 'N/A',
            $gtn->notes ?? 'N/A',
            $gtn->rejection_reason ?? 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'GTN Master Data';
    }
}
