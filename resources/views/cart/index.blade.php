@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>РљРѕСЂР·РёРЅР°</h1>

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
            <p>РљРѕСЂР·РёРЅР° РїСѓСЃС‚Р°.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>РўРѕРІР°СЂ</th>
                    <th>РџРѕРјРѕР»</th>
                    <th>Р¦РµРЅР°</th>
                    <th>РљРѕР»-РІРѕ</th>
                    <th>РЎСѓРјРјР°</th>
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
                                <button type="submit">РћР±РЅРѕРІРёС‚СЊ</button>
                            </form>
                        </td>
                        <td>{{ $item->lineTotal() }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $item->rowId) }}" method="POST">
                                @csrf
                                <button type="submit">РЈРґР°Р»РёС‚СЊ</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <p>РћР±С‰РµРµ РєРѕР»РёС‡РµСЃС‚РІРѕ: {{ $cart->count }}</p>
            <p>РС‚РѕРіРѕ: {{ $cart->subtotal }}</p>

            @if(!auth()->check() || !auth()->user()->isAdmin())
                <p style="margin-top:20px;">
                <a href="{{ route('checkout.create') }}">Proceed to checkout</a>

            </p>
            @endif

            <form action="{{ route('cart.clear') }}" method="POST" style="margin-top:10px;">
                @csrf
                <button type="submit">РћС‡РёСЃС‚РёС‚СЊ РєРѕСЂР·РёРЅСѓ</button>
            </form>
        @endif
    </div>
@endsection
