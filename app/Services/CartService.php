<?php

namespace App\Services;

use App\Data\Cart;
use App\Data\CartItemData;
use App\Enums\GrindType;
use App\Models\Product;
use App\Repositories\Contracts\CartRepository;
use App\Repositories\DatabaseCartRepository;
use App\Repositories\SessionCartRepository;
use DomainException;

class CartService
{
    public function __construct(
        protected CartCalculator $calculator,
        protected CartRepository $repository,
        protected SessionCartRepository $sessionRepository,
        protected DatabaseCartRepository $databaseRepository,
    ) {}

    public function all(): array
    {
        return $this->repository->all();
    }

    public function cart(): Cart
    {
        $cart = $this->all();

        return new Cart(
            items: $this->mapItems($cart),
            subtotal: $this->calculator->subtotal($cart),
            count: $this->calculator->count($cart),
        );
    }

    public function items(): array
    {
        return $this->mapItems($this->all());
    }

    private function mapItems(array $cart): array
    {
        $items = [];

        foreach ($cart as $item) {
            $items[] = CartItemData::fromArray($item);
        }

        return $items;
    }

    public function get(string $rowId): ?CartItemData
    {
        $cart = $this->all();

        if (! isset($cart[$rowId])) {
            return null;
        }

        return CartItemData::fromArray($cart[$rowId]);
    }

    public function add(int $productId, GrindType $grindType, int $qty = 1): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->ensureProductCanBeAdded($product, $qty);

        $rowId = $this->makeRowId($productId, $grindType);
        $cart = $this->all();

        if (isset($cart[$rowId])) {
            $existingItem = CartItemData::fromArray($cart[$rowId]);
            $newQty = $existingItem->qty + $qty;

            if ($product->stock < $newQty) {
                throw new DomainException('Нельзя добавить больше товара, чем есть в наличии.');
            }

            $updatedItem = new CartItemData(
                rowId: $existingItem->rowId,
                productId: $existingItem->productId,
                name: $existingItem->name,
                price: $existingItem->price,
                qty: $newQty,
                grindType: $existingItem->grindType,
            );

            $cart[$rowId] = $updatedItem->toArray();
        } else {
            $cart[$rowId] = $this->makeItem($product, $grindType, $qty)->toArray();
        }

