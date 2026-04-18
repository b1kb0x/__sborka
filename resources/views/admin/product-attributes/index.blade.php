@extends('admin.layout.admin')

@section('content')
    <div class="container">

    {{--@include('admin.components.top', ['title' => 'Attributes', 'link' => route('admin.product-attributes.create'), 'button_title' => 'Create'])--}}

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

        <table class="table align-middle">
            <thead class="table-light">
            <tr>
                <th>ID</th>
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
                    <td>{{ $attribute->id }}</td>
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
                            <a href="{{ route('admin.product-attributes.options.index', $attribute) }}" class="btn btn-outline-success btn-sm">
                                Options
                            </a>
                        @endif

                        <a href="{{ route('admin.product-attributes.edit', $attribute) }}" class="btn btn-outline-primary btn-sm">
                            Edit
                        </a>

                        <form action="{{ route('admin.product-attributes.destroy', $attribute) }}"
                              method="POST"
                              style="display:inline-block;"
                              onsubmit="return confirm('Удалить характеристику?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $attributes->links() }}
        </div>
    @endif
</div>
@endsection
