<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Branch;

class ReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $branch = Branch::findOrFail($this->branch_id);

        return [
            'branch_id' => 'required|exists:branches,id',
            'date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($branch) {
                    if (strtotime($value) < strtotime($branch->open_time)) {
                        $fail('Start time must be after branch opening time.');
                    }
                }
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) use ($branch) {
                    if (strtotime($value) > strtotime($branch->close_time)) {
                        $fail('End time must be before branch closing time.');
                    }
                }
            ],
            'num_people' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($branch) {
                    if ($value > $branch->max_capacity) {
                        $fail('Number of people exceeds branch capacity.');
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|min:10',
            'table_size' => 'required|integer|min:1',
            'order_type' => 'required|in:takeaway,dine-in',
            'payment_method' => 'required|string',
            'comments' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'date.after_or_equal' => 'Reservation date must be today or a future date.',
            'start_time.date_format' => 'Start time must be in HH:mm format.',
            'end_time.date_format' => 'End time must be in HH:mm format.',
            'end_time.after' => 'End time must be after start time.',
            'num_people.min' => 'Number of people must be at least 1.',
            'order_type.in' => 'Order type must be either takeaway or dine-in.'
        ];
    }
} 