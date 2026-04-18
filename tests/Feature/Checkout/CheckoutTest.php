<?php

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function checkoutProduct(array $attributes = []): Product
{
    $uuid = (string) Str::uuid();

    return Product::query()->create(array_merge([
        'title' => 'Checkout product '.$uuid,
        'slug' => 'checkout-product-'.Str::lower($uuid),
        'short_description' => 'Checkout product',
        'description' => 'Checkout product description',
        'price' => 499,
        'stock' => 10,
        'is_active' => true,
    ], $attributes));
}

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+380000000000',
        'email' => 'john-'.Str::uuid().'@example.test',
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Main street 1',
        'comment' => 'Leave at the door',
    ], $overrides);
}

function addItemToCart(\Illuminate\Foundation\Testing\TestCase $test, Product $product, int $qty = 1): void
{
    $test->post('/cart/add', [
        'product_id' => $product->id,
        'qty' => $qty,
        'grind_type' => 'beans',
    ])->assertRedirect(route('cart.index'));
}

it('auth user sees checkout with prefilled fields from database', function () {
    $user = User::factory()->create([
        'name' => 'John Profile',
        'first_name' => 'John',
        'last_name' => 'Profile',
        'email' => 'profile@example.test',
        'phone' => '+380111111111',
        'region' => 'Kyiv region',
        'city' => 'Kyiv',
        'address' => 'Profile street 10',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();

    $this->actingAs($user);
    addItemToCart($this, $product);

    $this->get(route('checkout.create'))
        ->assertOk()
        ->assertViewHas('checkoutData', fn (array $data) => $data['first_name'] === 'John'
            && $data['last_name'] === 'Profile'
            && $data['email'] === 'profile@example.test'
            && $data['phone'] === '+380111111111'
            && $data['region'] === 'Kyiv region'
            && $data['city'] === 'Kyiv'
            && $data['address'] === 'Profile street 10');
});

it('admin cannot open checkout page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();

    $this->actingAs($admin);
    addItemToCart($this, $product);

    $this->get(route('checkout.create'))
        ->assertRedirect(route('cart.index'));
});

it('admin cannot submit checkout order', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();

    $this->actingAs($admin);
    addItemToCart($this, $product);

    $this->post(route('cart.checkout'), checkoutPayload([
        'email' => 'admin-checkout@example.test',
    ]))->assertForbidden();

    expect(Order::query()->count())->toBe(0);
});

it('guest can open checkout page when guest checkout is enabled', function () {
    app(SettingsService::class)->set('checkout.guest_checkout_enabled', true);

    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->get(route('checkout.create'))
        ->assertOk();
});

it('guest is redirected to login when guest checkout is disabled', function () {
    app(SettingsService::class)->set('checkout.guest_checkout_enabled', false);

    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->get(route('checkout.create'))
        ->assertRedirect(route('login'));
});

it('guest cannot submit checkout when guest checkout is disabled', function () {
    app(SettingsService::class)->set('checkout.guest_checkout_enabled', false);

    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->post(route('cart.checkout'), checkoutPayload())
        ->assertRedirect(route('login'));

    expect(Order::query()->count())->toBe(0);
    $this->assertGuest();
});

