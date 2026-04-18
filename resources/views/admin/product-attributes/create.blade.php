@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Create attribute</h2>
        <div class="text-secondary mt-1">Add a new attribute for product specifications</div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">

        <form action="{{ route('admin.product-attributes.store') }}" method="POST">
            @csrf

            @include('admin.product-attributes._form')

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
        </div>
    </div>
@endsection
