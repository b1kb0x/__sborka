<?php

namespace App\Services;

use App\Data\CheckoutResult;
use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use DomainException;
use Throwable;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function defaultCheckoutData(?User $user): array
    {
        if (! $user) {
            return [
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'email' => '',
                'region' => '',
                'city' => '',
                'address' => '',
                'comment' => '',
            ];
        }

        [$fallbackFirstName, $fallbackLastName] = $this->splitName($user->name);

        return [
            'first_name' => $user->first_name ?? $fallbackFirstName,
            'last_name' => $user->last_name ?? $fallbackLastName,
            'phone' => $user->phone ?? '',
            'email' => $user->email,
            'region' => $user->region ?? '',
            'city' => $user->city ?? '',
            'address' => $user->address ?? '',
            'comment' => '',
        ];
    }

    public function createFromCart(array $checkoutData): CheckoutResult
    {
        $checkoutData['email'] = $this->normalizeEmail($checkoutData['email'] ?? '');

        $errors = $this->cartService->validateForCheckout();

        if (! empty($errors)) {
            throw new DomainException(implode(' ', $errors));
        }

        $cart = $this->cartService->cart();

        if (count($cart->items) === 0) {
            throw new DomainException('Корзина пуста.');
        }

        $result = DB::transaction(function () use ($cart, $checkoutData) {
            $authenticatedUser = Auth::user();
            $accountCreated = false;
            $shouldAuthenticate = false;
            $orderUser = $authenticatedUser;
            $orderUserId = $authenticatedUser?->id;

            if (! $authenticatedUser) {
                $existingUser = $this->findUserByEmail($checkoutData['email']);

                if ($existingUser) {
                    $orderUser = $existingUser;
                    $orderUserId = $existingUser->id;
                } else {
                    $orderUser = User::query()->create([
                        'name' => trim($checkoutData['first_name'].' '.$checkoutData['last_name']),
                        'first_name' => $checkoutData['first_name'],
                        'last_name' => $checkoutData['last_name'],
                        'email' => $checkoutData['email'],
                        'phone' => $checkoutData['phone'],
                        'region' => $checkoutData['region'],
                        'city' => $checkoutData['city'],
                        'address' => $checkoutData['address'],
                        'password' => Hash::make(Str::random(40)),
                        'role' => UserRole::Customer,
                        'status' => UserStatus::Active,
                    ]);

                    $orderUserId = $orderUser->id;
                    $accountCreated = true;
                    $shouldAuthenticate = true;
                }
            }

            $order = Order::query()->create([
                'user_id' => $orderUserId,
                'first_name' => $checkoutData['first_name'],
                'last_name' => $checkoutData['last_name'],
                'phone' => $checkoutData['phone'],
                'email' => $checkoutData['email'],
                'region' => $checkoutData['region'],
                'city' => $checkoutData['city'],
                'address' => $checkoutData['address'],
                'comment' => Arr::get($checkoutData, 'comment'),
                'subtotal' => $cart->subtotal,
                'total' => $cart->subtotal,
                'status' => OrderStatus::New,
                'fulfillment_status' => FulfillmentStatus::Accepted,
            ]);

            foreach ($cart->items as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->find($item->productId);

                if (! $product) {
                    throw new DomainException("Товар \"{$item->name}\" больше не существует.");
                }

                if (! $product->is_active) {
                    throw new DomainException("Товар \"{$item->name}\" недоступен.");
                }

                if ($product->stock < $item->qty) {
                    throw new DomainException("Недостаточно товара \"{$item->name}\" на складе.");
                }

                $product->decrement('stock', $item->qty);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->productId,
                    'product_title' => $item->name,
                    'unit_price' => $item->price,
                    'quantity' => $item->qty,
                    'grind_type' => $item->grindType->value,
                ]);
            }

            $this->cartService->clear();

            return new CheckoutResult(
                order: $order,
                authenticatedUser: $shouldAuthenticate ? $orderUser : Auth::user(),
                accountCreated: $accountCreated,
                resetLinkSent: false,
            );
        });

        if ($result->accountCreated && $result->authenticatedUser) {
            Auth::login($result->authenticatedUser);

            try {
                Password::broker()->sendResetLink([
                    'email' => $result->authenticatedUser->email,
                ]);

                return $result->withResetLinkSent(true);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $result;
    }

    protected function findUserByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower($email)])
            ->first();
    }

    protected function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    protected function splitName(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }
}
