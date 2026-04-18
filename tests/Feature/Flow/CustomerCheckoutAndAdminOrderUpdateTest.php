<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('allows a customer to log in, checkout, and an admin to update the order', function () {
    $password = 'pass12345';

    $customer = User::factory()->create([
        'name' => 'Checkout Customer',
        'email' => 'checkout-customer-'.Str::uuid().'@example.test',
        'password' => $password,
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $admin = User::factory()->create([
        'name' => 'Orders Admin',
        'email' => 'orders-admin-'.Str::uuid().'@example.test',
        'password' => $password,
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $product = Product::query()->create([
        'title' => 'Checkout Coffee '.Str::uuid(),
        'slug' => 'checkout-coffee-'.Str::lower((string) Str::uuid()),
        'short_description' => 'Checkout test product',
        'description' => 'Checkout test product description',
        'price' => 499,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->get('/login')
        ->assertOk();

    $this->from('/login')
        ->post('/login', [
            'email' => $customer->email,
            'password' => $password,
        ])
        ->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($customer);

    $this->post('/cart/add', [
        'product_id' => $product->id,
        'qty' => 2,
        'grind_type' => 'beans',
    ])->assertRedirect(route('cart.index'));

    $cartItem = CartItem::query()
        ->where('user_id', $customer->id)
        ->where('product_id', $product->id)
        ->first();

    expect($cartItem)->not->toBeNull();
    expect($cartItem?->qty)->toBe(2);

    $this->get('/checkout')
        ->assertOk();

    $this->post('/cart/checkout', [
        'first_name' => 'Checkout',
        'last_name' => 'Customer',
        'phone' => '+380501112233',
        'email' => $customer->email,
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Flow street 1',
        'comment' => 'Flow test',
    ])
        ->assertRedirect();

    $order = Order::query()
        ->where('user_id', $customer->id)
        ->latest('id')
        ->first();

    expect($order)->not->toBeNull();
    expect($order?->status)->toBe(OrderStatus::New);
    expect($order?->fulfillment_status)->toBe(FulfillmentStatus::Accepted);
    expect($order?->first_name)->toBe('Checkout');
    expect($order?->email)->toBe($customer->email);

    $order->load('items');

    expect($order->items)->toHaveCount(1);
    expect($order->items->first()?->quantity)->toBe(2);
    expect((int) $product->fresh()->stock)->toBe(8);
    expect(CartItem::query()->where('user_id', $customer->id)->count())->toBe(0);

    $this->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();

    $this->get('/login')
        ->assertOk();

    $this->from('/login')
        ->post('/login', [
            'email' => $admin->email,
            'password' => $password,
        ])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);

    $this->put(route('admin.orders.update', $order), [
        'status' => OrderStatus::Paid->value,
        'fulfillment_status' => FulfillmentStatus::HandedToCarrier->value,
        'carrier_name' => 'Nova Poshta',
        'tracking_number' => 'FLOW-TEST-123',
    ])->assertRedirect(route('admin.orders.index'));

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->fulfillment_status)->toBe(FulfillmentStatus::HandedToCarrier);
    expect($order->carrier_name)->toBe('Nova Poshta');
    expect($order->tracking_number)->toBe('FLOW-TEST-123');
    expect($order->handed_to_carrier_at)->not->toBeNull();
});
