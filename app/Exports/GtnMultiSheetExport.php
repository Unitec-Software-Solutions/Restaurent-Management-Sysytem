<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GtnMultiSheetExport implements WithMultipleSheets
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
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // GTN Master Data Sheet
        $sheets[] = new GtnMasterSheet($this->gtnIds, $this->dateFrom, $this->dateTo, $this->filters);

        // GTN Items Data Sheet
        $sheets[] = new GtnItemsSheet($this->gtnIds, $this->dateFrom, $this->dateTo, $this->filters);

        return $sheets;
    }
}
