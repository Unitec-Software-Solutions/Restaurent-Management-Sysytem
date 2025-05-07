<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function checkPhone(Request $request)
{
    $request->validate(['phone_number' => 'required']);
    $customer = \App\Models\Customer::where('phone', $request->phone)->first();

    if ($customer) {
        // Show a view asking if the user wants to login
        return view('reservations.ask_login', ['phone_number' => $request->phone]);
    } else {
        // Show a view asking if the user wants to sign up
        return view('reservations.ask_signup', ['phone_number' => $request->phone]);
    }
}
}
