@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Каталог кофе</h1>

        @if($products->isEmpty())
            <p>Товаров пока нет.</p>
        @else
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px;">
                @foreach($products as $product)
                    <article style="border:1px solid #ddd; padding:16px;">
                        @if($product->image_path)
                            <div style="margin-bottom:12px;">
                                <img src="{{ asset($product->image_path) }}"
                                     alt="{{ $product->title }}"
                                     style="max-width:100%; height:auto;">
                            </div>
                        @endif

                        <h2 style="margin:0 0 10px;">
                            <a href="{{ route('products.show', $product->slug) }}">
                                {{ $product->title }}
                            </a>
                        </h2>

                        @if($product->short_description)
                            <p>{{ $product->short_description }}</p>
                        @endif

                        <a href="{{ route('products.show', $product->slug) }}">
                            Подробнее
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
