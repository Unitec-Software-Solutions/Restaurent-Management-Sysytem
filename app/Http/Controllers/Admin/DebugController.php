<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DebugController extends Controller
{
    public function routes()
    {
        // TODO: Implement routes logic
        return view('admin.routes');
    }

}
