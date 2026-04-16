@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Checkout</h1>

        @if (! auth()->check())
            <p>
                Already have an account?
                <a href="{{ url('/login') }}">Sign in</a>
                to use your saved details, or continue as guest.
            </p>
        @endif

        @if (! empty($messages))
            <div style="color:#856404; margin-bottom:15px;">
                <ul>
                    @foreach($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div style="color:red; margin-bottom:15px;">
                {{ session('error') }}
            </div>
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

        <form action="{{ route('cart.checkout') }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; max-width:900px;">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $checkoutData['first_name']) }}" required>
                </div>

                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $checkoutData['last_name']) }}" required>
                </div>

                <div>
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $checkoutData['phone']) }}" required>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $checkoutData['email']) }}" required>
                </div>

                <div>
                    <label for="region">Region</label>
                    <input id="region" type="text" name="region" value="{{ old('region', $checkoutData['region']) }}" required>
                </div>

                <div>
                    <label for="city">City</label>
                    <input id="city" type="text" name="city" value="{{ old('city', $checkoutData['city']) }}" required>
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" value="{{ old('address', $checkoutData['address']) }}" required style="width:100%;">
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="comment">Comment</label>
                    <textarea id="comment" name="comment" rows="4" style="width:100%;">{{ old('comment', $checkoutData['comment']) }}</textarea>
                </div>
            </div>

            <div style="margin-top:20px;">
                <p>Total items: {{ $cart->count }}</p>
                <p>Subtotal: {{ $cart->subtotal }}</p>
            </div>

            <div style="margin-top:20px;">
                <button type="submit">Place order</button>
                <a href="{{ route('cart.index') }}" style="margin-left:12px;">Back to cart</a>
            </div>
        </form>
    </div>
@endsection
