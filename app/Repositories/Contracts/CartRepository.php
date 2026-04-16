<?php

namespace App\Repositories\Contracts;

interface CartRepository
{
    public function all(): array;

    public function put(array $items): void;

    public function clear(): void;
}
