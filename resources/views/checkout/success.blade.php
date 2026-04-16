@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Order placed</h1>

        @if(session('success'))
            <div style="background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                {{ session('success') }}
            </div>
        @endif

        <p style="margin-bottom:16px;">
            Your order has been accepted. We saved all checkout details and will continue processing it shortly.
        </p>

        @if(!empty($checkoutSuccess['account_created']) && !empty($checkoutSuccess['reset_link_sent']))
            <div style="background:#cff4fc; color:#055160; border:1px solid #9eeaf9; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                We created an account for you and sent an email to {{ $checkoutSuccess['email'] }}.
                Set your password using the link in that email, then you will be able to sign in and track your order.
            </div>
        @elseif(!empty($checkoutSuccess['account_created']))
            <div style="background:#fff3cd; color:#664d03; border:1px solid #ffecb5; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                We created an account for you, but we could not send the password setup email right now.
                Your order has been saved.
            </div>
        @endif

        <p>Your order #{{ $checkoutSuccess['order_id'] }} has been created.</p>
        <p>Confirmation email: {{ $checkoutSuccess['email'] }}</p>

        <p>
            <a href="{{ route('products.index') }}">Continue shopping</a>
            @if(!empty($checkoutSuccess['authenticated']))
                <span style="margin:0 8px;">|</span>
                <a href="{{ route('orders.show', $checkoutSuccess['order_id']) }}">View order</a>
            @else
                <span style="margin:0 8px;">|</span>
                <a href="{{ url('/login') }}">Sign in</a>
            @endif
        </p>
    </div>
@endsection
