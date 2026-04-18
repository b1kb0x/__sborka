<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $newOrders = Order::query()
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'newOrders' => $newOrders,
        ]);
    }
}
