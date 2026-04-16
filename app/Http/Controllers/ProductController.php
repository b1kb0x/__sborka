<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->active()
            ->latest()
            ->get();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'attributeValues.attribute',
                'attributeValues.option',
            ])
            ->firstOrFail();

        return view('products.show', [
            'product' => $product,
        ]);
    }
}
