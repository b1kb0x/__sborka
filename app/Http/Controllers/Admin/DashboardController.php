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
            ->where('status', 'new') // замени на твой реальный статус
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard.index', [
            'newOrders' => $newOrders,
        ]);
    }
}
