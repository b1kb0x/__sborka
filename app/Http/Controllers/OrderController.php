<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(): RedirectResponse
    {
        abort_unless(auth()->user()?->isCustomer(), 403);

        return redirect()->route('customer.orders.index');
    }

    public function show(Order $order): RedirectResponse
    {
        abort_unless(
            auth()->check()
            && auth()->user()?->isCustomer()
            && $order->user_id === Auth::id(),
            403
        );

        return redirect()->route('customer.orders.show', $order);
    }
}
