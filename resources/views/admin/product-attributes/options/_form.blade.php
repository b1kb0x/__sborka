@php
    /** @var \App\Models\ProductAttributeOption|null $option */
    $option = $option ?? null;
@endphp

<div class="mb-3">
    <label for="value" class="form-label">Значение</label>
    <input
        type="text"
        name="value"
        id="value"
        value="{{ old('value', $option?->value) }}"
        class="form-control"
    >
    @error('value')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-5">
    <label for="sort_order" class="form-label">Порядок сортировки</label>
    <input
        type="number"
        name="sort_order"
        id="sort_order"
        value="{{ old('sort_order', $option?->sort_order ?? 0) }}"
        min="0"
        class="form-control"
    >
    @error('sort_order')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>
