<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentMaster;
use App\Models\SupplierPaymentDetail;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\GrnMaster;
use App\Models\PurchaseOrder;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SupplierPaymentController extends Controller
{
    protected function getOrganizationId()
    {}
    protected function basePaymentQuery()
    {}
    protected function baseSupplierQuery()
    {}
    protected function checkOrganization(SupplierPaymentMaster $payment)
    {}
    public function store(Request $request)
    {}
}