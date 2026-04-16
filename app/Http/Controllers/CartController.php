<?php

namespace App\Http\Controllers;

use App\Enums\GrindType;
use App\Services\CartService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
    ) {}

    public function index(): View
    {
        $messages = $this->cartService->refresh();

        return view('cart.index', [
            'cart' => $this->cartService->cart(),
            'messages' => $messages,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'grind_type' => ['required', new Enum(GrindType::class)],
        ]);

        $this->cartService->add(
            productId: (int) $validated['product_id'],
            grindType: GrindType::from($validated['grind_type']),
            qty: (int) ($validated['qty'] ?? 1),
        );

        return redirect()
            ->route('cart.index')
            ->with('success', 'Товар добавлен в корзину.');
    }

    public function updateQty(Request $request, string $rowId): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateQty($rowId, (int) $validated['qty']);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Количество обновлено.');
    }

    public function remove(string $rowId): RedirectResponse
    {
        $this->cartService->remove($rowId);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Товар удалён из корзины.');
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Корзина очищена.');
    }

    public function checkout(): RedirectResponse
    {

        try {
            $order = $this->orderService->createFromCart();

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Заказ оформлен.');
        } catch (DomainException $e) {
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }
    }
}
