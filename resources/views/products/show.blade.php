@extends('layouts.app')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('products.index') }}">← Назад в каталог</a>
        </p>

        <h1>{{ $product->title }}</h1>

        @if($product->primaryImage)
            <p>
                <img src="{{ $product->primaryImage->original_url }}" alt="{{ $product->primaryImage->alt ?? $product->title }}" width="300">
            </p>
        @endif

        <p><strong>Цена:</strong> {{ $product->price }}</p>
        <p><strong>В наличии:</strong> {{ $product->stock }}</p>

        @if($product->short_description)
            <p>{{ $product->short_description }}</p>
        @endif

        @if($product->description)
            <div>{!! nl2br(e($product->description)) !!}</div>
        @endif

        <form action="{{ route('cart.add') }}" method="POST" style="margin-top:20px;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div style="margin-bottom:15px;">
                <label for="grind_type"><strong>Помол</strong></label><br>
                <select name="grind_type" id="grind_type" style="width:100%;">
                    @foreach(\App\Enums\GrindType::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label for="qty"><strong>Количество</strong></label><br>
                <input type="number" name="qty" id="qty" value="1" min="1" max="{{ $product->stock }}" style="width:100%;">
            </div>

            <button type="submit" {{ $product->stock < 1 ? 'disabled' : '' }}>
                Купить
            </button>
        </form>
    </div>
@endsection