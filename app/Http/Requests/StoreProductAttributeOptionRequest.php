<?php

namespace App\Http\Requests;

use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductAttributeOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ProductAttribute $productAttribute */
        $productAttribute = $this->route('productAttribute');

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attribute_options', 'value')
                    ->where(fn ($query) => $query->where('product_attribute_id', $productAttribute->id)),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
