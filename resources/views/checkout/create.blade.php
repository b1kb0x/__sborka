@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Checkout</h1>

        @if (! auth()->check())
            <div style="background:#f5f7fb; border:1px solid #d8e0ef; padding:16px; margin:16px 0; border-radius:8px;">
                <strong>Already have an account?</strong>
                <div style="margin-top:6px;">
                    <a href="{{ url('/login') }}">Sign in</a>
                    to use your saved details and see your order history. You can still continue as guest.
                </div>
            </div>
        @endif

        @if (! empty($messages))
            <div style="background:#fff3cd; color:#856404; border:1px solid #ffe08a; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                <ul>
                    @foreach($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div style="background:#f8d7da; color:#721c24; border:1px solid #f1aeb5; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#f8d7da; color:#721c24; border:1px solid #f1aeb5; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                <strong>Please check the form.</strong>
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
                    <label for="first_name">First name *</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $checkoutData['first_name']) }}" required aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('first_name') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="last_name">Last name *</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $checkoutData['last_name']) }}" required aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('last_name') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="phone">Phone *</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $checkoutData['phone']) }}" required aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" placeholder="+380..." style="width:100%; border:1px solid {{ $errors->has('phone') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $checkoutData['email']) }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('email') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="region">Region *</label>
                    <input id="region" type="text" name="region" value="{{ old('region', $checkoutData['region']) }}" required aria-invalid="{{ $errors->has('region') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('region') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="city">City *</label>
                    <input id="city" type="text" name="city" value="{{ old('city', $checkoutData['city']) }}" required aria-invalid="{{ $errors->has('city') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('city') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="address">Address *</label>
                    <input id="address" type="text" name="address" value="{{ old('address', $checkoutData['address']) }}" required aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('address') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="comment">Comment</label>
                    <textarea id="comment" name="comment" rows="4" style="width:100%;">{{ old('comment', $checkoutData['comment']) }}</textarea>
                </div>
            </div>

            <div style="margin-top:20px; background:#f8f9fa; border:1px solid #e9ecef; padding:16px; border-radius:8px; max-width:900px;">
                <p>Total items: {{ $cart->count }}</p>
                <p>Subtotal: {{ $cart->subtotal }}</p>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" id="checkout-submit">Place order</button>
                <a href="{{ route('cart.index') }}" style="margin-left:12px;">Back to cart</a>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.querySelector('form[action="{{ route('cart.checkout') }}"]');
                var submitButton = document.getElementById('checkout-submit');

                if (! form || ! submitButton) {
                    return;
                }

                form.addEventListener('submit', function () {
                    if (submitButton.disabled) {
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Placing order...';
                });
            });
        </script>
    </div>
@endsection
