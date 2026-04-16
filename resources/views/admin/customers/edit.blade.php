@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.customers.index') }}">← Назад к покупателям</a>
        </p>

        <h1>Покупатель #{{ $customer->id }}</h1>

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <p><strong>Имя:</strong> {{ $customer->name }}</p>
            <p><strong>Email:</strong> {{ $customer->email }}</p>
            <p><strong>Роль:</strong> {{ $customer->role }}</p>
            <p><strong>Статус:</strong> {{ $customer->status?->value ?? $customer->status }}</p>
            <p><strong>Создан:</strong> {{ $customer->created_at?->format('d.m.Y H:i') }}</p>

            @if(method_exists($customer, 'trashed') && $customer->trashed())
                <p><strong>Удалён:</strong> Да</p>
            @endif
        </div>

        <p>
            <a href="{{ route('admin.customers.edit', $customer) }}">Редактировать</a>
        </p>

        <hr style="margin:30px 0;">

        <h2>Заказы покупателя</h2>

        @if($customer->orders->isEmpty())
            <p>Заказов пока нет.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Статус заказа</th>
                    <th>Статус выполнения</th>
                    <th>Сумма</th>
                    <th>Создан</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customer->orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->status?->label() ?? $order->status?->value ?? $order->status }}</td>
                        <td>{{ $order->fulfillment_status_label ?? '—' }}</td>
                        <td>{{ number_format($order->total, 0, '.', ' ') }}</td>
                        <td>{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.edit', $order) }}">Открыть заказ</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
