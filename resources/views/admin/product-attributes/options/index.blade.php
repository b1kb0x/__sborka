@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Options</h2>
        <div class="text-secondary mt-1">...</div>
    </div>

    <div class="col-auto ms-auto d-print-none">
        <a href="{{ route('admin.product-attributes.options.create', $productAttribute) }}" class="btn btn-primary">
            Create option
        </a>
    </div>
@endsection

@section('content')

    <p>
        <a href="{{ route('admin.product-attributes.index', $productAttribute) }}" class="text-body-secondary text-decoration-none">← Back to Attributes</a>
    </p>


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

            <div class="card">
                <div class="card-header"></div>
            <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                <thead>
                <tr>
                    <th>Значение</th>
                    <th class="text-center">Сортировка</th>
                    <th class="w-1">Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($options as $option)
                    <tr>
                        <td>{{ $option->value }}</td>
                        <td class="text-center">{{ $option->sort_order }}</td>
                        <td>
                            <a href="{{ route('admin.product-attributes.options.edit', [$productAttribute, $option]) }}" class="btn btn-outline-primary">
                                Edit
                            </a>

                            <form action="{{ route('admin.product-attributes.options.destroy', [$productAttribute, $option]) }}"
                                  method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Удалить опцию?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
                <div class="card-footer">
                    {{ $options->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
