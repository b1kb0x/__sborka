@extends('admin.layout.admin')

@section('title', 'Checkout settings')

@section('header')
    <div class="col">
        <h2 class="page-title">Checkout settings</h2>
        <div class="text-secondary mt-1">Manage checkout behavior and order notifications</div>
    </div>
@endsection

@section('content')
    @include('admin.components.flash-success')

    <div class="row g-4">
        <div class="col-md-3">
            @include('admin.settings.partials.nav')
        </div>

        <div class="col-md-9">
            <form method="POST" action="{{ route('admin.settings.checkout.update') }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Checkout</h3>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-check form-switch">
                                <input
                                    type="checkbox"
                                    name="guest_checkout_enabled"
                                    value="1"
                                    class="form-check-input @error('guest_checkout_enabled') is-invalid @enderror"
                                    {{ old('guest_checkout_enabled', $values['guest_checkout_enabled'] ?? false) ? 'checked' : '' }}
                                >
                                <span class="form-check-label">Guest checkout enabled</span>
                            </label>
                            @error('guest_checkout_enabled')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default_order_status" class="form-label">Default order status</label>
                            <select
                                id="default_order_status"
                                name="default_order_status"
                                class="form-select @error('default_order_status') is-invalid @enderror"
                            >
                                @php
                                    $defaultOrderStatus = old('default_order_status', $values['default_order_status'] ?? 'new');
                                @endphp

                                <option value="new" @selected($defaultOrderStatus === 'new')>New</option>
                                <option value="processing" @selected($defaultOrderStatus === 'processing')>Processing</option>
                                <option value="completed" @selected($defaultOrderStatus === 'completed')>Completed</option>
                                <option value="cancelled" @selected($defaultOrderStatus === 'cancelled')>Cancelled</option>
                            </select>
                            @error('default_order_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_notification_email" class="form-label">Order notification email</label>
                            <input
                                id="order_notification_email"
                                type="email"
                                name="order_notification_email"
                                value="{{ old('order_notification_email', $values['order_notification_email'] ?? '') }}"
                                class="form-control @error('order_notification_email') is-invalid @enderror"
                            >
                            @error('order_notification_email')
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
