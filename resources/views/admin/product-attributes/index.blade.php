@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Attributes</h2>
        <div class="text-secondary mt-1">Manage product attributes and their display order</div>
    </div>

    <div class="col-auto ms-auto d-print-none">
        <a href="{{ route('admin.product-attributes.create') }}" class="btn btn-primary">
            Create attribute
        </a>
    </div>
@endsection

@section('content')

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

            <div class="card">
                <div class="table-responsive">
                    <div class="card-header"></div>
                    <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Group</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($attributes as $attribute)
                            <tr>
                                <td>{{ $attribute->name }}</td>
                                <td>{{ $attribute->slug }}</td>
                                <td>{{ $attribute->type }}</td>
                                <td>{{ $attribute->display_group ?: '—' }}</td>
                                <td>{{ $attribute->sort_order }}</td>
                                <td><span class="chip {{ $attribute->is_visible ? 'chip-success' : 'chip-danger' }}">
                            {{ $attribute->is_visible ? 'Visible' : 'Hidden' }}</span></td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($attribute->type === 'select')
                                            <a href="{{ route('admin.product-attributes.options.index', $attribute) }}"
                                               class="btn btn-outline-success">
                                                Options
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.product-attributes.edit', $attribute) }}"
                                           class="btn btn-outline-primary">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.product-attributes.destroy', $attribute) }}"
                                              method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('Удалить характеристику?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $attributes->links() }}
                </div>
            </div>
        @endif

@endsection
