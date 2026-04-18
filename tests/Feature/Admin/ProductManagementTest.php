<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function adminProduct(array $attributes = []): Product
{
    $uuid = (string) Str::uuid();

    return Product::query()->create(array_merge([
        'title' => 'Admin product '.$uuid,
        'slug' => 'admin-product-'.Str::lower($uuid),
        'short_description' => 'Admin product short description',
        'description' => 'Admin product description',
        'price' => 199,
        'stock' => 10,
        'is_active' => true,
    ], $attributes));
}

it('uses products per page from settings in the admin products list', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.products_per_page', 3);

    foreach (range(1, 5) as $index) {
        adminProduct([
            'title' => "Product {$index}",
            'slug' => "product-{$index}-".Str::lower((string) Str::uuid()),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertViewHas('products', fn ($products) => $products->perPage() === 3 && $products->count() === 3);
});

it('falls back to 20 products per page when the setting is invalid', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('admin.products_per_page', 0);

    foreach (range(1, 25) as $index) {
        adminProduct([
            'title' => "Fallback product {$index}",
            'slug' => "fallback-product-{$index}-".Str::lower((string) Str::uuid()),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertViewHas('products', fn ($products) => $products->perPage() === 20 && $products->count() === 20);
});
