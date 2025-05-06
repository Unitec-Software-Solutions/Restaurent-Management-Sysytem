<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerAuthController extends Controller
{
    // Add your methods here

    public function showRegistrationForm(Request $request)
    {
        $phone = $request->input('phone');
        return view('auth.register', compact('phone'));
    }
}
