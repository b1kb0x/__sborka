<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminOrderPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Order',
        'last_name' => 'Customer',
        'phone' => '+380000000000',
        'email' => 'order@example.test',
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Main street 1',
        'comment' => 'Leave at the desk',
        'subtotal' => 1000,
        'total' => 1000,
        'status' => OrderStatus::New,
        'fulfillment_status' => FulfillmentStatus::Accepted,
    ], $overrides);
}

it('shows guest checkout snapshot details on the admin order page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $order = Order::query()->create(adminOrderPayload([
        'email' => 'guest-order@example.test',
    ]));

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $order))
        ->assertOk()
        ->assertSee('Customer details')
        ->assertSee('Order')
        ->assertSee('Customer')
        ->assertSee('+380000000000')
        ->assertSee('Main street 1')
        ->assertSee('Leave at the desk')
        ->assertSee('Customer status:')
        ->assertSee('guest');
});

it('shows active customer status on the admin order page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $customer = User::factory()->create([
        'email' => 'active-order@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $order = Order::query()->create(adminOrderPayload([
        'user_id' => $customer->id,
        'email' => $customer->email,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $order))
        ->assertOk()
        ->assertSee('active');
});

it('shows blocked customer status on the admin order page and handles missing legacy users safely', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $blockedCustomer = User::factory()->create([
        'email' => 'blocked-order@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Blocked,
    ]);

    $blockedOrder = Order::query()->create(adminOrderPayload([
        'user_id' => $blockedCustomer->id,
        'email' => $blockedCustomer->email,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $blockedOrder))
        ->assertOk()
        ->assertSee('blocked');

    $legacyOrder = new Order(adminOrderPayload([
        'user_id' => 999999,
        'email' => 'legacy-missing-user@example.test',
        'comment' => null,
    ]));

    expect($legacyOrder->customer_status)->toBe('guest');
});

it('filters the admin orders list by customer when requested', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $targetCustomer = User::factory()->create([
        'name' => 'Target Customer',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $otherCustomer = User::factory()->create([
        'name' => 'Other Customer',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $targetOrder = Order::query()->create(adminOrderPayload([
        'user_id' => $targetCustomer->id,
        'email' => $targetCustomer->email,
    ]));

    $otherOrder = Order::query()->create(adminOrderPayload([
        'user_id' => $otherCustomer->id,
        'email' => $otherCustomer->email,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.orders.index', ['customer' => $targetCustomer->id]))
        ->assertOk()
        ->assertSee('Filtering by customer:')
        ->assertSee('Target Customer')
        ->assertSee((string) $targetOrder->id)
        ->assertDontSee('Other Customer')
        ->assertDontSee($otherCustomer->email);
});
