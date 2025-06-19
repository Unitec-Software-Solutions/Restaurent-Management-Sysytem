<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reservation_id' => 'nullable|exists:reservations,id',
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_type' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
