<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.dashboard', [
            'role' => $user ? $user->userRole : null
        ]);
    }

    public function staff()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.staff', compact('user'));
    }

    public function management()
    {
        return view('admin.management');
    }
}
