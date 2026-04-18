@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Orders</h2>
        <div class="text-secondary mt-1">Review, filter, and manage customer orders</div>
    </div>
@endsection

@section('content')


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

        <div class="card">
            <div class="table-responsive">
                <div class="card-header"></div>
                <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
        <thead>
        <tr>
            <th class="w-1">#</th>
            <th>Customer</th>
            <th>Order Status</th>
            <th>Fulfillment</th>
            <th>Carrier</th>
            <th>Track</th>
            <th class="w-1">Action</th>
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
                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-primary">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $orders->links() }}
            </div>
        </div>

@endsection
