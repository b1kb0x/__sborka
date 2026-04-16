<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductAttributeController as AdminProductAttributeController;
use App\Http\Controllers\Admin\ProductAttributeOptionController as AdminProductAttributeOptionController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerCabinetController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/account/profile', function () {
        if (auth()->user()?->isCustomer()) {
            return redirect()->route('customer.profile.edit');
        }

        return view('account.profile');
    })->name('account.profile');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'active.user', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard.index', ['user' => Auth::user()]);
    })->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('product-attributes', AdminProductAttributeController::class)->except(['show']);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'edit', 'update']);

        Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::put('customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');

        Route::prefix('product-attributes/{productAttribute}/options')
            ->name('product-attributes.options.')
            ->group(function () {
                Route::get('/', [AdminProductAttributeOptionController::class, 'index'])->name('index');
                Route::get('/create', [AdminProductAttributeOptionController::class, 'create'])->name('create');
                Route::post('/', [AdminProductAttributeOptionController::class, 'store'])->name('store');
                Route::get('/{option}/edit', [AdminProductAttributeOptionController::class, 'edit'])->name('edit');
                Route::put('/{option}', [AdminProductAttributeOptionController::class, 'update'])->name('update');
                Route::delete('/{option}', [AdminProductAttributeOptionController::class, 'destroy'])->name('destroy');
            });

        Route::post('products/{product}/image', [AdminProductController::class, 'uploadImage'])->name('products.image.store');
        Route::put('products/{product}/image', [AdminProductController::class, 'replaceImage'])->name('products.image.replace');
        Route::delete('products/{product}/image', [AdminProductController::class, 'deleteImage'])->name('products.image.destroy');
    });
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update/{rowId}', [CartController::class, 'updateQty'])->name('cart.update');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/checkout', [CartController::class, 'showCheckout'])->name('checkout.create');
Route::get('/checkout/success', [CartController::class, 'success'])->name('checkout.success');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

Route::middleware(['auth', 'active.user', 'customer'])->group(function () {
    Route::prefix('cabinet')->name('customer.')->group(function () {
        Route::get('/', [CustomerCabinetController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerCabinetController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{order}', [CustomerCabinetController::class, 'showOrder'])->name('orders.show');
        Route::get('/profile', [CustomerCabinetController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [CustomerCabinetController::class, 'updateProfile'])->name('profile.update');
    });
});
