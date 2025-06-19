<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'menu_category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image_path' => 'nullable|string|max:255',
            'is_available' => 'boolean',
            'requires_preparation' => 'boolean',
            'preparation_time' => 'nullable|integer|min:0',
            'station' => 'nullable|string|max:100',
            'is_vegetarian' => 'boolean',
            'contains_alcohol' => 'boolean',
            'allergens' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}
