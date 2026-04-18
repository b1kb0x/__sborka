@extends('admin.layout.admin')

@section('title', 'Admin settings')

@section('header')
    <div class="col">
        <h2 class="page-title">Admin settings</h2>
        <div class="text-secondary mt-1">Manage admin panel preferences</div>
    </div>
@endsection

@section('content')
    @include('admin.components.flash-success')

    <div class="row g-4">
        <div class="col-md-3">
            @include('admin.settings.partials.nav')
        </div>

        <div class="col-md-9">
            <form method="POST" action="{{ route('admin.settings.admin.update') }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Admin</h3>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="products_per_page" class="form-label">Products per page</label>
                            <input
                                id="products_per_page"
                                type="number"
                                name="products_per_page"
                                min="1"
                                max="200"
                                value="{{ old('products_per_page', $values['products_per_page'] ?? 20) }}"
                                class="form-control @error('products_per_page') is-invalid @enderror"
                            >
                            @error('products_per_page')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="orders_per_page" class="form-label">Orders per page</label>
                            <input
                                id="orders_per_page"
                                type="number"
                                name="orders_per_page"
                                min="1"
                                max="200"
                                value="{{ old('orders_per_page', $values['orders_per_page'] ?? 20) }}"
                                class="form-control @error('orders_per_page') is-invalid @enderror"
                            >
                            @error('orders_per_page')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customers_per_page" class="form-label">Customers per page</label>
                            <input
                                id="customers_per_page"
                                type="number"
                                name="customers_per_page"
                                min="1"
                                max="200"
                                value="{{ old('customers_per_page', $values['customers_per_page'] ?? 20) }}"
                                class="form-control @error('customers_per_page') is-invalid @enderror"
                            >
                            @error('customers_per_page')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-check form-switch">
                                <input
                                    type="checkbox"
                                    name="sidebar_collapsed_by_default"
                                    value="1"
                                    class="form-check-input @error('sidebar_collapsed_by_default') is-invalid @enderror"
                                    {{ old('sidebar_collapsed_by_default', $values['sidebar_collapsed_by_default'] ?? false) ? 'checked' : '' }}
                                >
                                <span class="form-check-label">Sidebar collapsed by default</span>
                            </label>
                            @error('sidebar_collapsed_by_default')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
