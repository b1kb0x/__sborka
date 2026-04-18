<?php

namespace Tests\Feature\Admin\Settings;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_settings_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.settings.admin.edit'));

        $response->assertOk();
        $response->assertSee('Admin settings');
        $response->assertSee('Products per page');
        $response->assertSee('Orders per page');
        $response->assertSee('Customers per page');
        $response->assertSee('Sidebar collapsed by default');
    }

    public function test_admin_can_update_admin_settings(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.admin.update'), [
                'products_per_page' => 24,
                'orders_per_page' => 30,
                'customers_per_page' => 18,
                'sidebar_collapsed_by_default' => '1',
            ]);

        $response->assertRedirect(route('admin.settings.admin.edit'));
        $response->assertSessionHas('success', 'Settings updated.');

        $this->assertDatabaseHas('settings', [
            'group' => 'admin',
            'key' => 'products_per_page',
            'value' => '24',
            'type' => 'integer',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'admin',
            'key' => 'orders_per_page',
            'value' => '30',
            'type' => 'integer',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'admin',
            'key' => 'customers_per_page',
            'value' => '18',
            'type' => 'integer',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'admin',
            'key' => 'sidebar_collapsed_by_default',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function test_sidebar_collapsed_by_default_is_saved_as_false_when_unchecked(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.admin.update'), [
                'products_per_page' => 20,
                'orders_per_page' => 20,
                'customers_per_page' => 20,
            ]);

        $response->assertRedirect(route('admin.settings.admin.edit'));
        $response->assertSessionHas('success', 'Settings updated.');

        $this->assertDatabaseHas('settings', [
            'group' => 'admin',
            'key' => 'sidebar_collapsed_by_default',
            'value' => '0',
            'type' => 'boolean',
        ]);
    }

    public function test_admin_settings_validation_works(): void
    {
        $admin = $this->createAdmin();

        $response = $this->from(route('admin.settings.admin.edit'))
            ->actingAs($admin)
            ->put(route('admin.settings.admin.update'), [
                'products_per_page' => 0,
                'orders_per_page' => 201,
                'customers_per_page' => 'abc',
                'sidebar_collapsed_by_default' => '1',
            ]);

        $response->assertRedirect(route('admin.settings.admin.edit'));
        $response->assertSessionHasErrors([
            'products_per_page',
            'orders_per_page',
            'customers_per_page',
        ]);
    }

    public function test_guest_is_redirected_from_admin_settings_page(): void
    {
        $response = $this->get(route('admin.settings.admin.edit'));

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
