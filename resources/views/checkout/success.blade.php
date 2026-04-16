@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Order placed</h1>

        @if(session('success'))
            <div style="color:green; margin-bottom:15px;">
                {{ session('success') }}
            </div>
        @endif

        @if(!empty($checkoutSuccess['account_created']) && !empty($checkoutSuccess['reset_link_sent']))
            <div style="color:#0c5460; margin-bottom:15px;">
                We created an account for you and sent an email to {{ $checkoutSuccess['email'] }}.
                Set your password using the link in that email, then you will be able to sign in and track your order.
            </div>
        @elseif(!empty($checkoutSuccess['account_created']))
            <div style="color:#856404; margin-bottom:15px;">
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
            @endif
        </p>
    </div>
@endsection
