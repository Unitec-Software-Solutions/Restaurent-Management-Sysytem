<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DebugController extends Controller
{
    public function routes()
    {
        // Show debug routes
        return view('admin.debug.routes');
    }
}
