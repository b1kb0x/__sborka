@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Create product</h2>
        <div class="text-secondary mt-1">Add a new catalog item</div>
    </div>

@endsection

@section('content')

    <p>
        <a href="{{ route('admin.products.index') }}" class="text-body-secondary text-decoration-none">← Back to Products</a>
    </p>

        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf

            @include('admin.products._form', [
                'attributes' => $attributes,
            ])

            <button type="submit" class="btn btn-outline-primary">Сохранить</button>
        </form>
@endsection
