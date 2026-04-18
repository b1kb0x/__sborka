<?php

namespace Tests\Feature\Admin\Settings;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_general_settings_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.settings.general.edit'));

        $response->assertOk();
        $response->assertSee('General settings');
        $response->assertSee('Store name');
        $response->assertSee('Support email');
        $response->assertSee('Support phone');
    }

    public function test_admin_can_update_general_settings(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'store_name' => '__sborka',
                'support_email' => 'support@example.com',
                'support_phone' => '+380500000001',
            ]);

        $response->assertRedirect(route('admin.settings.general.edit'));
        $response->assertSessionHas('success', 'Settings updated.');

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'store_name',
            'value' => '__sborka',
            'type' => 'string',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'support_email',
            'value' => 'support@example.com',
            'type' => 'string',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'support_phone',
            'value' => '+380500000001',
            'type' => 'string',
        ]);
    }

    public function test_general_settings_validation_works(): void
    {
        $admin = $this->createAdmin();

        $response = $this->from(route('admin.settings.general.edit'))
            ->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'store_name' => '',
                'support_email' => 'not-an-email',
                'support_phone' => str_repeat('1', 60),
            ]);

        $response->assertRedirect(route('admin.settings.general.edit'));
        $response->assertSessionHasErrors([
            'store_name',
            'support_email',
            'support_phone',
        ]);
    }

    public function test_guest_is_redirected_from_general_settings_page(): void
    {
        $response = $this->get(route('admin.settings.general.edit'));

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
