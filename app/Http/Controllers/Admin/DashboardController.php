<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        return view('dashboard', [
            'role' => $user->userRole
        ]);
    }

    public function staff()
    {
        $user = Auth::user();
        // ...existing code...
    }

    public function management()
    {
        // ...existing code...
    }
}
