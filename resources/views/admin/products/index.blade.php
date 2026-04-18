@extends('admin.layout.admin')

@section('header')
    <div class="col">
        <h2 class="page-title">Products</h2>
        <div class="text-secondary mt-1">Manage catalog items, availability, and product details</div>
    </div>

    <div class="col-auto ms-auto d-print-none">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            Create product
        </a>
    </div>
@endsection

@section('content')

    @if(session('success'))
        <div style="margin-bottom:15px; padding:10px; border:1px solid green;">
            {{ session('success') }}
        </div>
    @endif

    @if($products->isEmpty())
        <p>Товаров нет.</p>
    @else

        <div class="card">
            <div class="table-responsive">
                <div class="card-header"></div>
                <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="chip {{ $product->is_active ? 'chip-success' : 'chip-danger' }}">
                                {{ $product->is_active ? 'Publish' : 'Inactive' }}</span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="btn btn-outline-primary btn-sm">Edit</a>

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
            </div>
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        </div>

    @endif
@endsection
