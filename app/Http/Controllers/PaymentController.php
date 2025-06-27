<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function create()
    {
        // TODO: Implement create logic
        return view('admin.create');
    }

}
