<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    use VerifiesEmails;

    /**
     * Dynamically determine where to redirect users after verification.
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
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
