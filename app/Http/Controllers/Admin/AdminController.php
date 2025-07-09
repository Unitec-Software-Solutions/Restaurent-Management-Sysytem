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
        $admin = auth('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Unauthorized access.']);
        }

        return view('admin.dashboard', compact('admin'));
    }

    /**
     */
    public function reservations()
    {
        // List reservations for admin
        return view('admin.reservations.index');
    }

    /**
     */
    public function index()
    {
        // List admins
        return view('admin.admins.index');
    }

    /**
     */
    public function getAdminDetails($adminId)
    {
        // Show admin details
        return view('admin.admins.show', compact('adminId'));
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        // Validate and update admin
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ...other rules...
        ]);
        
        $admin->update($validated);
        
        return redirect()->route('admin.admins.index')->with('success', 'Admin updated.');
    }
}
