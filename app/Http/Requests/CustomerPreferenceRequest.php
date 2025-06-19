<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPreferenceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_profile_id' => 'required|exists:customer_profiles,id',
            'dietary_restrictions' => 'nullable|array',
            'favorite_dishes' => 'nullable|array',
            'allergies' => 'nullable|array',
            'preferred_language' => 'nullable|string|max:10',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ];
    }
}
