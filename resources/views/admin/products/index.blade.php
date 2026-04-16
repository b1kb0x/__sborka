@extends('admin.layout.admin')

@section('content')
        <div class="container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h1>Товары</h1>
                <a href="{{ route('admin.products.create') }}">Создать товар</a>
            </div>

            @if(session('success'))
                <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                    {{ session('success') }}
                </div>
            @endif

            @if($products->isEmpty())
                <p>Товаров нет.</p>
            @else
                <table border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Активен</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->slug }}</td>
                            <td>{{ $product->is_active ? 'Да' : 'Нет' }}</td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product) }}">Редактировать</a>

                                <form action="{{ route('admin.products.destroy', $product) }}"
                                      method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Удалить товар?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div style="margin-top:20px;">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
@endsection
