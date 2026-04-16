<?php

namespace App\Services;

class CartCalculator
{
    public function subtotal(array $items): int
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        return $subtotal;
    }

    public function count(array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $count += $item['qty'];
        }

        return $count;
    }
}
