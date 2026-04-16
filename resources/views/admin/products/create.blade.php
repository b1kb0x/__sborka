@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Создать товар</h1>

        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf

            @include('admin.products._form', [
                'attributes' => $attributes,
            ])

            <button type="submit">Сохранить</button>
        </form>
    </div>
@endsection
