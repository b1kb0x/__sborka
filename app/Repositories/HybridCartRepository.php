<?php

namespace App\Repositories;

use App\Repositories\Contracts\CartRepository;
use Illuminate\Support\Facades\Auth;

class HybridCartRepository implements CartRepository
{
    public function __construct(
        protected SessionCartRepository $sessionRepository,
        protected DatabaseCartRepository $databaseRepository,
    ) {}

    public function all(): array
    {
        return $this->repository()->all();
    }

    public function put(array $items): void
    {
        $this->repository()->put($items);
    }

    public function clear(): void
    {
        $this->repository()->clear();
    }

    protected function repository(): CartRepository
    {
        if (Auth::check()) {
            return $this->databaseRepository;
        }

        return $this->sessionRepository;
    }
}
