@extends('layouts.app')

@section('content')
    <div class="container">
        @include('customer._cabinet_nav')

        <h1>My orders</h1>

        @if($orders->isEmpty())
            <p>You do not have any orders yet.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Order status</th>
                    <th>Fulfillment</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                        <td>{{ $order->total }}</td>
                        <td>{{ $order->status?->label() ?? $order->status?->value }}</td>
                        <td>{{ $order->fulfillment_status_label }}</td>
                        <td><a href="{{ route('customer.orders.show', $order) }}">Details</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
