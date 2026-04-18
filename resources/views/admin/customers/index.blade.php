@php use Carbon\Carbon; @endphp
@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Customers</h2>
        <div class="text-secondary mt-1">View customer accounts, statuses, and order history</div>
    </div>
@endsection

@section('content')

    @if(session('success'))
        <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.customers.index') }}" style="width: 100%" class="my-2">
                <div class="row g-2 align-items-end">
                    <div class="col-4">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            class="form-control"
                            placeholder="Name, email or phone"
                        >
                    </div>

                    <div class="col-md-auto">
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                            <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Blocked</option>
                        </select>
                    </div>

                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>
                    </div>

                    <div class="col-md-auto">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
            @if($customers->isEmpty())
                <p>No customers found.</p>
            @else

                <div class="table-responsive">
                    <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                        <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center">Orders</th>
                            <th>Last order</th>
                            <th>Registered</th>
                            <th class="w-1">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>

                                    @php
                                        $status = $customer->status?->value ?? $customer->status;
                                    @endphp

                                    <span @class([
                                'status',
                                'status-teal' => $status === 'active',
                                'status-red' => $status === 'blocked',
                            ])>
                                {{ $status }}
                            </span></td>


                                <td class="text-center">
                                    @if($customer->orders_count)
                                        <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}">
                                            {{ $customer->orders_count }}
                                        </a>
                                    @else
                                        <span class="text-secondary">0</span>
                                    @endif
                                </td>
                                <td>{{ $customer->last_order_at ? Carbon::parse($customer->last_order_at)->format('d.m.Y H:i') : '—' }}</td>
                                <td>{{ $customer->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.customers.edit', $customer) }}"
                                       class="btn btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="card-footer">{{ $customers->links() }}</div>
                </div>
        </div>
    @endif

@endsection
