@extends('admin.layout.admin')

@section('content')
    <div class="container">
        <h1>Покупатели</h1>

        @if(session('success'))
            <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.customers.index') }}" style="margin-bottom:20px;">
            <div style="display:grid; grid-template-columns:2fr 1fr auto auto; gap:10px; align-items:end;">
                <div>
                    <label for="search">Поиск</label><br>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Имя или email"
                        style="width:100%;"
                    >
                </div>

                <div>
                    <label for="status">Статус</label><br>
                    <select name="status" id="status" style="width:100%;">
                        <option value="">Все</option>
                        @foreach(\App\Enums\UserStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>
                        <input
                            type="checkbox"
                            name="with_trashed"
                            value="1"
                            {{ !empty($filters['with_trashed']) ? 'checked' : '' }}
                        >
                        С удалёнными
                    </label>
                </div>

                <div>
                    <button type="submit">Фильтровать</button>
                </div>
            </div>
        </form>

        @if($customers->isEmpty())
            <p>Покупатели не найдены.</p>
        @else
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Статус</th>
                    <th>Удалён</th>
                    <th>Заказы</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->status?->value ?? $customer->status }}</td>
                        <td>{{ method_exists($customer, 'trashed') && $customer->trashed() ? 'Да' : 'Нет' }}</td>
                        <td>{{ $customer->orders_count }}</td>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}">Открыть</a>
                            |
                            <a href="{{ route('admin.customers.edit', $customer) }}">Редактировать</a>

                            @if(method_exists($customer, 'trashed') && $customer->trashed())
                                |
                                <form action="{{ route('admin.customers.restore', $customer->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit">Восстановить</button>
                                </form>
                            @else
                                |
                                <form
                                    action="{{ route('admin.customers.destroy', $customer) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('Удалить покупателя?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Удалить</button>
                                </form>
                            @endif
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
