<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Unauthorized access.']);
        }

    }

    /**
     */
    public function reservations()
    {}

    /**
     */
    public function index()
    {}

    /**
     */
    public function getAdminDetails($adminId)
    {
        
    }

    public function edit(Admin $admin)
    {

    }

    public function update(Request $request, Admin $admin)
    {
        
    }
}
