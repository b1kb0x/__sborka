@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.customers.index') }}">< Back to customers</a>
        </p>

        <h1>Customer #{{ $customer->id }}</h1>

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
                    <label for="first_name">First name</label><br>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $customer->first_name) }}" style="width:100%;">
                    @error('first_name')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="last_name">Last name</label><br>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $customer->last_name) }}" style="width:100%;">
                    @error('last_name')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label for="phone">Phone</label><br>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" style="width:100%;">
                    @error('phone')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="email">Email</label><br>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}" style="width:100%;">
                    @error('email')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label for="status">Customer status</label><br>
                <select name="status" id="status" style="width:100%;">
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
                    <label for="region">Region</label><br>
                    <input type="text" name="region" id="region" value="{{ old('region', $customer->region) }}" style="width:100%;">
                    @error('region')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="city">City</label><br>
                    <input type="text" name="city" id="city" value="{{ old('city', $customer->city) }}" style="width:100%;">
                    @error('city')
                    <div style="color:red;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label for="address">Address</label><br>
                <textarea name="address" id="address" rows="3" style="width:100%;">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex; gap:15px; align-items:center;">
                <button type="submit">Save</button>
                <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}">View customer orders</a>
            </div>
        </form>
    </div>
@endsection
