<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\ProductImageService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(SettingsService $settings): View
    {
        $products = Product::query()
            ->latest()
            ->paginate($settings->adminProductsPerPage());

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $attributes = ProductAttribute::query()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.products.create', [
            'attributes' => $attributes,
        ]);
    }

    public function store(StoreProductRequest $request, ProductImageService $service): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->syncAttributeValues($product, $request->input('attributes', []));

            return $product;
        });

        if ($request->hasFile('image')) {
            $service->uploadPrimary(
                $product,
                $request->file('image'),
                $request->input('alt')
            );
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Товар создан.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'attributeValues.attribute',
            'attributeValues.option',
            'primaryImage',
        ]);

        $attributes = ProductAttribute::query()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.products.edit', [
            'product' => $product,
            'attributes' => $attributes,
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductImageService $service
    ): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['title']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        DB::transaction(function () use ($request, $product, $data): void {
            $product->update($data);
            $this->syncAttributeValues($product, $request->input('attributes', []));
        });

        if ($request->hasFile('image')) {
            if ($product->primaryImage()->exists()) {
                $service->replace(
                    $product->primaryImage()->firstOrFail(),
                    $request->file('image'),
                    $request->input('alt')
                );
            } else {
                $service->uploadPrimary(
                    $product,
                    $request->file('image'),
                    $request->input('alt')
                );
            }
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Товар обновлён.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Товар удалён.');
    }

    protected function syncAttributeValues(Product $product, array $input): void
    {
        $attributes = ProductAttribute::query()
            ->with('options')
            ->get()
            ->keyBy('id');

        foreach ($attributes as $attributeId => $attribute) {
            $rawValue = $input[$attributeId] ?? null;

            $payload = match ($attribute->type) {
                'string' => $this->buildStringPayload($rawValue),
                'text' => $this->buildTextPayload($rawValue),
                'number' => $this->buildNumberPayload($rawValue),
                'boolean' => $this->buildBooleanPayload($rawValue),
                'select' => $this->buildSelectPayload($attribute, $rawValue),
                default => null,
            };

            $existing = ProductAttributeValue::query()
                ->where('product_id', $product->id)
                ->where('product_attribute_id', $attribute->id)
                ->first();

            if ($payload === null) {
                $existing?->delete();
                continue;
            }

            ProductAttributeValue::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'product_attribute_id' => $attribute->id,
                ],
                $payload
            );
        }
    }

    protected function buildStringPayload(mixed $rawValue): ?array
    {
        $value = is_string($rawValue) ? trim($rawValue) : null;

        if ($value === null || $value === '') {
            return null;
        }

        return [
            'product_attribute_option_id' => null,
            'value_string' => $value,
            'value_text' => null,
            'value_number' => null,
            'value_boolean' => null,
        ];
    }

    protected function buildTextPayload(mixed $rawValue): ?array
    {
        $value = is_string($rawValue) ? trim($rawValue) : null;

        if ($value === null || $value === '') {
            return null;
        }

        return [
            'product_attribute_option_id' => null,
            'value_string' => null,
            'value_text' => $value,
            'value_number' => null,
            'value_boolean' => null,
        ];
    }

    protected function buildNumberPayload(mixed $rawValue): ?array
    {
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        if (! is_numeric($rawValue)) {
            return null;
        }

        return [
            'product_attribute_option_id' => null,
            'value_string' => null,
            'value_text' => null,
            'value_number' => $rawValue,
            'value_boolean' => null,
        ];
    }

    protected function buildBooleanPayload(mixed $rawValue): ?array
    {
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        $normalized = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($normalized === null) {
            return null;
        }

        return [
            'product_attribute_option_id' => null,
            'value_string' => null,
            'value_text' => null,
            'value_number' => null,
            'value_boolean' => $normalized,
        ];
    }

    protected function buildSelectPayload(ProductAttribute $attribute, mixed $rawValue): ?array
    {
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        $option = $attribute->options->firstWhere('id', (int) $rawValue);

        if (! $option) {
            return null;
        }

        return [
            'product_attribute_option_id' => $option->id,
            'value_string' => null,
            'value_text' => null,
            'value_number' => null,
            'value_boolean' => null,
        ];
    }

    public function uploadImage(Request $request, Product $product, ProductImageService $service): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $service->uploadPrimary(
            $product,
            $validated['image'],
            $validated['alt'] ?? null,
        );

        return back()->with('success', 'Фото товара сохранено.');
    }

    public function replaceImage(Request $request, Product $product, ProductImageService $service): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $image = $product->primaryImage()->firstOrFail();

        $service->replace(
            $image,
            $validated['image'],
            $validated['alt'] ?? null,
        );

        return back()->with('success', 'Фото товара заменено.');
    }

    public function deleteImage(Product $product, ProductImageService $service): RedirectResponse
    {
        $image = $product->primaryImage()->first();

        if ($image) {
            $service->delete($image);
        }

        return back()->with('success', 'Фото товара удалено.');
    }
}
