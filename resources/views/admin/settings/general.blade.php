@extends('admin.layout.admin')

@section('title', 'General settings')

@section('header')
    <div class="col">
        <h2 class="page-title">General settings</h2>
        <div class="text-secondary mt-1">Manage common store information</div>
    </div>
@endsection

@section('content')
    @include('admin.components.flash-success')

    <div class="row g-4">
        <div class="col-md-3">
            @include('admin.settings.partials.nav')
        </div>

        <div class="col-md-9">
            <form method="POST" action="{{ route('admin.settings.general.update') }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">General</h3>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Store name</label>
                            <input
                                id="store_name"
                                type="text"
                                name="store_name"
                                value="{{ old('store_name', $values['store_name'] ?? '') }}"
                                class="form-control @error('store_name') is-invalid @enderror"
                            >
                            @error('store_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="support_email" class="form-label">Support email</label>
                            <input
                                id="support_email"
                                type="email"
                                name="support_email"
                                value="{{ old('support_email', $values['support_email'] ?? '') }}"
                                class="form-control @error('support_email') is-invalid @enderror"
                            >
                            @error('support_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="support_phone" class="form-label">Support phone</label>
                            <input
                                id="support_phone"
                                type="text"
                                name="support_phone"
                                value="{{ old('support_phone', $values['support_phone'] ?? '') }}"
                                class="form-control @error('support_phone') is-invalid @enderror"
                            >
                            @error('support_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
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
