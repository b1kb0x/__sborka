<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DomainException;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function createFromCart(): Order
    {
        $this->cartService->refresh();

        $errors = $this->cartService->validateForCheckout();

        if (! empty($errors)) {
            throw new DomainException(implode(' ', $errors));
        }

        $cart = $this->cartService->cart();

        if (count($cart->items) === 0) {
            throw new DomainException('Корзина пуста.');
        }

        return DB::transaction(function () use ($cart) {
            $order = Order::query()->create([
                'user_id' => Auth::id(),
                'subtotal' => $cart->subtotal,
                'total' => $cart->subtotal,
                'status' => OrderStatus::New,
                'fulfillment_status' => \App\Enums\FulfillmentStatus::Accepted,
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

            return $order;
        });
    }
}
