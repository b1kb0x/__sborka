@extends('admin.layout.admin')

@section('title', 'Dashboard')

@section('header')
    <div class="col">
        <h2 class="page-title">Dashboard</h2>
        <div class="text-secondary mt-1">Overview of new orders</div>
    </div>

    <div class="col-auto ms-auto d-print-none">
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="btn btn-primary">
            All new orders
        </a>
    </div>
@endsection

@section('content')
    @php
        $newOrdersCount = $newOrders->count();

        $formatCustomerName = function ($order) {
            $fullName = trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''));

            return $fullName !== ''
                ? $fullName
                : ($order->customer_name ?? $order->name ?? $order->user?->name ?? '—');
        };

        $formatCustomerEmail = function ($order) {
            return $order->email ?? $order->customer_email ?? $order->user?->email ?? '—';
        };

        $formatTotal = function ($order) {
            $total = $order->total ?? 0;

            return number_format($total / 100, 2, '.', ' ');
        };
    @endphp

    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">New orders</div>
                    <div class="h1 mb-0">{{ $newOrdersCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Latest new orders</h3>
        </div>

        @if($newOrders->isEmpty())
            <div class="card-body">
                <div class="text-secondary">No new orders.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="w-1">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($newOrders as $order)
                        @php
                            $status = $order->status?->value ?? $order->status;
                        @endphp

                        <tr>
                            <td>#{{ $order->id }}</td>

                            <td>{{ $formatCustomerName($order) }}</td>

                            <td>{{ $formatCustomerEmail($order) }}</td>

                            <td>{{ $formatTotal($order) }}</td>

                            <td>
                                <span @class([
                                    'badge',
                                    'bg-blue-lt' => $status === 'new',
                                    'bg-yellow-lt' => $status === 'pending',
                                    'bg-green-lt' => in_array($status, ['paid', 'completed', 'delivered'], true),
                                    'bg-red-lt' => in_array($status, ['cancelled', 'blocked'], true),
                                    'bg-secondary-lt' => !in_array($status, ['new', 'pending', 'paid', 'completed', 'delivered', 'cancelled', 'blocked'], true),
                                ])>
                                    {{ $status }}
                                </span>
                            </td>

                            <td>{{ $order->created_at?->format('d.m.Y H:i') ?? '—' }}</td>

                            <td>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-primary btn-sm">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
