@extends('layouts.app')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('orders.index') }}">← Назад к заказам</a>
        </p>

        <h1>Заказ #{{ $order->id }}</h1>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <p>
                <strong>Статус заказа:</strong>
                {{ $order->status?->label() ?? $order->status?->value }}
            </p>

            <p>
                <strong>Статус выполнения:</strong>
                {{ $order->fulfillment_status_label }}
            </p>

            @if($order->carrier_name)
                <p>
                    <strong>Транспортная компания:</strong>
                    {{ $order->carrier_name }}
                </p>
            @endif

            @if($order->tracking_number)
                <p>
                    <strong>Трек-номер:</strong>
                    {{ $order->tracking_number }}
                </p>
            @endif

            @if($order->handed_to_carrier_at)
                <p>
                    <strong>Передано в доставку:</strong>
                    {{ $order->handed_to_carrier_at->format('d.m.Y H:i') }}
                </p>
            @endif

            @if($order->delivered_at)
                <p>
                    <strong>Доставлено:</strong>
                    {{ $order->delivered_at->format('d.m.Y H:i') }}
                </p>
            @endif
        </div>

        <h2>Позиции заказа</h2>

        @if($order->items->isEmpty())
            <p>Позиции отсутствуют.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Товар</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Помол</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_title ?? $item->product?->title }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit_price }}</td>
                        <td>{{ $item->grind_label }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
