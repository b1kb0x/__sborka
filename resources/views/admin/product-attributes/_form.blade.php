@php
    /** @var \App\Models\ProductAttribute|null $productAttribute */
    $productAttribute = $productAttribute ?? null;
@endphp

<div style="margin-bottom:15px;">
    <label for="name">Название</label><br>
    <input
        type="text"
        name="name"
        id="name"
        value="{{ old('name', $productAttribute?->name) }}"
        style="width:100%;"
    >
    @error('name')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="slug">Slug</label><br>
    <input
        type="text"
        name="slug"
        id="slug"
        value="{{ old('slug', $productAttribute?->slug) }}"
        style="width:100%;"
        placeholder="region"
    >
    @error('slug')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="type">Тип</label><br>
    <select name="type" id="type" style="width:100%;">
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

<div style="margin-bottom:15px;">
    <label for="unit">Единица измерения</label><br>
    <input
        type="text"
        name="unit"
        id="unit"
        value="{{ old('unit', $productAttribute?->unit) }}"
        style="width:100%;"
        placeholder="MASL, г, %, балл"
    >
    @error('unit')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="display_group">Группа вывода</label><br>
    <input
        type="text"
        name="display_group"
        id="display_group"
        value="{{ old('display_group', $productAttribute?->display_group) }}"
        style="width:100%;"
        placeholder="top_specs / main_table / sidebar"
    >
    @error('display_group')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label for="sort_order">Порядок сортировки</label><br>
    <input
        type="number"
        name="sort_order"
        id="sort_order"
        value="{{ old('sort_order', $productAttribute?->sort_order ?? 0) }}"
        min="0"
        style="width:100%;"
    >
    @error('sort_order')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>

<div style="margin-bottom:15px;">
    <label>
        <input
            type="checkbox"
            name="is_visible"
            value="1"
            {{ old('is_visible', $productAttribute?->is_visible ?? true) ? 'checked' : '' }}
        >
        Видима на сайте
    </label>
    @error('is_visible')
    <div style="color:red;">{{ $message }}</div>
    @enderror
</div>
