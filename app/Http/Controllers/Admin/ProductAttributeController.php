<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductAttributeRequest;
use App\Http\Requests\UpdateProductAttributeRequest;
use App\Models\ProductAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function index(): View
    {
        $attributes = ProductAttribute::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(8);

        return view('admin.product-attributes.index', [
            'attributes' => $attributes,
        ]);
    }

    public function create(): View
    {
        return view('admin.product-attributes.create');
    }

    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);

        ProductAttribute::create($data);

        return redirect()
            ->route('admin.product-attributes.index')
            ->with('success', 'Характеристика создана.');
    }

    public function edit(ProductAttribute $productAttribute): View
    {
        return view('admin.product-attributes.edit', [
            'productAttribute' => $productAttribute,
        ]);
    }

    public function update(UpdateProductAttributeRequest $request, ProductAttribute $productAttribute): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);

        $productAttribute->update($data);

        return redirect()
            ->route('admin.product-attributes.index')
            ->with('success', 'Характеристика обновлена.');
    }

    public function destroy(ProductAttribute $productAttribute): RedirectResponse
    {
        if ($productAttribute->values()->exists()) {
            return redirect()
                ->route('admin.product-attributes.index')
                ->with('error', 'Нельзя удалить характеристику, потому что она уже используется у товаров.');
        }

        $productAttribute->delete();

        return redirect()
            ->route('admin.product-attributes.index')
            ->with('success', 'Характеристика удалена.');
    }
}
