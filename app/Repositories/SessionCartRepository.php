<?php

namespace App\Repositories;

use App\Repositories\Contracts\CartRepository;

class SessionCartRepository implements CartRepository
{
    public function all(): array
    {
        return session()->get('cart', []);
    }

    public function put(array $items): void
    {
        session()->put('cart', $items);
    }

    public function clear(): void
    {
        session()->forget('cart');
    }
}
