<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateGeneralSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GeneralSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.general', [
            'values' => [
                'store_name' => settings('general.store_name', config('app.name')),
                'support_email' => settings('general.support_email'),
                'support_phone' => settings('general.support_phone'),
            ],
        ]);
    }

    public function update(
        UpdateGeneralSettingsRequest $request,
        SettingsService $settings
    ): RedirectResponse {
        $validated = $request->validated();

        $settings->set('general.store_name', $validated['store_name']);
        $settings->set('general.support_email', $validated['support_email'] ?? null);
        $settings->set('general.support_phone', $validated['support_phone'] ?? null);

        return redirect()
            ->route('admin.settings.general.edit')
            ->with('success', 'Settings updated.');
    }
}
