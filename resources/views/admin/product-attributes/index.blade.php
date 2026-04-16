@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1>Характеристики</h1>
            <a href="{{ route('admin.product-attributes.create') }}">Создать характеристику</a>
        </div>

        @if(session('success'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid red;">
                {{ session('error') }}
            </div>
        @endif

        @if($attributes->isEmpty())
            <p>Характеристик пока нет.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Slug</th>
                    <th>Тип</th>
                    <th>Группа</th>
                    <th>Сортировка</th>
                    <th>Видима</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($attributes as $attribute)
                    <tr>
                        <td>{{ $attribute->id }}</td>
                        <td>{{ $attribute->name }}</td>
                        <td>{{ $attribute->slug }}</td>
                        <td>{{ $attribute->type }}</td>
                        <td>{{ $attribute->display_group ?: '—' }}</td>
                        <td>{{ $attribute->sort_order }}</td>
                        <td>{{ $attribute->is_visible ? 'Да' : 'Нет' }}</td>
                        <td>
                            @if($attribute->type === 'select')
                                <a href="{{ route('admin.product-attributes.options.index', $attribute) }}">
                                    Опции
                                </a>
                                |
                            @endif

                            <a href="{{ route('admin.product-attributes.edit', $attribute) }}">
                                Редактировать
                            </a>

                            <form action="{{ route('admin.product-attributes.destroy', $attribute) }}"
                                  method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Удалить характеристику?')">
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
                {{ $attributes->links() }}
            </div>
        @endif
    </div>
@endsection
