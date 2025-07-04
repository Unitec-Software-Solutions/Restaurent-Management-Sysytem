<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('dashboard', [
            'user' => $user,
            'organization' => $user->organization,
            'branch' => $user->branch,
            'role' => $user->userRole
        ]);
    }

    /**
     * Show staff dashboard
     */
    public function staff()
    {
        $user = Auth::user();
        
        // Check if user has staff role
        if (!$user->userRole || !in_array($user->userRole->name, ['Staff', 'Employee'])) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }
        
        return view('dashboard.staff', [
            'user' => $user,
            'organization' => $user->organization,
            'branch' => $user->branch,
            'role' => $user->userRole
        ]);
    }

    /**
     * Show management dashboard
     */
    public function management()
    {
        $user = Auth::user();
        
        // Check if user has management role
        if (!$user->userRole || !in_array($user->userRole->name, ['Manager', 'Supervisor'])) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }
        
        return view('dashboard.management', [
            'user' => $user,
            'organization' => $user->organization,
            'branch' => $user->branch,
            'role' => $user->userRole
        ]);
    }
}
