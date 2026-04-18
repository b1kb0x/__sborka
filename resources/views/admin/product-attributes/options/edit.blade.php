@extends('admin.layout.admin')

@section('content')

        <p>
            <a href="{{ route('admin.product-attributes.options.index', $productAttribute) }}">← Назад к опциям</a>
        </p>

        <h1>Редактировать опцию</h1>
        <p><strong>{{ $productAttribute->name }}</strong></p>
        <div class="card">
            <div class="card-body">
        <form action="{{ route('admin.product-attributes.options.update', [$productAttribute, $option]) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.product-attributes.options._form', ['option' => $option])

            <button type="submit" class="btn btn-primary">Обновить</button>
        </form>
            </div>
        </div>
@endsection
