<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function salesReport()
    {
        return view('admin.reports.sales.index');
    }

    public function inventoryReport()
    {
        return view('admin.reports.inventory.index');
    }

    public function inventoryGrn()
    {
        return view('admin.reports.inventory.grn.index');
    }

    public function inventoryGtn()
    {
        return view('admin.reports.inventory.gtn.index');
    }

    public function inventorySrn()
    {
        return view('admin.reports.inventory.srn.index');
    }

    public function inventoryStock()
    {
        return view('admin.reports.inventory.stock.index');
    }


}
