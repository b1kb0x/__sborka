<?php

namespace App\Data;

use App\Enums\GrindType;

final readonly class CartItemData
{
    public function __construct(
        public string $rowId,
        public int $productId,
        public string $name,
        public int $price,
        public int $qty,
        public GrindType $grindType,
    ) {}

    public static function fromArray(array $data): self
    {
        $productId = (int) ($data['product_id'] ?? 0);
        $grindTypeValue = (string) ($data['grind_type'] ?? 'beans');

        return new self(
            rowId: (string) ($data['row_id'] ?? ($productId . '_' . $grindTypeValue)),
            productId: $productId,
            name: (string) ($data['name'] ?? ''),
            price: (int) ($data['price'] ?? 0),
            qty: (int) ($data['qty'] ?? 0),
            grindType: GrindType::from($grindTypeValue),
        );
    }

    public function toArray(): array
    {
        return [
            'row_id' => $this->rowId,
            'product_id' => $this->productId,
            'name' => $this->name,
            'price' => $this->price,
            'qty' => $this->qty,
            'grind_type' => $this->grindType->value,
        ];
    }

    public function grindLabel(): string
    {
        return $this->grindType->label();
    }

    public function lineTotal(): int
    {
        return $this->price * $this->qty;
    }
}
