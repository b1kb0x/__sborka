@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Заказы</h1>

        @if(session('success'))
            <div style="color: green; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        @if($filteredCustomer)
            <div style="margin-bottom:15px; padding:10px; border:1px solid #ddd;">
                Filtering by customer:
                <strong>{{ $filteredCustomer->name ?: $filteredCustomer->email }}</strong>
                <a href="{{ route('admin.orders.index') }}" style="margin-left:10px;">Clear filter</a>
            </div>
        @endif

        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Покупатель</th>
                <th>Статус заказа</th>
                <th>Статус выполнения</th>
                <th>Транспортная компания</th>
                <th>Трек</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user?->name }}</td>
                    <td>{{ $order->status?->label() ?? $order->status?->value }}</td>
                    <td>{{ $order->fulfillment_status_label }}</td>
                    <td>{{ $order->carrier_name ?: '—' }}</td>
                    <td>{{ $order->tracking_number ?: '—' }}</td>
                    <td>
                        <a href="{{ route('admin.orders.edit', $order) }}">Редактировать</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div style="margin-top:20px;">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
