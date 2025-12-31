@extends('layouts.dashboard')

@section('title', 'Products')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-sm btn-outline-primary mr-2">Create</a>
     <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- filter --}}
{{-- <form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4"> --}}
<form action="{{ request()->url() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
            @foreach (['active' => 'Active' ,'arvived' => 'Archived', 'draft' => 'Draft'] as $value => $label )
                <option value="{{ $value }}" @selected(request('status') == $value)>
                    {{ $label }}
                </option>
            @endforeach
    </select>
    <button class="btn btn-dark mx-2">Filter</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>quantity</th>
            <th>price</th>
            <th>compare price</th>
            <th>Status</th>
            <th>Created At</th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td>
    @if ($product->images->isNotEmpty())
        <img src="{{ asset('storage/' . $product->images->first()->image) }}" 
            alt="" height="50">
    @else
        <span>No Image</span>
    @endif
</td>
            <td>{{ $product->id }}</td>
            <td> <a href="{{ route('dashboard.products.show', $product->id) }}">{{ $product->name }}</td>
            <td>{{ $product->category->name }}</td>
            <td>{{ $product->quantity }}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->compare_price }}</td>
            <td>{{ $product->status }}</td>
            <td>{{ $product->created_at }}</td>
            <td>
                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
            </td>
            <td>
                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No products defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $products->appends(request()->query())->links('pagination::bootstrap-5') }} 
{{ $products->links('pagination::bootstrap-5') }} 


{{ $products
->withQueryString()->appends(['search' => 1])
->links() }}


@endsection