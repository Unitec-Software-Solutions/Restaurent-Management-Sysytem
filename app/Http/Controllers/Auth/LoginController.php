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
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
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
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout
        if ($user) {
            \Illuminate\Support\Facades\Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
