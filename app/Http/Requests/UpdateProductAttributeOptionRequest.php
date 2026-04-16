<?php

namespace App\Http\Requests;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductAttributeOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ProductAttribute $productAttribute */
        $productAttribute = $this->route('productAttribute');

        /** @var ProductAttributeOption $option */
        $option = $this->route('option');

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attribute_options', 'value')
                    ->ignore($option->id)
                    ->where(fn ($query) => $query->where('product_attribute_id', $productAttribute->id)),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
