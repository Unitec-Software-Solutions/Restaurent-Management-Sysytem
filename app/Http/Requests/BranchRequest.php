<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'total_capacity' => 'required|integer|min:1',
            'reservation_fee' => 'required|numeric|min:0',
            'cancellation_fee' => 'required|numeric|min:0',
            'type' => 'nullable|string|max:50',
            'activation_key' => 'required|string|unique:branches,activation_key,' . $this->id,
            'is_active' => 'boolean',
        ];
    }
}
