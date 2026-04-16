@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.product-attributes.options.index', $productAttribute) }}">← Назад к опциям</a>
        </p>

        <h1>Редактировать опцию</h1>
        <p><strong>{{ $productAttribute->name }}</strong></p>

        <form action="{{ route('admin.product-attributes.options.update', [$productAttribute, $option]) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.product-attributes.options._form', ['option' => $option])

            <button type="submit">Обновить</button>
        </form>
    </div>
@endsection
