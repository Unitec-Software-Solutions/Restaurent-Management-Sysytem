<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'number' => 'required|integer|min:1',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string|max:50',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ];
    }
}
