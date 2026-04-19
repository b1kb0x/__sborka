<?php

namespace App\Http\Controllers;

use App\Enums\GrindType;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SettingsService;
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
        protected SettingsService $settingsService,
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

        try {
            $this->cartService->add(
                productId: (int) $validated['product_id'],
                grindType: GrindType::from($validated['grind_type']),
                qty: (int) ($validated['qty'] ?? 1),
            );
        } catch (DomainException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Товар добавлен в корзину.');
    }

    public function updateQty(Request $request, string $rowId): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->cartService->updateQty($rowId, (int) $validated['qty']);
        } catch (DomainException $e) {
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }

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

    public function showCheckout(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Admins cannot place orders.');
        }

        if (! $request->user() && ! $this->settingsService->guestCheckoutEnabled()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please sign in to place an order.');
        }

        $messages = $this->cartService->refresh();
        $cart = $this->cartService->cart();

        if (count($cart->items) === 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Cart is empty.');
        }

        return view('checkout.create', [
            'cart' => $cart,
            'messages' => $messages,
            'checkoutData' => $this->orderService->defaultCheckoutData($request->user()),
        ]);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        if (! $request->user() && ! $this->settingsService->guestCheckoutEnabled()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please sign in to place an order.');
        }

        try {
            $result = $this->orderService->createFromCart($request->validated());

            return redirect()
                ->route('checkout.success')
                ->with('checkout_success', [
                    'order_id' => $result->order->id,
                    'email' => $result->order->email,
                    'account_created' => $result->accountCreated,
                    'reset_link_sent' => $result->resetLinkSent,
                    'authenticated' => (bool) $result->authenticatedUser,
                ])
                ->with('success', 'Order placed.');
        } catch (DomainException $e) {
            return redirect()
                ->route('checkout.create')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function success(Request $request): View|RedirectResponse
    {
        $checkoutSuccess = $request->session()->get('checkout_success');

        if (! $checkoutSuccess) {
            return redirect()->route('cart.index');
        }

        return view('checkout.success', [
            'checkoutSuccess' => $checkoutSuccess,
        ]);
    }
}
