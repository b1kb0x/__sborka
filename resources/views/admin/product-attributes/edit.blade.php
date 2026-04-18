@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Edit attribute</h2>
        <div class="text-secondary mt-1">Edit attribute settings</div>
    </div>
@endsection

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.product-attributes.index') }}" class="text-body-secondary text-decoration-none">← Back to Attributes</a>
        </p>

        <form action="{{ route('admin.product-attributes.update', $productAttribute) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.product-attributes._form', ['productAttribute' => $productAttribute])

            <button type="submit">Обновить</button>
        </form>
    </div>
@endsection
