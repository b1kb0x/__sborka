<?php

namespace Tests\Feature\Admin\Settings;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_checkout_settings_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.settings.checkout.edit'));

        $response->assertOk();
        $response->assertSee('Checkout settings');
        $response->assertSee('Guest checkout enabled');
        $response->assertSee('Default order status');
        $response->assertSee('Order notification email');
    }

    public function test_admin_can_update_checkout_settings(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.checkout.update'), [
                'guest_checkout_enabled' => '1',
                'default_order_status' => 'new',
                'order_notification_email' => 'orders@example.com',
            ]);

        $response->assertRedirect(route('admin.settings.checkout.edit'));
        $response->assertSessionHas('success', 'Settings updated.');

        $this->assertDatabaseHas('settings', [
            'group' => 'checkout',
            'key' => 'guest_checkout_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'checkout',
            'key' => 'default_order_status',
            'value' => 'new',
            'type' => 'string',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'checkout',
            'key' => 'order_notification_email',
            'value' => 'orders@example.com',
            'type' => 'string',
        ]);
    }

    public function test_guest_checkout_enabled_is_saved_as_false_when_unchecked(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.checkout.update'), [
                'default_order_status' => 'processing',
                'order_notification_email' => 'orders@example.com',
            ]);

        $response->assertRedirect(route('admin.settings.checkout.edit'));
        $response->assertSessionHas('success', 'Settings updated.');

        $this->assertDatabaseHas('settings', [
            'group' => 'checkout',
            'key' => 'guest_checkout_enabled',
            'value' => '0',
            'type' => 'boolean',
        ]);
    }

    public function test_checkout_settings_validation_works(): void
    {
        $admin = $this->createAdmin();

        $response = $this->from(route('admin.settings.checkout.edit'))
            ->actingAs($admin)
            ->put(route('admin.settings.checkout.update'), [
                'guest_checkout_enabled' => '1',
                'default_order_status' => '',
                'order_notification_email' => 'not-an-email',
            ]);

        $response->assertRedirect(route('admin.settings.checkout.edit'));
        $response->assertSessionHasErrors([
            'default_order_status',
            'order_notification_email',
        ]);
    }

    public function test_guest_is_redirected_from_checkout_settings_page(): void
    {
        $response = $this->get(route('admin.settings.checkout.edit'));

        $response->assertRedirect(route('login'));
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);
    }
}
