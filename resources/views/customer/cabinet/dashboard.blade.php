@extends('layouts.app')

@section('content')
    <div class="container">
        @include('customer._cabinet_nav')

        <h1>Hello, {{ $user->first_name ?: $user->name }}</h1>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <p><strong>Total orders:</strong> {{ $ordersCount }}</p>

            @if($latestOrder)
                <p><strong>Last order:</strong> #{{ $latestOrder->id }}</p>
                <p><strong>Date:</strong> {{ $latestOrder->created_at?->format('d.m.Y H:i') }}</p>
                <p><strong>Total:</strong> {{ $latestOrder->total }}</p>
                <p><a href="{{ route('customer.orders.show', $latestOrder) }}">Open last order</a></p>
            @else
                <p>You do not have any orders yet.</p>
            @endif
        </div>

        <p>
            <a href="{{ route('customer.orders.index') }}">My orders</a>
            <span style="margin:0 8px;">|</span>
            <a href="{{ route('customer.profile.edit') }}">Profile</a>
        </p>
    </div>
@endsection
