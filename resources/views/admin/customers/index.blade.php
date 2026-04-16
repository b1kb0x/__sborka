@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Customers</h1>

        @if(session('success'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.customers.index') }}" style="margin-bottom:20px;">
            <div style="display:grid; grid-template-columns:2fr 1fr auto; gap:10px; align-items:end;">
                <div>
                    <label for="search">Search</label><br>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name or email"
                        style="width:100%;"
                    >
                </div>

                <div>
                    <label for="status">UserStatus</label><br>
                    <select name="status" id="status" style="width:100%;">
                        <option value="">All</option>
                        @foreach(\App\Enums\UserStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit">Filter</button>
                </div>
            </div>
        </form>

        @if($customers->isEmpty())
            <p>No customers found.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>UserStatus</th>
                    <th>Orders</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->status?->value ?? $customer->status }}</td>
                        <td>{{ $customer->orders_count }}</td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}">Open</a>
                            |
                            <a href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
@endsection
