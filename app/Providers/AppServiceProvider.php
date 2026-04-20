<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Repositories\Contracts\CartRepository;
use App\Repositories\HybridCartRepository;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CartRepository::class, HybridCartRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Paginator::useBootstrapFive();
        View::composer('admin.components.sidebar', function ($view): void {
            $newOrdersCount = Order::query()
                ->where('status', OrderStatus::New->value)
                ->count();

            $view->with('newOrdersCount', $newOrdersCount);
        });
        View::composer('components.nav', function ($view) {
            $cartCount = app(CartService::class)->count();

            $view->with('cartCount', $cartCount);
        });
    }
}
