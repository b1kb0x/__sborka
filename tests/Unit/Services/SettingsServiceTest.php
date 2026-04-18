<?php

namespace Tests\Unit\Services;

use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_store_and_get_string_setting(): void
    {
        $service = app(SettingsService::class);

        $service->set('general.store_name', '__sborka');

        $this->assertSame('__sborka', $service->get('general.store_name'));
    }

    public function test_it_can_store_and_get_boolean_setting(): void
    {
        $service = app(SettingsService::class);

        $service->set('checkout.guest_checkout_enabled', true);

        $this->assertTrue($service->get('checkout.guest_checkout_enabled'));
    }

    public function test_it_can_store_and_get_integer_setting(): void
    {
        $service = app(SettingsService::class);

        $service->set('admin.products_per_page', 24);

        $this->assertSame(24, $service->get('admin.products_per_page'));
    }

    public function test_it_can_store_and_get_json_setting(): void
    {
        $service = app(SettingsService::class);

        $service->set('general.meta', [
            'title' => '__sborka',
            'enabled' => true,
        ], 'json');

        $this->assertSame([
            'title' => '__sborka',
            'enabled' => true,
        ], $service->get('general.meta'));
    }

    public function test_it_returns_default_when_setting_does_not_exist(): void
    {
        $service = app(SettingsService::class);

        $this->assertSame('fallback', $service->get('general.unknown_key', 'fallback'));
    }

    public function test_it_updates_existing_setting_instead_of_creating_duplicate(): void
    {
        $service = app(SettingsService::class);

        $service->set('general.store_name', 'First value');
        $service->set('general.store_name', 'Second value');

        $this->assertDatabaseCount('settings', 1);

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'store_name',
            'value' => 'Second value',
            'type' => 'string',
        ]);
    }

    public function test_it_throws_exception_for_invalid_full_key(): void
    {
        $service = app(SettingsService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->set('invalid-key', 'value');
    }

    public function test_it_clears_cache_after_set(): void
    {
        $service = app(SettingsService::class);

        $service->set('general.store_name', 'Old value');

        $this->assertSame('Old value', $service->get('general.store_name'));

        $service->set('general.store_name', 'New value');

        $this->assertSame('New value', $service->get('general.store_name'));
    }
}