        $this->repository->put($cart);
    }

    public function updateQty(string $rowId, int $qty): void
    {
        $cart = $this->all();

        if (! isset($cart[$rowId])) {
            return;
        }

        $item = CartItemData::fromArray($cart[$rowId]);
        $product = Product::query()->findOrFail($item->productId);

        $this->ensureProductCanBeAdded($product, $qty);

        $updatedItem = new CartItemData(
            rowId: $item->rowId,
            productId: $item->productId,
            name: $item->name,
            price: $item->price,
            qty: $qty,
            grindType: $item->grindType,
        );

        $cart[$rowId] = $updatedItem->toArray();

        $this->repository->put($cart);
    }

    public function remove(string $rowId): void
    {
        $cart = $this->all();

        unset($cart[$rowId]);

        $this->repository->put($cart);
    }

    public function clear(): void
    {
        $this->repository->clear();
    }

    private function makeItem(Product $product, GrindType $grindType, int $qty): CartItemData
    {
        return new CartItemData(
            rowId: $this->makeRowId($product->id, $grindType),
            productId: $product->id,
            name: $product->title,
            price: (int) $product->price,
            qty: $qty,
            grindType: $grindType,
        );
    }

    private function makeRowId(int $productId, GrindType $grindType): string
    {
        return $productId . '_' . $grindType->value;
    }

    public function mergeGuestCartIntoUserCart(): void
    {
        if (! auth()->check()) {
            return;
        }

        $guestCart = $this->sessionRepository->all();

        if (empty($guestCart)) {
            return;
        }

        $userCart = $this->databaseRepository->all();

        foreach ($guestCart as $rowId => $guestItemArray) {
            $guestItem = CartItemData::fromArray($guestItemArray);
            $product = Product::query()->find($guestItem->productId);

            if (! $product || ! $product->is_active) {
                continue;
            }

            if (isset($userCart[$rowId])) {
                $existing = CartItemData::fromArray($userCart[$rowId]);
                $newQty = min($existing->qty + $guestItem->qty, (int) $product->stock);

                $userCart[$rowId] = (new CartItemData(
                    rowId: $existing->rowId,
                    productId: $existing->productId,
                    name: $existing->name,
                    price: (int) $product->price,
                    qty: $newQty,
                    grindType: $existing->grindType,
                ))->toArray();
            } else {
                $qty = min($guestItem->qty, (int) $product->stock);

                if ($qty < 1) {
                    continue;
                }

                $userCart[$rowId] = (new CartItemData(
                    rowId: $guestItem->rowId,
                    productId: $guestItem->productId,
                    name: $product->title,
                    price: (int) $product->price,
                    qty: $qty,
                    grindType: $guestItem->grindType,
                ))->toArray();
            }
        }

        $this->databaseRepository->put($userCart);
        $this->sessionRepository->clear();
    }

    private function ensureProductCanBeAdded(Product $product, int $qty): void
    {
        if (! $product->is_active) {
            throw new DomainException('Товар недоступен.');
        }

        if ($qty < 1) {
            throw new DomainException('Количество должно быть не меньше 1.');
        }

        if ($product->stock < $qty) {
            throw new DomainException('Недостаточно товара в наличии.');
        }
    }

    public function refresh(): array
    {
        $cart = $this->all();
        $messages = [];

        if (empty($cart)) {
            return $messages;
        }

        foreach ($cart as $rowId => $itemArray) {
            $cartItem = CartItemData::fromArray($itemArray);
            $product = Product::query()->find($cartItem->productId);

            if (! $product) {
                unset($cart[$rowId]);
                $messages[] = "Товар \"{$cartItem->name}\" удален из корзины, потому что больше не существует.";
                continue;
            }

            if (! $product->is_active) {
                unset($cart[$rowId]);
                $messages[] = "Товар \"{$cartItem->name}\" удален из корзины, потому что недоступен.";
                continue;
            }

            if ($product->stock < 1) {
                unset($cart[$rowId]);
                $messages[] = "Товар \"{$cartItem->name}\" удален из корзины, потому что закончился.";
                continue;
            }

            $updatedQty = min($cartItem->qty, (int) $product->stock);
            $updatedPrice = (int) $product->price;

            if ($updatedQty !== $cartItem->qty) {
                $messages[] = "Количество товара \"{$cartItem->name}\" уменьшено до {$updatedQty}.";
            }

            if ($updatedPrice !== $cartItem->price) {
                $messages[] = "Цена товара \"{$cartItem->name}\" была обновлена.";
            }

            $cart[$rowId] = (new CartItemData(
                rowId: $cartItem->rowId,
                productId: $product->id,
                name: $product->title,
                price: $updatedPrice,
                qty: $updatedQty,
                grindType: $cartItem->grindType,
            ))->toArray();
        }

        $this->repository->put($cart);

        return $messages;
    }

    public function validateForCheckout(): array
    {
        $errors = [];
        $cart = $this->all();

        if (empty($cart)) {
            $errors[] = 'Корзина пуста.';
            return $errors;
        }

        foreach ($cart as $itemArray) {
            $cartItem = CartItemData::fromArray($itemArray);
            $product = Product::query()->find($cartItem->productId);

            if (! $product) {
                $errors[] = "Товар \"{$cartItem->name}\" больше не существует.";
                continue;
            }

            if (! $product->is_active) {
                $errors[] = "Товар \"{$cartItem->name}\" недоступен.";
                continue;
            }

            if ($product->stock < $cartItem->qty) {
                $errors[] = "Недостаточно товара \"{$cartItem->name}\" в наличии.";
            }
        }

        return $errors;
    }

    public function isReadyForCheckout(): bool
    {
        return empty($this->validateForCheckout());
    }
}
