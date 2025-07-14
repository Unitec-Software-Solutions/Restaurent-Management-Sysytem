<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GrnMaster;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    use Exportable;
    public function index(Request $request)
    {}
    public function create()
    {}
    public function store(Request $request)
    {}
    public function show(Supplier $supplier)
    {}
    public function purchaseOrders(Supplier $supplier)
    {}
    public function goodsReceived(Supplier $supplier)
    {}
}
