@extends('admin.layout.admin')

@section('content')
        <div class="container">
            {{--@include('admin.components.top', ['title' => 'Products', 'link' => route('admin.products.create'), 'button_title' => 'Create'])--}}

            @if(session('success'))
                <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
                    {{ session('success') }}
                </div>
            @endif

            @if($products->isEmpty())
                <p>Товаров нет.</p>
            @else

                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th class="w-25"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="chip {{ $product->is_active ? 'chip-success' : 'chip-danger' }}">
                                {{ $product->is_active ? 'Publish' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary btn-sm">Edit</a>

                                <form action="{{ route('admin.products.destroy', $product) }}"
                                      method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Удалить товар?')">
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

                <div style="margin-top:20px;">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
@endsection
