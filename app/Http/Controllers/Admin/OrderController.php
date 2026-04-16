<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function edit(Order $order): View
    {
        $order->load(['user', 'items.product']);

        return view('admin.orders.edit', [
            'order' => $order,
            'orderStatuses' => OrderStatus::cases(),
            'fulfillmentStatuses' => FulfillmentStatus::cases(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'fulfillment_status' => ['required', 'string'],
            'carrier_name' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $orderStatus = OrderStatus::from($validated['status']);
        $fulfillmentStatus = FulfillmentStatus::from($validated['fulfillment_status']);

        $order->status = $orderStatus;
        $order->fulfillment_status = $fulfillmentStatus;
        $order->carrier_name = $validated['carrier_name'] ?: null;
        $order->tracking_number = $validated['tracking_number'] ?: null;

        if ($fulfillmentStatus === FulfillmentStatus::HandedToCarrier && ! $order->handed_to_carrier_at) {
            $order->handed_to_carrier_at = now();
        }

        if ($fulfillmentStatus === FulfillmentStatus::Delivered && ! $order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Заказ обновлён.');
    }
}
