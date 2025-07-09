<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckTableAvailabilityController extends Controller
{
    public function index()
    {
        // Show table availability
        return view('admin.tables.availability');
    }
}
