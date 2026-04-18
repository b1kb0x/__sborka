@php
    /** @var \App\Models\ProductAttribute|null $productAttribute */
    $productAttribute = $productAttribute ?? null;
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Название</label>
    <input
        type="text"
        name="name"
        id="name"
        value="{{ old('name', $productAttribute?->name) }}"
        class="form-control"
    >
    @error('name')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input
        type="text"
        name="slug"
        id="slug"
        value="{{ old('slug', $productAttribute?->slug) }}"
        class="form-control"
        placeholder="region"
    >
    @error('slug')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="type" class="form-label">Тип</label>
    <select name="type" id="type" class="form-control">
        @php
            $selectedType = old('type', $productAttribute?->type ?? 'string');
        @endphp

        <option value="string" {{ $selectedType === 'string' ? 'selected' : '' }}>string</option>
        <option value="text" {{ $selectedType === 'text' ? 'selected' : '' }}>text</option>
        <option value="number" {{ $selectedType === 'number' ? 'selected' : '' }}>number</option>
        <option value="boolean" {{ $selectedType === 'boolean' ? 'selected' : '' }}>boolean</option>
        <option value="select" {{ $selectedType === 'select' ? 'selected' : '' }}>select</option>
    </select>
    @error('type')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="unit" class="form-label">Единица измерения</label>
    <input
        type="text"
        name="unit"
        id="unit"
        value="{{ old('unit', $productAttribute?->unit) }}"
        class="form-control"
        placeholder="MASL, г, %, балл"
    >
    @error('unit')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="display_group" class="form-label">Группа вывода</label>
    <input
        type="text"
        name="display_group"
        id="display_group"
        value="{{ old('display_group', $productAttribute?->display_group) }}"
        class="form-control"
        placeholder="top_specs / main_table / sidebar"
    >
    @error('display_group')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-5">
    <label for="sort_order" class="form-label">Порядок сортировки</label>
    <input
        type="number"
        name="sort_order"
        id="sort_order"
        value="{{ old('sort_order', $productAttribute?->sort_order ?? 0) }}"
        min="0"
        class="form-control"
    >
    @error('sort_order')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div class="mb-5">
    <label class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            name="is_visible"
            value="1"
            {{ old('is_visible', $productAttribute?->is_visible ?? true) ? 'checked' : '' }}
        >
        <span class="form-check-label">Видима на сайте</span>
    </label>
    @error('is_visible')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>
