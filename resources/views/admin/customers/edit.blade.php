@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Edit customer</h2>
        <div class="text-secondary mt-1">Update customer profile information and account status</div>
    </div>
@endsection

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.customers.index') }}" class="text-body-secondary text-decoration-none">← Back to customers</a>
        </p>

        @php
            $page_title = 'Customer #'.$customer->id;
        @endphp



        @if(session('success'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label for="first_name" class="form-label">First name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $customer->first_name) }}" class="form-control mb-2">
                    @error('first_name')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="form-label">Last name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $customer->last_name) }}" class="form-control mb-2">
                    @error('last_name')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" class="form-control mb-2">
                    @error('phone')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}" class="form-control mb-2">
                    @error('email')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label for="status" class="form-label">Customer status</label>
                <select name="status" id="status" class="form-control mb-2">
                    @foreach(\App\Enums\UserStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ old('status', $customer->status?->value ?? $customer->status) === $status->value ? 'selected' : '' }}>
                            {{ $status->value }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label for="region" class="form-label">Region</label>
                    <input type="text" name="region" id="region" value="{{ old('region', $customer->region) }}" class="form-control mb-2">
                    @error('region')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $customer->city) }}" class="form-control mb-2">
                    @error('city')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" rows="3" class="form-control mb-4">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex; gap:15px; align-items:center;">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
