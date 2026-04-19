@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Корзина</h1>

        @if(!empty($messages))
            <div style="color:#856404; margin-bottom:15px;">
                <ul>
                    @foreach($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div style="color:green; margin-bottom:15px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="color:red; margin-bottom:15px;">
                {{ session('error') }}
            </div>
        @endif

        @if(count($cart->items) === 0)
            <p>Корзина пуста.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Товар</th>
                    <th>Помол</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($cart->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->grindLabel() }}</td>
                        <td>{{ $item->price }}</td>
                        <td>
                            <form action="{{ route('cart.update', $item->rowId) }}" method="POST">
                                @csrf
                                <input type="number" name="qty" value="{{ $item->qty }}" min="1">
                                <button type="submit">Обновить</button>
                            </form>
                        </td>
                        <td>{{ $item->lineTotal() }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $item->rowId) }}" method="POST">
                                @csrf
                                <button type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <p>Общее количество: {{ $cart->count }}</p>
            <p>Итого: {{ $cart->subtotal }}</p>

            @if(!auth()->check() || !auth()->user()->isAdmin())
                <p style="margin-top:20px;">
                    <a href="{{ route('checkout.create') }}">Proceed to checkout</a>

                </p>
            @endif

            <form action="{{ route('cart.clear') }}" method="POST" style="margin-top:10px;">
                @csrf
                <button type="submit">Очистить корзину</button>
            </form>
        @endif
    </div>
@endsection
