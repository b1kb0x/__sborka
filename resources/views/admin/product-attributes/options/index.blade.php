@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <p>
            <a href="{{ route('admin.product-attributes.index') }}">← Назад к характеристикам</a>
        </p>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h1>Опции характеристики</h1>
                <p><strong>{{ $productAttribute->name }}</strong> ({{ $productAttribute->slug }})</p>
            </div>

            <a href="{{ route('admin.product-attributes.options.create', $productAttribute) }}">
                Создать опцию
            </a>
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

        @if($options->isEmpty())
            <p>Опций пока нет.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Значение</th>
                    <th>Сортировка</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($options as $option)
                    <tr>
                        <td>{{ $option->id }}</td>
                        <td>{{ $option->value }}</td>
                        <td>{{ $option->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.product-attributes.options.edit', [$productAttribute, $option]) }}">
                                Редактировать
                            </a>

                            <form action="{{ route('admin.product-attributes.options.destroy', [$productAttribute, $option]) }}"
                                  method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Удалить опцию?')">
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
                {{ $options->links() }}
            </div>
        @endif
    </div>
@endsection
