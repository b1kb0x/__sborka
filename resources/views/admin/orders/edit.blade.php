@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Edit order #{{ $order->id }}</h1>

        @if(session('success'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                {{ session('success') }}
            </div>
        @endif

        <div style="margin-bottom:20px; padding:15px; border:1px solid #ddd;">
            <h2 style="margin-top:0;">Customer details</h2>
            <p><strong>First name:</strong> {{ $order->first_name }}</p>
            <p><strong>Last name:</strong> {{ $order->last_name }}</p>
            <p><strong>Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Email:</strong> {{ $order->email }}</p>
            <p><strong>Customer status:</strong> {{ $order->customer_status }}</p>
            <p><strong>Region:</strong> {{ $order->region }}</p>
            <p><strong>City:</strong> {{ $order->city }}</p>
            <p><strong>Address:</strong> {{ $order->address }}</p>
            <p><strong>Comment:</strong> {{ $order->comment ?: '—' }}</p>
        </div>

        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:15px;">
                <label for="status">Order status</label><br>
                <select name="status" id="status" style="width:100%;">
                    @foreach($orderStatuses as $status)
                        <option
                            value="{{ $status->value }}"
                            {{ old('status', $order->status?->value) === $status->value ? 'selected' : '' }}
                        >
                            {{ method_exists($status, 'label') ? $status->label() : $status->value }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:15px;">
                <label for="fulfillment_status">Fulfillment status</label><br>
                <select name="fulfillment_status" id="fulfillment_status" style="width:100%;">
                    @foreach($fulfillmentStatuses as $status)
                        <option
                            value="{{ $status->value }}"
                            {{ old('fulfillment_status', $order->fulfillment_status?->value) === $status->value ? 'selected' : '' }}
                        >
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('fulfillment_status')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:15px;">
                <label for="carrier_name">Carrier</label><br>
                <input
                    type="text"
                    name="carrier_name"
                    id="carrier_name"
                    value="{{ old('carrier_name', $order->carrier_name) }}"
                    style="width:100%;"
                >
                @error('carrier_name')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:15px;">
                <label for="tracking_number">Tracking number</label><br>
                <input
                    type="text"
                    name="tracking_number"
                    id="tracking_number"
                    value="{{ old('tracking_number', $order->tracking_number) }}"
                    style="width:100%;"
                >
                @error('tracking_number')
                <div style="color:red;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Save</button>
        </form>

        <hr style="margin:30px 0;">

        <h2>Order items</h2>

        @if($order->items->isEmpty())
            <p>No items found.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Grind</th>
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
