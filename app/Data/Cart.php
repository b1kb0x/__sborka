<?php

namespace App\Data;

final readonly class Cart
{
    /**
     * @param array<CartItemData> $items
     */
    public function __construct(
        public array $items,
        public int $subtotal,
        public int $count,
    ) {}
}
