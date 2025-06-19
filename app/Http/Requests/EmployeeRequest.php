<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'emp_id' => 'required|string|max:50|unique:employees,emp_id,' . $this->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $this->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:100',
            'branch_id' => 'required|exists:branches,id',
            'organization_id' => 'required|exists:organizations,id',
            'is_active' => 'boolean',
            'joined_date' => 'required|date',
            'address' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:20',
        ];
    }
}
