<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerCabinetController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders()->latest()->get();

        return view('customer.cabinet.dashboard', [
            'user' => $user,
            'ordersCount' => $orders->count(),
            'latestOrder' => $orders->first(),
        ]);
    }

    public function orders(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.product');

        return view('customer.orders.show', [
            'order' => $order,
        ]);
    }

    public function editProfile(Request $request): View
    {
        return view('customer.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'name' => trim($request->string('first_name')->value().' '.$request->string('last_name')->value()),
            'first_name' => $request->string('first_name')->value(),
            'last_name' => $request->string('last_name')->value(),
            'phone' => $request->string('phone')->value(),
            'email' => $request->string('email')->value(),
            'region' => $request->string('region')->value(),
            'city' => $request->string('city')->value(),
            'address' => $request->string('address')->value(),
        ])->save();

        return redirect()
            ->route('customer.profile.edit')
            ->with('status', 'profile-updated');
    }
}
