<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use App\Services\AdminAuthService;
use App\Http\Controllers\Controller;

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
            'username' => 'required', 
            'password' => 'required',
        ]);

        $result = $this->authService->login(
            $credentials['username'], 
            $credentials['password'],
            $request->boolean('remember', false)
        );
    }

    public function logout(Request $request)
    {}

    public function adminLogout(Request $request)
    {}
}