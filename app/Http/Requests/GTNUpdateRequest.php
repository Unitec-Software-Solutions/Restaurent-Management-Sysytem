<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\GTNService;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class GTNUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $gtnId = $this->route('id');

        return [
            // Remove gtn_number validation since it's not editable
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.transfer_quantity' => 'required|numeric|min:0.01',
            'items.*.transfer_price' => 'nullable|numeric|min:0', // Changed from required to nullable
            'items.*.batch_no' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'gtn_number.required' => 'GTN number is required.',
            'gtn_number.unique' => 'GTN number already exists.',
            'from_branch_id.required' => 'Origin branch is required.',
            'to_branch_id.required' => 'Destination branch is required.',
            'to_branch_id.different' => 'Destination branch must be different from origin branch.',
            'transfer_date.required' => 'Transfer date is required.',
            'items.required' => 'At least one item is required.',
            'items.*.item_id.required' => 'Item selection is required.',
            'items.*.transfer_quantity.required' => 'Transfer quantity is required.',
            'items.*.transfer_quantity.min' => 'Transfer quantity must be greater than 0.',
            'items.*.transfer_price.min' => 'Transfer price must be greater than or equal to 0.', // Removed required message
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->has('items') && $this->has('from_branch_id')) {
                $gtnService = app(GTNService::class);
                $fromBranchId = $this->input('from_branch_id');
                $items = $this->input('items', []);

                // Validate each item's stock availability
                foreach ($items as $index => $item) {
                    if (isset($item['item_id']) && isset($item['transfer_quantity'])) {
                        try {
                            $gtnService->validateItemStock(
                                $item['item_id'],
                                $fromBranchId,
                                $item['transfer_quantity']
                            );
                        } catch (\Exception $e) {
                            $validator->errors()->add(
                                "items.{$index}.transfer_quantity",
                                $e->getMessage()
                            );
                        }
                    }
                }
            }
        });
    }
}
