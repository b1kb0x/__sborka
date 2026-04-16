<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductAttributeOptionRequest;
use App\Http\Requests\UpdateProductAttributeOptionRequest;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductAttributeOptionController extends Controller
{
    public function index(ProductAttribute $productAttribute): View|RedirectResponse
    {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        $options = $productAttribute->options()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.product-attributes.options.index', [
            'productAttribute' => $productAttribute,
            'options' => $options,
        ]);
    }

    public function create(ProductAttribute $productAttribute): View|RedirectResponse
    {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.product-attributes.options.create', [
            'productAttribute' => $productAttribute,
        ]);
    }

    public function store(
        StoreProductAttributeOptionRequest $request,
        ProductAttribute $productAttribute
    ): RedirectResponse {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        $productAttribute->options()->create([
            'value' => $request->validated('value'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return redirect()
            ->route('admin.product-attributes.options.index', $productAttribute)
            ->with('success', 'Опция создана.');
    }

    public function edit(
        ProductAttribute $productAttribute,
        ProductAttributeOption $option
    ): View|RedirectResponse {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        $this->ensureBelongsToAttribute($productAttribute, $option);

        return view('admin.product-attributes.options.edit', [
            'productAttribute' => $productAttribute,
            'option' => $option,
        ]);
    }

    public function update(
        UpdateProductAttributeOptionRequest $request,
        ProductAttribute $productAttribute,
        ProductAttributeOption $option
    ): RedirectResponse {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        $this->ensureBelongsToAttribute($productAttribute, $option);

        $option->update([
            'value' => $request->validated('value'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return redirect()
            ->route('admin.product-attributes.options.index', $productAttribute)
            ->with('success', 'Опция обновлена.');
    }

    public function destroy(
        ProductAttribute $productAttribute,
        ProductAttributeOption $option
    ): RedirectResponse {
        $redirect = $this->ensureSelectable($productAttribute);
        if ($redirect) {
            return $redirect;
        }

        $this->ensureBelongsToAttribute($productAttribute, $option);

        if ($option->values()->exists()) {
            return redirect()
                ->route('admin.product-attributes.options.index', $productAttribute)
                ->with('error', 'Нельзя удалить опцию, потому что она уже используется у товаров.');
        }

        $option->delete();

        return redirect()
            ->route('admin.product-attributes.options.index', $productAttribute)
            ->with('success', 'Опция удалена.');
    }

    protected function ensureSelectable(ProductAttribute $productAttribute): ?RedirectResponse
    {
        if ($productAttribute->type !== 'select') {
            return redirect()
                ->route('admin.product-attributes.index')
                ->with('error', 'Опции доступны только для характеристик типа select.');
        }

        return null;
    }

    protected function ensureBelongsToAttribute(
        ProductAttribute $productAttribute,
        ProductAttributeOption $option
    ): void {
        abort_unless($option->product_attribute_id === $productAttribute->id, 404);
    }
}
