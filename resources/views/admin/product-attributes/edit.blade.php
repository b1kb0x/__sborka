@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Редактировать характеристику</h1>

        <form action="{{ route('admin.product-attributes.update', $productAttribute) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.product-attributes._form', ['productAttribute' => $productAttribute])

            <button type="submit">Обновить</button>
        </form>
    </div>
@endsection
