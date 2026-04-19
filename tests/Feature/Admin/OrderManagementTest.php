<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\SettingsService;
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
        'delivery_service_name' => null,
        'delivery_region_name' => null,
        'delivery_city_name' => null,
        'delivery_branch_name' => null,
        'delivery_branch_address' => null,
        'delivery_branch_postal_code' => null,
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
        'delivery_service_name' => 'Nova Poshta',
        'delivery_region_name' => 'Kyiv region',
        'delivery_city_name' => 'Kyiv',
        'delivery_branch_name' => 'Branch 12',
        'delivery_branch_address' => 'Branch street 12',
        'delivery_branch_postal_code' => '01012',
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
        ->assertSee('guest')
        ->assertSee('Delivery')
        ->assertSee('Nova Poshta')
        ->assertSee('Branch 12')
        ->assertSee('Branch street 12')
        ->assertSee('01012');
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

    $emptySnapshotOrder = Order::query()->create(adminOrderPayload([
        'email' => 'empty-delivery@example.test',
        'delivery_service_name' => null,
        'delivery_region_name' => null,
        'delivery_city_name' => null,
        'delivery_branch_name' => null,
        'delivery_branch_address' => null,
        'delivery_branch_postal_code' => null,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $emptySnapshotOrder))
        ->assertOk()
        ->assertSee('Delivery')
        ->assertSee('Service:')
        ->assertSee('Postal code:');
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

it('uses orders per page from settings in the admin orders list', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.orders_per_page', 3);

    foreach (range(1, 5) as $index) {
        Order::query()->create(adminOrderPayload([
            'email' => "order-{$index}@example.test",
        ]));
    }

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders) => $orders->perPage() === 3 && $orders->count() === 3);
});

it('falls back to 20 orders per page when the setting is invalid', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.orders_per_page', 0);

    foreach (range(1, 25) as $index) {
        Order::query()->create(adminOrderPayload([
            'email' => "fallback-order-{$index}@example.test",
        ]));
    }

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders) => $orders->perPage() === 20 && $orders->count() === 20);
});
