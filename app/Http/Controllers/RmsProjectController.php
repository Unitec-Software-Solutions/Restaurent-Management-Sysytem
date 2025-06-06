<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RmsProjectController extends Controller
{
    /**
     * Display the RMS project introduction page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('rmsproject');
    }
}
