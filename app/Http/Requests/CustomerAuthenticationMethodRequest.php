<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAuthenticationMethodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_profile_id' => 'required|exists:customer_profiles,id',
            'provider' => 'required|string|max:50',
            'provider_id' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'is_verified' => 'boolean',
            'email_verified_at' => 'nullable|date',
            'phone_verified_at' => 'nullable|date',
        ];
    }
}
