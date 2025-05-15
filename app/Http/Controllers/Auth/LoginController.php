<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Dynamically determine where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (Auth::guard('admin')->check()) {
            return route('admin.dashboard'); // Redirect admins to admin dashboard
        }

        return '/home'; // Redirect regular users to user dashboard
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
