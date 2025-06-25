<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function items()
    {
        // TODO: Implement items logic
        return view('admin.items');
    }

}
