<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckoutSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'guest_checkout_enabled' => $this->boolean('guest_checkout_enabled'),
        ]);
    }

    public function rules(): array
    {
        return [
            'guest_checkout_enabled' => ['nullable', 'boolean'],
            'default_order_status' => ['required', 'string', 'max:50'],
            'order_notification_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
