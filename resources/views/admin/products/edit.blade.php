@extends('admin.layout.admin')

@section('title', 'Edit product')

@section('header')
    <div class="col">
        <h2 class="page-title">Edit product</h2>
        <div class="text-secondary mt-1">Edit product details and status</div>
    </div>
@endsection

@section('content')
    <p>
        <a href="{{ route('admin.products.index') }}" class="text-body-secondary text-decoration-none">
            ← Back to Products
        </a>
    </p>

    @include('admin.components.flash-success')

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.products._form', [
            'product' => $product,
            'attributes' => $attributes,
        ])

        <button type="submit" class="btn btn-outline-primary">Обновить</button>
    </form>

    <form
        id="delete-product-image-form"
        action="{{ route('admin.products.image.destroy', $product) }}"
        method="POST"
        class="d-none"
    >
        @csrf
        @method('DELETE')
    </form>
@endsection
