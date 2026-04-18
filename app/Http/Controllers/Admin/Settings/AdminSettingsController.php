<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateAdminSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.admin', [
            'values' => [
                'products_per_page' => settings('admin.products_per_page', 20),
                'orders_per_page' => settings('admin.orders_per_page', 20),
                'customers_per_page' => settings('admin.customers_per_page', 20),
                'sidebar_collapsed_by_default' => settings('admin.sidebar_collapsed_by_default', false),
            ],
        ]);
    }

    public function update(
        UpdateAdminSettingsRequest $request,
        SettingsService $settings
    ): RedirectResponse {
        $validated = $request->validated();

        $settings->set('admin.products_per_page', (int) $validated['products_per_page']);
        $settings->set('admin.orders_per_page', (int) $validated['orders_per_page']);
        $settings->set('admin.customers_per_page', (int) $validated['customers_per_page']);
        $settings->set(
            'admin.sidebar_collapsed_by_default',
            (bool) ($validated['sidebar_collapsed_by_default'] ?? false)
        );

        return redirect()
            ->route('admin.settings.admin.edit')
            ->with('success', 'Settings updated.');
    }
}
