@php
    /** @var \App\Models\ProductAttributeOption|null $option */
    $option = $option ?? null;
@endphp

<div style="margin-bottom:15px;">
    <label for="value">Значение</label><br>
    <input
        type="text"
        name="value"
        id="value"
        value="{{ old('value', $option?->value) }}"
        style="width:100%;"
    >
    @error('value')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="sort_order">Порядок сортировки</label><br>
    <input
        type="number"
        name="sort_order"
        id="sort_order"
        value="{{ old('sort_order', $option?->sort_order ?? 0) }}"
        min="0"
        style="width:100%;"
    >
    @error('sort_order')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>
