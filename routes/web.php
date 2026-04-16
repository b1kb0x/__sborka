<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductAttributeController as AdminProductAttributeController;
use App\Http\Controllers\Admin\ProductAttributeOptionController as AdminProductAttributeOptionController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\CartController;
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
    Route::view('/account/profile', 'account.profile')->name('account.profile');

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
        Route::get('customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('customers/{customer}/restore', [AdminCustomerController::class, 'restore'])->name('customers.restore');

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

        Route::post('/admin/products/{product}/image', [ProductController::class, 'uploadImage'])->name('admin.products.image.store');
        Route::put('/admin/products/{product}/image', [ProductController::class, 'replaceImage'])->name('admin.products.image.replace');
        Route::delete('/admin/products/{product}/image', [ProductController::class, 'deleteImage'])->name('admin.products.image.destroy');
    });
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update/{rowId}', [CartController::class, 'updateQty'])->name('cart.update');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::middleware(['auth'])->group(function () {
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

Route::middleware(['auth', 'active.user', 'customer'])->group(function () {
    Route::get('/cabinet', function () {
        return view('customer.dashboard', ['user' => Auth::user()]);
    })->name('customer.dashboard');
});
