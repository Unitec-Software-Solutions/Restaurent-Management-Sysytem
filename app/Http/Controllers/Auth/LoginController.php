<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     * This supports both regular users and organizational admins.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // First, try logging in as a regular user
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::guard('web')->user();
            
            // Log successful login
            \Illuminate\Support\Facades\Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'branch_id' => $user->branch_id,
                'role_id' => $user->role_id
            ]);

            // Redirect based on user role/type
            return $this->redirectUser($user);
        }

        // If user login fails, try admin login (for organizational admins)
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $admin = Auth::guard('admin')->user();
            
            // Log successful admin login
            \Illuminate\Support\Facades\Log::info('Admin logged in through user portal', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'organization_id' => $admin->organization_id,
                'branch_id' => $admin->branch_id ?? null,
                'is_super_admin' => $admin->is_super_admin
            ]);

            // Redirect admin to appropriate dashboard
            return $this->redirectAdmin($admin);
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectUser($user)
    {
        // If user has admin privileges, redirect to admin area
        if ($user->is_admin || $user->is_super_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Check if user has a role assigned
        if ($user->role_id && $user->userRole) {
            $roleName = $user->userRole->name;
            
            // Redirect based on role
            switch ($roleName) {
                case 'Manager':
                case 'Supervisor':
                    return redirect()->intended('/dashboard/management');
                case 'Staff':
                case 'Employee':
                    return redirect()->intended('/dashboard/staff');
                case 'Customer':
                    return redirect()->intended('/customer/dashboard');
                default:
                    return redirect()->intended('/dashboard');
            }
        }

        // Default redirect for users without specific roles
        return redirect()->intended('/dashboard');
    }

    /**
     * Redirect admin based on their type and permissions
     */
    protected function redirectAdmin($admin)
    {
        // Super admins always go to admin dashboard
        if ($admin->is_super_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Organizational admins go to admin dashboard
        if ($admin->organization_id && !$admin->branch_id) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Branch admins go to admin dashboard
        if ($admin->branch_id) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Default to admin dashboard for any admin
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the user out of the application.
     * This handles both regular users and admins.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        $admin = Auth::guard('admin')->user();
        
        // Log logout for user
        if ($user) {
            \Illuminate\Support\Facades\Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            Auth::guard('web')->logout();
        }

        // Log logout for admin
        if ($admin) {
            \Illuminate\Support\Facades\Log::info('Admin logged out through user portal', [
                'admin_id' => $admin->id,
                'email' => $admin->email
            ]);
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
