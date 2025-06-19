<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => 'required|string|max:255',
            'model_type' => 'required|string|max:255',
            'model_id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:255',
        ];
    }
}
