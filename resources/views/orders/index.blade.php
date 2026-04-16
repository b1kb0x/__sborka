@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Мои заказы</h1>

        @if($orders->isEmpty())
            <p>Заказов пока нет.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Статус заказа</th>
                    <th>Статус выполнения</th>
                    <th>Трек</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->status?->label() ?? $order->status?->value }}</td>
                        <td>{{ $order->fulfillment_status_label }}</td>
                        <td>{{ $order->tracking_number ?: '—' }}</td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}">Открыть</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
