<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\GrnMaster;
use App\Models\GrnItem;

class GrnMultiSheetExport implements WithMultipleSheets
{
    protected $grnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($grnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->grnIds = $grnIds;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->filters = $filters;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // GRN Master Data Sheet
        $sheets[] = new GrnMasterSheet($this->grnIds, $this->dateFrom, $this->dateTo, $this->filters);

        // GRN Items Data Sheet
        $sheets[] = new GrnItemsSheet($this->grnIds, $this->dateFrom, $this->dateTo, $this->filters);

        return $sheets;
    }
}
