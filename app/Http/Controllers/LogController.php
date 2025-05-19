<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClickLog;

class LogController extends Controller
{
    public function logClick(Request $request)
    {
        $log = new ClickLog();
        $log->function_name = $request->input('function_name');
        $log->save();

        return response()->json(['function' => $request->input('function_name')]);
    }
}