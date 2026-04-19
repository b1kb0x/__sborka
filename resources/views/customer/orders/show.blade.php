@extends('layouts.app')

@section('content')
    <div class="container">
        @include('customer._cabinet_nav')

        <p><a href="{{ route('customer.orders.index') }}">Back to orders</a></p>

        <h1>Order #{{ $order->id }}</h1>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <p><strong>Date:</strong> {{ $order->created_at?->format('d.m.Y H:i') }}</p>
            <p><strong>Order status:</strong> {{ $order->status?->label() ?? $order->status?->value }}</p>
            <p><strong>Fulfillment status:</strong> {{ $order->fulfillment_status_label }}</p>
            <p><strong>Subtotal:</strong> {{ $order->subtotal }}</p>
            <p><strong>Total:</strong> {{ $order->total }}</p>
            <p><strong>Comment:</strong> {{ $order->comment ?: '—' }}</p>
        </div>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <h2>Delivery details</h2>
            <p><strong>First name:</strong> {{ $order->first_name }}</p>
            <p><strong>Last name:</strong> {{ $order->last_name }}</p>
            <p><strong>Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Email:</strong> {{ $order->email }}</p>
            <p><strong>Region:</strong> {{ $order->region }}</p>
            <p><strong>City:</strong> {{ $order->city }}</p>
            <p><strong>Address:</strong> {{ $order->address }}</p>
        </div>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <h2>Delivery</h2>
            <p><strong>Service:</strong> {{ $order->delivery_service_name ?: 'вЂ”' }}</p>
            <p><strong>Region:</strong> {{ $order->delivery_region_name ?: 'вЂ”' }}</p>
            <p><strong>City:</strong> {{ $order->delivery_city_name ?: 'вЂ”' }}</p>
            <p><strong>Branch:</strong> {{ $order->delivery_branch_name ?: 'вЂ”' }}</p>
            <p><strong>Address:</strong> {{ $order->delivery_branch_address ?: 'вЂ”' }}</p>
            <p><strong>Postal code:</strong> {{ $order->delivery_branch_postal_code ?: 'вЂ”' }}</p>
        </div>

        <h2>Items</h2>

        @if($order->items->isEmpty())
            <p>No items found.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Grind</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_title ?? $item->product?->title }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->line_total }}</td>
                        <td>{{ $item->grind_label }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
