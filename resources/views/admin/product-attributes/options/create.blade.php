@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.product-attributes.options.index', $productAttribute) }}">← Назад к опциям</a>
        </p>

        <h1>Создать опцию</h1>
        <p><strong>{{ $productAttribute->name }}</strong></p>

        <form action="{{ route('admin.product-attributes.options.store', $productAttribute) }}" method="POST">
            @csrf

            @include('admin.product-attributes.options._form')

            <button type="submit">Сохранить</button>
        </form>
    </div>
@endsection
