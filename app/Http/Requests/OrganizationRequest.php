<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email,' . $this->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_designation' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'subscription_plan_id' => 'nullable|integer|exists:subscription_plans,id',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
