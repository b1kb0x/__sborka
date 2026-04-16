@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.customers.index') }}">< Back to customers</a>
        </p>

        <h1>Customer #{{ $customer->id }}</h1>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <p><strong>Name:</strong> {{ $customer->name }}</p>
            <p><strong>Email:</strong> {{ $customer->email }}</p>
            <p><strong>Role:</strong> {{ $customer->role }}</p>
            <p><strong>UserStatus:</strong> {{ $customer->status?->value ?? $customer->status }}</p>
            <p><strong>Created:</strong> {{ $customer->created_at?->format('d.m.Y H:i') }}</p>
        </div>

        <p>
            <a href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
        </p>

        <hr style="margin:30px 0;">

        <h2>Customer orders</h2>

        @if($customer->orders->isEmpty())
            <p>No orders yet.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Order status</th>
                    <th>Fulfillment status</th>
                    <th>Total</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customer->orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->status?->label() ?? $order->status?->value ?? $order->status }}</td>
                        <td>{{ $order->fulfillment_status_label ?: '—' }}</td>
                        <td>{{ number_format($order->total, 0, '.', ' ') }}</td>
                        <td>{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.edit', $order) }}">Open order</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
