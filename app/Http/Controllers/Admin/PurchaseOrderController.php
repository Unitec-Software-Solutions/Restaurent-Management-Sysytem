<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    public function edit($id)
    {
        // TODO: Implement edit logic
        return view('admin.edit', compact('id'));
    }

}
