<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SrnMultiSheetExport implements WithMultipleSheets
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
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // SRN Master Data Sheet
        $sheets[] = new SrnMasterSheet($this->srnIds, $this->dateFrom, $this->dateTo, $this->filters);

        // SRN Items Data Sheet
        $sheets[] = new SrnItemsSheet($this->srnIds, $this->dateFrom, $this->dateTo, $this->filters);

        return $sheets;
    }
}
