@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Edit product</h2>
        <div class="text-secondary mt-1">Edit product details and status</div>
    </div>
@endsection

@section('content')

        <p>
            <a href="{{ route('admin.product-attributes.index') }}" class="text-body-secondary text-decoration-none">← Back to Products</a>
        </p>

        <form action="{{ route('admin.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.products._form', [
                'product' => $product,
                'attributes' => $attributes,
            ])

            <button type="submit"  class="btn btn-outline-primary">Обновить</button>
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

                    <button type="submit" class="btn btn-outline-primary">Заменить фото</button>
                </form>

                <form action="{{ route('admin.products.image.destroy', $product) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-primary">Удалить фото</button>
                </form>
            @else
                <form action="{{ route('admin.products.image.store', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div style="margin-bottom:10px;">
                        <label for="image_upload">Загрузить фото</label><br>
                        <!--input type="file" name="image" id="image_upload" required-->

                        <div class="mb-3">
                            <label class="form-label">Product image</label>

                            <label for="image" class="dropzone @error('image') is-invalid @enderror">
                                <div class="dz-message">
                                    <h3 class="dropzone-msg-title">Drop files here to upload</h3>
                                    <span class="dropzone-msg-desc text-secondary">or click to browse</span>
                                </div>

                                <input
                                    id="image"
                                    type="file"
                                    name="image"
                                    class="d-none"
                                    accept="image/*"
                                >
                            </label>

                            @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    Dropzone.autoDiscover = false;

                                    new Dropzone('#product-dropzone', {
                                        url: '#',
                                        autoProcessQueue: false,
                                        uploadMultiple: false,
                                        maxFiles: 1,
                                        acceptedFiles: 'image/*',
                                        addRemoveLinks: true,
                                        paramName: 'image'
                                    });
                                });
                            </script>
                        @endpush

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

                    <button type="submit" class="btn btn-outline-primary">Загрузить фото</button>
                </form>
            @endif
        </section>

@endsection
