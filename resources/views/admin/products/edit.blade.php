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
    </div>
@endsection
