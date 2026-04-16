@php
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;

    /** @var \Illuminate\Support\Collection|\App\Models\ProductAttribute[] $attributes */
    $attributes = $attributes ?? collect();

    $attributeValueMap = $product?->attributeValues
        ? $product->attributeValues->keyBy('product_attribute_id')
        : collect();
@endphp

<div style="margin-bottom:15px;">
    <label for="title">Название</label><br>
    <input
        type="text"
        name="title"
        id="title"
        value="{{ old('title', $product?->title) }}"
        style="width:100%;"
    >
    @error('title')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="slug">Slug</label><br>
    <input
        type="text"
        name="slug"
        id="slug"
        value="{{ old('slug', $product?->slug) }}"
        style="width:100%;"
    >
    @error('slug')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="short_description">Краткое описание</label><br>
    <textarea
        name="short_description"
        id="short_description"
        rows="3"
        style="width:100%;"
    >{{ old('short_description', $product?->short_description) }}</textarea>
    @error('short_description')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="description">Описание</label><br>
    <textarea
        name="description"
        id="description"
        rows="8"
        style="width:100%;"
    >{{ old('description', $product?->description) }}</textarea>
    @error('description')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="price">Цена</label><br>
    <input
        type="number"
        name="price"
        id="price"
        value="{{ old('price', $product?->price ?? 0) }}"
        min="0"
        style="width:100%;"
    >
    @error('price')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="stock">Остаток</label><br>
    <input
        type="number"
        name="stock"
        id="stock"
        value="{{ old('stock', $product?->stock ?? 0) }}"
        min="0"
        style="width:100%;"
    >
    @error('stock')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label>
        <input
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}
        >
        Активен
    </label>
    @error('is_active')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

@if($attributes->isNotEmpty())
    <hr style="margin:30px 0;">

    <h2>Характеристики</h2>

    @foreach($attributes as $attribute)
        @php
            $currentValue = $attributeValueMap->get($attribute->id);
            $fieldName = "attributes[{$attribute->id}]";
        @endphp

        <div style="margin-bottom:20px; padding:12px; border:1px solid #ddd;">
            <label for="attribute_{{ $attribute->id }}">
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
                    style="width:100%;"
                >
            @elseif($attribute->type === 'text')
                <textarea
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    rows="4"
                    style="width:100%;"
                >{{ old($fieldName, $currentValue?->value_text) }}</textarea>
            @elseif($attribute->type === 'number')
                <input
                    type="number"
                    step="0.001"
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    value="{{ old($fieldName, $currentValue?->value_number) }}"
                    style="width:100%;"
                >
            @elseif($attribute->type === 'boolean')
                @php
                    $booleanOld = old($fieldName, $currentValue?->value_boolean);
                @endphp

                <select
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    style="width:100%;"
                >
                    <option value="">-- Не выбрано --</option>
                    <option value="1" {{ (string) $booleanOld === '1' ? 'selected' : '' }}>Да</option>
                    <option value="0" {{ (string) $booleanOld === '0' ? 'selected' : '' }}>Нет</option>
                </select>
            @elseif($attribute->type === 'select')
                <select
                    name="{{ $fieldName }}"
                    id="attribute_{{ $attribute->id }}"
                    style="width:100%;"
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