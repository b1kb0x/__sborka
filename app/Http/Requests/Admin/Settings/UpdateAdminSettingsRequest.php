<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sidebar_collapsed_by_default' => $this->boolean('sidebar_collapsed_by_default'),
        ]);
    }

    public function rules(): array
    {
        return [
            'products_per_page' => ['required', 'integer', 'min:1', 'max:200'],
            'orders_per_page' => ['required', 'integer', 'min:1', 'max:200'],
            'customers_per_page' => ['required', 'integer', 'min:1', 'max:200'],
            'sidebar_collapsed_by_default' => ['nullable', 'boolean'],
        ];
    }
}
