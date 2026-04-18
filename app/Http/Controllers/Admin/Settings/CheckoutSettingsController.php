<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateCheckoutSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.checkout', [
            'values' => [
                'guest_checkout_enabled' => settings('checkout.guest_checkout_enabled', true),
                'default_order_status' => settings('checkout.default_order_status', 'new'),
                'order_notification_email' => settings('checkout.order_notification_email'),
            ],
        ]);
    }

    public function update(
        UpdateCheckoutSettingsRequest $request,
        SettingsService $settings
    ): RedirectResponse {
        $validated = $request->validated();

        $settings->set(
            'checkout.guest_checkout_enabled',
            (bool) ($validated['guest_checkout_enabled'] ?? false)
        );

        $settings->set(
            'checkout.default_order_status',
            $validated['default_order_status']
        );

        $settings->set(
            'checkout.order_notification_email',
            $validated['order_notification_email'] ?? null
        );

        return redirect()
            ->route('admin.settings.checkout.edit')
            ->with('success', 'Settings updated.');
    }
}
