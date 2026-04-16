<?php

namespace App\Http\Requests;

use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ProductAttribute $productAttribute */
        $productAttribute = $this->route('product_attribute');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'slug')->ignore($productAttribute->id),
            ],
            'type' => ['required', Rule::in(['string', 'text', 'number', 'boolean', 'select'])],
            'unit' => ['nullable', 'string', 'max:50'],
            'display_group' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }
}
