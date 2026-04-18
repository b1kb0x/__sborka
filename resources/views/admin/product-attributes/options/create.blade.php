@extends('admin.layout.admin')

@section('header')
<div class="col">
    <h2 class="page-title">Create Option</h2>
    <div class="text-secondary mt-1">...</div>
</div>
@endsection

@section('content')

        <p>
            <a href="{{ route('admin.product-attributes.options.index', $productAttribute) }}" class="text-body-secondary text-decoration-none">← Back to Options</a>
        </p>

        <div class="card">
            <div class="card-body">

        <form action="{{ route('admin.product-attributes.options.store', $productAttribute) }}" method="POST">
            @csrf

            @include('admin.product-attributes.options._form')

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
            </div>
        </div>
@endsection
