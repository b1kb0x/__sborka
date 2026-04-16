@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Создать характеристику</h1>

        <form action="{{ route('admin.product-attributes.store') }}" method="POST">
            @csrf

            @include('admin.product-attributes._form')

            <button type="submit">Сохранить</button>
        </form>
    </div>
@endsection
