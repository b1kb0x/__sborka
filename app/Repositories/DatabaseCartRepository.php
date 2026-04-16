<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Repositories\Contracts\CartRepository;
use Illuminate\Support\Facades\Auth;

class DatabaseCartRepository implements CartRepository
{
    public function all(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return CartItem::query()
            ->where('user_id', $user->id)
            ->get()
            ->mapWithKeys(function (CartItem $item) {
                return [
                    $item->row_id => [
                        'row_id' => $item->row_id,
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'price' => (int) $item->price,
                        'qty' => (int) $item->qty,
                        'grind_type' => $item->grind_type,
                    ],
                ];
            })
            ->toArray();
    }

    public function put(array $items): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();

        foreach ($items as $item) {
            CartItem::query()->create([
                'user_id' => $user->id,
                'row_id' => $item['row_id'],
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'grind_type' => $item['grind_type'],
            ]);
        }
    }

    public function clear(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();
    }
}
