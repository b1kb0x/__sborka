@php
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;

    /** @var \Illuminate\Support\Collection|\App\Models\ProductAttribute[] $attributes */
    $attributes = $attributes ?? collect();

    $attributeValueMap = $product?->attributeValues
        ? $product->attributeValues->keyBy('product_attribute_id')
        : collect();
@endphp

<div class="card mb-5">
    <div class="card-body">
<div class="mb-3">
    <label for="title" class="form-label">Название</label>
    <input
        type="text"
        name="title"
        id="title"
        value="{{ old('title', $product?->title) }}"
        class="form-control"
    >
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input
        type="text"
        name="slug"
        id="slug"
        value="{{ old('slug', $product?->slug) }}"
        class="form-control"
    >
    @error('slug')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="short_description" class="form-label">Краткое описание</label>
    <textarea
        name="short_description"
        id="short_description"
        rows="3"
        class="form-control"
    >{{ old('short_description', $product?->short_description) }}</textarea>
    @error('short_description')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Описание</label>
    <textarea
        name="description"
        id="description"
        rows="8"
        class="form-control"
    >{{ old('description', $product?->description) }}</textarea>
    @error('description')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="price" class="form-label">Цена</label>
    <input
        type="number"
        name="price"
        id="price"
        value="{{ old('price', $product?->price ?? 0) }}"
        min="0"
        class="form-control"
    >
    @error('price')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="stock" class="form-label">Остаток</label>
    <input
        type="number"
        name="stock"
        id="stock"
        value="{{ old('stock', $product?->stock ?? 0) }}"
        min="0"
        class="form-control"
    >
    @error('stock')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}
        >
        <span class="form-check-label">Активен</span>
    </label>
    @error('is_active')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <h3 class="card-title">Характеристики</h3>
    </div>
        <div class="card-body">

@if($attributes->isNotEmpty())


    @foreach($attributes as $attribute)
        @php
            $currentValue = $attributeValueMap->get($attribute->id);
            $fieldName = "attributes[{$attribute->id}]";
        @endphp

        <div style="margin-bottom:20px; padding:12px; border:1px solid #ddd;">
            <label for="attribute_{{ $attribute->id }}" class="form-label">
                <strong>{{ $attribute->name }}</strong>
                @if($attribute->unit)
                    <span style="color:#666;">({{ $attribute->unit }})</span>
                @endif
            </label>

            @if($attribute->display_group)
                <div style="font-size:12px; color:#777; margin:4px 0 10px;">
                    Группа: {{ $attribute->display_group }}
                </div>
            @endif

            @if($attribute->type === 'string')
                <input
                    type="text"
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    value="{{ old($fieldName, $currentValue?->value_string) }}"
                    class="form-control"
                >
            @elseif($attribute->type === 'text')
                <textarea
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    rows="4"
                    class="form-control"
                >{{ old($fieldName, $currentValue?->value_text) }}</textarea>
            @elseif($attribute->type === 'number')
                <input
                    type="number"
                    step="0.001"
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    value="{{ old($fieldName, $currentValue?->value_number) }}"
                    class="form-control"
                >
            @elseif($attribute->type === 'boolean')
                @php
                    $booleanOld = old($fieldName, $currentValue?->value_boolean);
                @endphp

                <select
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    class="form-control"
                >
                    <option value="">-- Не выбрано --</option>
                    <option value="1" {{ (string) $booleanOld === '1' ? 'selected' : '' }}>Да</option>
                    <option value="0" {{ (string) $booleanOld === '0' ? 'selected' : '' }}>Нет</option>
                </select>
            @elseif($attribute->type === 'select')
                <select
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    class="form-control"
                >
                    <option value="">-- Не выбрано --</option>

                    @foreach($attribute->options as $option)
                        <option
                            value="{{ $option->id }}"
                            {{ (string) old($fieldName, $currentValue?->product_attribute_option_id) === (string) $option->id ? 'selected' : '' }}
                        >
                            {{ $option->value }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    @endforeach
@endif
        </div>
</div>
