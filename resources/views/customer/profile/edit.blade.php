@extends('layouts.app')

@section('content')
    <div class="container">
        @include('customer._cabinet_nav')

        <h1>Profile</h1>

        @if(session('status') === 'profile-updated')
            <div style="color:green; margin-bottom:15px;">Profile updated.</div>
        @endif

        @if($errors->any())
            <div style="color:red; margin-bottom:15px;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.profile.update') }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; max-width:900px;">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                </div>

                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                </div>

                <div>
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" required>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div>
                    <label for="region">Region</label>
                    <input id="region" type="text" name="region" value="{{ old('region', $user->region) }}" required>
                </div>

                <div>
                    <label for="city">City</label>
                    <input id="city" type="text" name="city" value="{{ old('city', $user->city) }}" required>
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" value="{{ old('address', $user->address) }}" required style="width:100%;">
                </div>
            </div>

            <div style="margin-top:20px;">
                <button type="submit">Save profile</button>
            </div>
        </form>
    </div>
@endsection
