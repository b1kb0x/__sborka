<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductImageService;

class ProductObserver
{
    /**
     * @var array<int, array<int, string>>
     */
    protected static array $cleanupPaths = [];

    public function deleting(Product $product): void
    {
        self::$cleanupPaths[spl_object_id($product)] = app(ProductImageService::class)->pathsForProduct($product);
    }

    public function deleted(Product $product): void
    {
        $key = spl_object_id($product);
        $paths = self::$cleanupPaths[$key] ?? [];

        unset(self::$cleanupPaths[$key]);

        app(ProductImageService::class)->deletePaths($paths);
    }
}
