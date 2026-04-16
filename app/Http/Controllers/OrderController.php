<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless(auth()->check() && $order->user_id === Auth::id(), 403);

        $order->load(['items.product']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }
}
