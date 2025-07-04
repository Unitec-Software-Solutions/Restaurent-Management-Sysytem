<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use App\Services\AdminAuthService;

class AdminAuthController extends Controller
{
    protected $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->authService = $authService;
    }
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authService->login(
            $credentials['email'],
            $credentials['password'],
            $request->boolean('remember')
        );

        if ($result['success']) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        Cookie::queue(Cookie::forget(config('session.cookie')));
        return redirect()->route('admin.login');
    }

    public function adminLogout(Request $request)
    {
        $this->authService->logout();
        Cookie::queue(Cookie::forget(config('session.cookie')));
        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}
