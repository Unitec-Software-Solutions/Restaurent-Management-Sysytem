<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuestController extends Controller
{
    public function reservation()
    {
        // TODO: Implement reservation logic
        return view('admin.reservation');
    }

}
