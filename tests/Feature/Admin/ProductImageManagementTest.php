<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createAdminUser(): User
{
    return User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);
}

function createProduct(): Product
{
    $suffix = (string) Str::uuid();

    return Product::query()->create([
        'title' => 'Image Test Product '.$suffix,
        'slug' => 'image-test-product-'.Str::lower($suffix),
        'short_description' => 'Product for image tests',
        'description' => 'Product for image tests',
        'price' => 499,
        'stock' => 10,
        'is_active' => true,
    ]);
}

it('upload image creates db row and stores three files', function () {
    Storage::fake('public');

    $admin = createAdminUser();
    $product = createProduct();
    $file = UploadedFile::fake()->image('coffee.jpg', 1200, 1200);

    $this->actingAs($admin)
        ->post(route('admin.products.image.store', $product), [
            'image' => $file,
            'alt' => 'Coffee alt',
        ])
        ->assertRedirect();

    $image = ProductImage::query()->where('product_id', $product->id)->first();

    expect($image)->not->toBeNull();
    expect($image->file_name)->not->toBeEmpty();
    expect($image->alt)->toBe('Coffee alt');
    expect($image->is_primary)->toBeTrue();

    Storage::disk('public')->assertExists($image->path('thumbnail'));
    Storage::disk('public')->assertExists($image->path('preview'));
    Storage::disk('public')->assertExists($image->path('original'));
});

it('replace image deletes old three files and stores new three files', function () {
    Storage::fake('public');

    $admin = createAdminUser();
    $product = createProduct();

    $this->actingAs($admin)->post(route('admin.products.image.store', $product), [
        'image' => UploadedFile::fake()->image('first.jpg', 1200, 1200),
        'alt' => 'First alt',
    ])->assertRedirect();

    $oldImage = $product->fresh()->primaryImage;
    $oldPaths = [
        $oldImage->path('thumbnail'),
        $oldImage->path('preview'),
        $oldImage->path('original'),
    ];

    $this->actingAs($admin)->put(route('admin.products.image.replace', $product), [
        'image' => UploadedFile::fake()->image('second.jpg', 1200, 1200),
        'alt' => 'Second alt',
    ])->assertRedirect();

    $newImage = $product->fresh()->primaryImage;

    expect($newImage)->not->toBeNull();
    expect($newImage->id)->toBe($oldImage->id);
    expect($newImage->file_name)->not->toBe($oldImage->file_name);
    expect($newImage->alt)->toBe('Second alt');

    foreach ($oldPaths as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    Storage::disk('public')->assertExists($newImage->path('thumbnail'));
    Storage::disk('public')->assertExists($newImage->path('preview'));
    Storage::disk('public')->assertExists($newImage->path('original'));
});

it('manual delete removes db row and all three files', function () {
    Storage::fake('public');

    $admin = createAdminUser();
    $product = createProduct();

    $this->actingAs($admin)->post(route('admin.products.image.store', $product), [
        'image' => UploadedFile::fake()->image('coffee.jpg', 1200, 1200),
        'alt' => 'Coffee alt',
    ])->assertRedirect();

    $image = $product->fresh()->primaryImage;

    $paths = [
        $image->path('thumbnail'),
        $image->path('preview'),
        $image->path('original'),
    ];

    $this->actingAs($admin)
        ->delete(route('admin.products.image.destroy', $product))
        ->assertRedirect();

    expect(ProductImage::query()->whereKey($image->id)->exists())->toBeFalse();

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

it('deleting product removes all related image files', function () {
    Storage::fake('public');

    $product = createProduct();

    $image = ProductImage::query()->create([
        'product_id' => $product->id,
        'file_name' => 'test-image.webp',
        'alt' => 'Test image',
        'is_primary' => true,
        'sort_order' => 0,
    ]);

    Storage::disk('public')->put($image->path('thumbnail'), 'thumb');
    Storage::disk('public')->put($image->path('preview'), 'preview');
    Storage::disk('public')->put($image->path('original'), 'original');

    $product->delete();

    Storage::disk('public')->assertMissing($image->path('thumbnail'));
    Storage::disk('public')->assertMissing($image->path('preview'));
    Storage::disk('public')->assertMissing($image->path('original'));
});