it('auth user successfully places order even when guest checkout is disabled, keeps success flow, and does not see account-created message', function () {
    app(SettingsService::class)->set('checkout.guest_checkout_enabled', false);

    $user = User::factory()->create([
        'name' => 'Jane Saved',
        'first_name' => 'Jane',
        'last_name' => 'Saved',
        'email' => 'saved@example.test',
        'phone' => '+380222222222',
        'region' => 'Lviv region',
        'city' => 'Lviv',
        'address' => 'Saved street 2',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();
    $payload = checkoutPayload([
        'first_name' => 'Order',
        'last_name' => 'Snapshot',
        'phone' => '+380999999999',
        'email' => $user->email,
        'region' => 'Odesa region',
        'city' => 'Odesa',
        'address' => 'Order street 9',
        'comment' => 'Call me first',
    ]);

    $this->actingAs($user);
    addItemToCart($this, $product, 2);

    $this->followingRedirects()
        ->post(route('cart.checkout'), $payload)
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertSee('Your order #')
        ->assertDontSee('We created an account for you')
        ->assertSee('View order')
        ->assertSee(route('customer.orders.show', $order = Order::query()->latest('id')->first()), false);

    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);
    expect($order->status)->toBe(OrderStatus::New);
    expect($order->fulfillment_status)->toBe(FulfillmentStatus::Accepted);
    expect($order->first_name)->toBe('Order');
    expect($order->last_name)->toBe('Snapshot');
    expect($order->phone)->toBe('+380999999999');
    expect($order->email)->toBe($user->email);
    expect($order->region)->toBe('Odesa region');
    expect($order->city)->toBe('Odesa');
    expect($order->address)->toBe('Order street 9');
    expect($order->comment)->toBe('Call me first');
    expect((int) $product->fresh()->stock)->toBe(8);

    $user->refresh();

    expect($user->first_name)->toBe('Jane');
    expect($user->last_name)->toBe('Saved');
    expect($user->phone)->toBe('+380222222222');
    expect($user->region)->toBe('Lviv region');
    expect($user->city)->toBe('Lviv');
    expect($user->address)->toBe('Saved street 2');
});

it('guest with new email creates order, account, login, password setup notification, and account-created success message', function () {
    Notification::fake();

    $product = checkoutProduct();
    $payload = checkoutPayload();

    addItemToCart($this, $product, 2);

    $this->followingRedirects()
        ->post(route('cart.checkout'), $payload)
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertSee('We created an account for you and sent an email')
        ->assertSee($payload['email']);

    $user = User::query()->where('email', $payload['email'])->first();
    $order = Order::query()->latest('id')->first();

    expect($user)->not->toBeNull();
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);
    expect($order->email)->toBe($payload['email']);

    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('guest with existing email creates order without creating user, login, notification, or account-created message', function () {
    Notification::fake();

    $existingUser = User::factory()->create([
        'name' => 'Existing User',
        'first_name' => 'Existing',
        'last_name' => 'User',
        'email' => 'existing@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();
    $payload = checkoutPayload([
        'email' => $existingUser->email,
    ]);

    addItemToCart($this, $product);

    $this->followingRedirects()
        ->post(route('cart.checkout'), $payload)
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertSee('Your order #')
        ->assertDontSee('We created an account for you');

    expect(User::query()->where('email', $existingUser->email)->count())->toBe(1);

    $order = Order::query()->latest('id')->first();

    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($existingUser->id);
    expect($order->email)->toBe($existingUser->email);

    $this->assertGuest();

    Notification::assertNothingSent();
});

it('guest checkout normalizes email and reuses existing user without duplicates', function () {
    Notification::fake();

    $existingUser = User::factory()->create([
        'email' => 'normalized@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Active,
    ]);

    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->followingRedirects()
        ->post(route('cart.checkout'), checkoutPayload([
            'email' => '  NORMALIZED@EXAMPLE.TEST  ',
        ]))
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertDontSee('We created an account for you');

    $order = Order::query()->latest('id')->first();

    expect(User::query()->where('email', 'normalized@example.test')->count())->toBe(1);
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($existingUser->id);
    expect($order->email)->toBe('normalized@example.test');

    $this->assertGuest();

    Notification::assertNothingSent();
});

it('guest checkout reuses blocked user without creating a duplicate or logging in', function () {
    Notification::fake();

    $blockedUser = User::factory()->create([
        'email' => 'blocked@example.test',
        'role' => UserRole::Customer,
        'status' => UserStatus::Blocked,
    ]);

    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->followingRedirects()
        ->post(route('cart.checkout'), checkoutPayload([
            'email' => '  BLOCKED@EXAMPLE.TEST  ',
        ]))
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertDontSee('We created an account for you');

    $order = Order::query()->latest('id')->first();

    expect(User::query()->where('email', 'blocked@example.test')->count())->toBe(1);
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($blockedUser->id);
    expect($order->email)->toBe('blocked@example.test');

    $this->assertGuest();

    Notification::assertNothingSent();
});

it('keeps checkout success flow when password setup email sending fails', function () {
    Password::shouldReceive('broker->sendResetLink')
        ->once()
        ->andThrow(new RuntimeException('Mail transport failed'));

    $product = checkoutProduct();
    $payload = checkoutPayload([
        'email' => 'mail-failure-'.Str::uuid().'@example.test',
    ]);

    $this->followingRedirects()
        ->post('/cart/add', [
            'product_id' => $product->id,
            'qty' => 1,
            'grind_type' => 'beans',
        ])
        ->assertOk();

    $this->followingRedirects()
        ->post(route('cart.checkout'), $payload)
        ->assertOk()
        ->assertSee('Order placed.')
        ->assertSee('We created an account for you, but we could not send the password setup email right now.')
        ->assertSee('Your order #');

    $user = User::query()->where('email', $payload['email'])->first();
    $order = Order::query()->latest('id')->first();

    expect($user)->not->toBeNull();
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);

    $this->assertAuthenticatedAs($user);
});

it('does not allow checkout with an empty cart', function () {
    $payload = checkoutPayload();

    $this->post(route('cart.checkout'), $payload)
        ->assertRedirect(route('checkout.create'));

    expect(Order::query()->count())->toBe(0);
});

it('rejects invalid checkout fields', function () {
    $product = checkoutProduct();

    addItemToCart($this, $product);

    $this->post(route('cart.checkout'), [
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'email' => 'not-an-email',
        'region' => '',
        'city' => '',
        'address' => '',
    ])->assertSessionHasErrors([
        'first_name',
        'last_name',
        'phone',
        'email',
        'region',
        'city',
        'address',
    ]);

    expect(Order::query()->count())->toBe(0);
});

it('handles stock shortage during checkout', function () {
    $product = checkoutProduct([
        'stock' => 2,
    ]);

    addItemToCart($this, $product, 2);

    $product->update([
        'stock' => 1,
    ]);

    $this->post(route('cart.checkout'), checkoutPayload())
        ->assertRedirect(route('checkout.create'));

    expect(Order::query()->count())->toBe(0);
    expect((int) $product->fresh()->stock)->toBe(1);
});
