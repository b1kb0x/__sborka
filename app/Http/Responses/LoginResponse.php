<?php

namespace App\Http\Responses;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function toResponse($request)
    {
        $this->cartService->mergeGuestCartIntoUserCart();

        $user = $request->user();
        $fallback = $user && $user->isAdmin()
            ? route('admin.dashboard')
            : route('customer.dashboard');

        if ($request->wantsJson()) {
            return new JsonResponse(['redirect' => $fallback], 200);
        }

        return redirect()->intended($fallback);
    }
}
