<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows user status in the admin customers list without delete or restore controls', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $customer = User::factory()->create([
        'name' => 'Managed Customer',
        'email' => 'managed@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Blocked,
    ]);

    Order::query()->create([
        'user_id' => $customer->id,
        'first_name' => 'Managed',
        'last_name' => 'Customer',
        'phone' => '+380000000000',
        'email' => $customer->email,
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Main street 1',
        'comment' => null,
        'subtotal' => 1000,
        'total' => 1000,
        'status' => 'new',
        'fulfillment_status' => 'accepted',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertSee('Status')
        ->assertSee('blocked')
        ->assertSee(route('admin.orders.index', ['customer' => $customer->id]), false)
        ->assertDontSee('with_trashed', false)
        ->assertDontSee('Delete')
        ->assertDontSee('Restore')
        ->assertDontSee('Open');

    expect($customer->fresh()->status)->toBe(UserStatus::Blocked);
});

it('does not expose admin routes for deleting or restoring customers', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $customer = User::factory()->create([
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->delete("/admin/customers/{$customer->id}")
        ->assertStatus(405);

    $this->actingAs($admin)
        ->post("/admin/customers/{$customer->id}/restore")
        ->assertNotFound();

    expect($customer->fresh())->not->toBeNull();
});

it('uses the customer show page as the main edit form and updates profile fields with synced name', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $customer = User::factory()->create([
        'name' => 'Old Name',
        'first_name' => 'Old',
        'last_name' => 'Name',
        'phone' => '000',
        'email' => 'customer-edit@example.test',
        'region' => 'Old region',
        'city' => 'Old city',
        'address' => 'Old address',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.customers.edit', $customer))
        ->assertOk()
        ->assertSee('First name')
        ->assertSee('Last name')
        ->assertSee('Customer status')
        ->assertDontSee('Open');

    $this->actingAs($admin)
        ->put(route('admin.customers.update', $customer), [
            'first_name' => 'Jane',
            'last_name' => 'Customer',
            'phone' => '+380111111111',
            'email' => 'jane.customer@example.test',
            'status' => UserStatus::Blocked->value,
            'region' => 'Kyiv region',
            'city' => 'Kyiv',
            'address' => 'Main street 10',
        ])
        ->assertRedirect(route('admin.customers.edit', $customer));

    $customer->refresh();

    expect($customer->name)->toBe('Jane Customer');
    expect($customer->first_name)->toBe('Jane');
    expect($customer->last_name)->toBe('Customer');
    expect($customer->phone)->toBe('+380111111111');
    expect($customer->email)->toBe('jane.customer@example.test');
    expect($customer->status)->toBe(UserStatus::Blocked);
    expect($customer->region)->toBe('Kyiv region');
    expect($customer->city)->toBe('Kyiv');
    expect($customer->address)->toBe('Main street 10');
});

it('no longer exposes a separate customer edit route', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $customer = User::factory()->create([
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get("/admin/customers/{$customer->id}/edit")
        ->assertNotFound();
});

it('uses customers per page from settings in the admin customers list', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.customers_per_page', 3);

    User::factory()->count(5)->create([
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertViewHas('customers', fn ($customers) => $customers->perPage() === 3 && $customers->count() === 3);
});

it('falls back to 20 customers per page when the setting is invalid', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.customers_per_page', 0);

    User::factory()->count(25)->create([
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertViewHas('customers', fn ($customers) => $customers->perPage() === 20 && $customers->count() === 20);
});
