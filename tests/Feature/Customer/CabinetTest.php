<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function cabinetCustomer(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
        'first_name' => 'Cabinet',
        'last_name' => 'Customer',
        'phone' => '+380501234567',
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Cabinet street 1',
    ], $attributes));
}

function cabinetProduct(): Product
{
    $uuid = (string) Str::uuid();

    return Product::query()->create([
        'title' => 'Cabinet Product '.$uuid,
        'slug' => 'cabinet-product-'.Str::lower($uuid),
        'short_description' => 'Cabinet product',
        'description' => 'Cabinet product description',
        'price' => 499,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function cabinetOrder(User $user, array $attributes = []): Order
{
    return Order::query()->create(array_merge([
        'user_id' => $user->id,
        'first_name' => 'Order',
        'last_name' => 'Snapshot',
        'phone' => '+380501234567',
        'email' => $user->email,
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Snapshot address 7',
        'comment' => 'Snapshot comment',
        'subtotal' => 998,
        'total' => 998,
        'status' => OrderStatus::New,
        'fulfillment_status' => FulfillmentStatus::Accepted,
    ], $attributes));
}

it('customer can open cabinet dashboard', function () {
    $customer = cabinetCustomer();

    $this->actingAs($customer)
        ->get(route('customer.dashboard'))
        ->assertOk()
        ->assertSee('Hello')
        ->assertSee('My orders')
        ->assertSee('Profile')
        ->assertSee('Logout');
});

it('customer sees only their own orders', function () {
    $customer = cabinetCustomer(['email' => 'customer-a@example.test']);
    $otherCustomer = cabinetCustomer(['email' => 'customer-b@example.test']);

    $ownOrder = cabinetOrder($customer);
    $otherOrder = cabinetOrder($otherCustomer);

    $this->actingAs($customer)
        ->get(route('customer.orders.index'))
        ->assertOk()
        ->assertSee((string) $ownOrder->id)
        ->assertDontSee(route('customer.orders.show', $otherOrder));
});

it('customer can open their own order', function () {
    $customer = cabinetCustomer();
    $order = cabinetOrder($customer, [
        'first_name' => 'Snapshot',
        'last_name' => 'Owner',
        'phone' => '+380500000001',
        'region' => 'Lviv region',
        'city' => 'Lviv',
        'address' => 'Order address 12',
        'comment' => 'Handle with care',
    ]);

    $product = cabinetProduct();

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_title' => $product->title,
        'unit_price' => 499,
        'quantity' => 2,
        'grind_type' => 'beans',
    ]);

    $this->actingAs($customer)
        ->get(route('customer.orders.show', $order))
        ->assertOk()
        ->assertSee('Order #'.$order->id)
        ->assertSee('Snapshot')
        ->assertSee('Owner')
        ->assertSee('Handle with care')
        ->assertSee($product->title);
});

it('customer cannot open another customers order', function () {
    $customer = cabinetCustomer(['email' => 'owner@example.test']);
    $otherCustomer = cabinetCustomer(['email' => 'other@example.test']);
    $otherOrder = cabinetOrder($otherCustomer);

    $this->actingAs($customer)
        ->get(route('customer.orders.show', $otherOrder))
        ->assertForbidden();
});

it('legacy orders index redirects customer to cabinet orders', function () {
    $customer = cabinetCustomer(['email' => 'legacy-orders-index@example.test']);

    $this->actingAs($customer)
        ->get(route('orders.index'))
        ->assertRedirect(route('customer.orders.index'));
});

it('guest is redirected to login from legacy orders index', function () {
    $this->get(route('orders.index'))
        ->assertRedirect('/login');
});

it('admin cannot use legacy orders index', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('orders.index'))
        ->assertForbidden();
});

it('legacy order show redirects customer to cabinet order page', function () {
    $customer = cabinetCustomer(['email' => 'legacy-orders-show@example.test']);
    $order = cabinetOrder($customer);

    $this->actingAs($customer)
        ->get(route('orders.show', $order))
        ->assertRedirect(route('customer.orders.show', $order));
});

it('customer cannot open another customers order through legacy route', function () {
    $customer = cabinetCustomer(['email' => 'legacy-owner@example.test']);
    $otherCustomer = cabinetCustomer(['email' => 'legacy-other@example.test']);
    $otherOrder = cabinetOrder($otherCustomer);

    $this->actingAs($customer)
        ->get(route('orders.show', $otherOrder))
        ->assertForbidden();
});

it('customer can still view order snapshot after ordered product is deleted', function () {
    $customer = cabinetCustomer(['email' => 'snapshot-after-delete@example.test']);
    $order = cabinetOrder($customer);
    $product = cabinetProduct();

    $item = OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_title' => $product->title,
        'unit_price' => 499,
        'quantity' => 1,
        'grind_type' => 'beans',
    ]);

    $product->delete();

    $item->refresh();

    expect($item->product_id)->toBeNull();

    $this->actingAs($customer)
        ->get(route('customer.orders.show', $order))
        ->assertOk()
        ->assertSee($item->product_title)
        ->assertSee('Order #'.$order->id);
});

it('legacy account profile redirects customer to cabinet profile', function () {
    $customer = cabinetCustomer(['email' => 'legacy-profile-customer@example.test']);

    $this->actingAs($customer)
        ->get(route('account.profile'))
        ->assertRedirect(route('customer.profile.edit'));
});

it('guest is redirected to login from legacy account profile', function () {
    $this->get(route('account.profile'))
        ->assertRedirect('/login');
});

it('admin can still open legacy account profile', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
        'email' => 'legacy-profile-admin@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('account.profile'))
        ->assertOk()
        ->assertSee('Email');
});

it('customer can update their profile', function () {
    $customer = cabinetCustomer([
        'email' => 'profile-update@example.test',
    ]);

    $order = cabinetOrder($customer, [
        'first_name' => 'Old snapshot first',
        'address' => 'Old snapshot address',
    ]);

    $this->actingAs($customer)
        ->put(route('customer.profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Customer',
            'phone' => '+380509999999',
            'email' => 'updated-profile@example.test',
            'region' => 'Odesa region',
            'city' => 'Odesa',
            'address' => 'Updated address 55',
        ])
        ->assertRedirect(route('customer.profile.edit'));

    $customer->refresh();
    $order->refresh();

    expect($customer->first_name)->toBe('Updated');
    expect($customer->last_name)->toBe('Customer');
    expect($customer->phone)->toBe('+380509999999');
    expect($customer->email)->toBe('updated-profile@example.test');
    expect($customer->region)->toBe('Odesa region');
    expect($customer->city)->toBe('Odesa');
    expect($customer->address)->toBe('Updated address 55');
    expect($customer->name)->toBe('Updated Customer');

    expect($order->first_name)->toBe('Old snapshot first');
    expect($order->address)->toBe('Old snapshot address');
});

it('guest cannot open customer cabinet', function () {
    $this->get(route('customer.dashboard'))
        ->assertRedirect('/login');
});

it('admin cannot open customer cabinet', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($admin)
        ->get(route('customer.dashboard'))
        ->assertForbidden();
});
