@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Редактировать товар</h1>

        <form action="{{ route('admin.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.products._form', [
                'product' => $product,
                'attributes' => $attributes,
            ])

            <button type="submit">Обновить</button>
        </form>

        <hr style="margin:30px 0;">

        <section>
            <h2>Изображение товара</h2>

            @if($product->primaryImage)
                <div style="margin-bottom:20px;">
                    <img
                        src="{{ $product->primaryImage->preview_url }}"
                        alt="{{ $product->primaryImage->alt ?? $product->title }}"
                        style="max-width:300px; height:auto; display:block; margin-bottom:10px;"
                    >

                    <div style="color:#666; margin-bottom:15px;">
                        Alt: {{ $product->primaryImage->alt ?? '—' }}
                    </div>
                </div>

                <form action="{{ route('admin.products.image.replace', $product) }}" method="POST" enctype="multipart/form-data" style="margin-bottom:15px;">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom:10px;">
                        <label for="image_replace">Заменить фото</label><br>
                        <input type="file" name="image" id="image_replace" required>
                    </div>

                    <div style="margin-bottom:10px;">
                        <label for="image_alt_replace">Alt текст</label><br>
                        <input
                            type="text"
                            name="alt"
                            id="image_alt_replace"
                            value="{{ old('alt', $product->primaryImage->alt) }}"
                            style="width:100%;"
                        >
                    </div>

                    @error('image')
                    <div style="color:red; margin-bottom:10px;">{{ $message }}</div>
                    @enderror
                    @error('alt')
                    <div style="color:red; margin-bottom:10px;">{{ $message }}</div>
                    @enderror

                    <button type="submit">Заменить фото</button>
                </form>

                <form action="{{ route('admin.products.image.destroy', $product) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Удалить фото</button>
                </form>
            @else
                <form action="{{ route('admin.products.image.store', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div style="margin-bottom:10px;">
                        <label for="image_upload">Загрузить фото</label><br>
                        <input type="file" name="image" id="image_upload" required>
                    </div>

                    <div style="margin-bottom:10px;">
                        <label for="image_alt">Alt текст</label><br>
                        <input
                            type="text"
                            name="alt"
                            id="image_alt"
                            value="{{ old('alt') }}"
                            style="width:100%;"
                        >
                    </div>

                    @error('image')
                    <div style="color:red; margin-bottom:10px;">{{ $message }}</div>
                    @enderror
                    @error('alt')
                    <div style="color:red; margin-bottom:10px;">{{ $message }}</div>
                    @enderror

                    <button type="submit">Загрузить фото</button>
                </form>
            @endif
        </section>
    </div>
@endsection