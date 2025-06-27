<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KitchenController extends Controller
{
    public function orders()
    {
        // TODO: Implement orders logic
        return view('admin.orders');
    }

}
