<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductImageService;

class ProductObserver
{
    public function deleted(Product $product): void
    {
        app(ProductImageService::class)->deleteForProduct($product);
    }
}
