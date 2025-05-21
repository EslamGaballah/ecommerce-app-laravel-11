@extends('layouts.dashboard')

@section('title', $product->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">Products</li>
<li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')

<table class="table">
    <thead>
        <tr>
            <th></th>
            <th>Name</th>
            {{-- <th>Store</th> --}}
            <th>quantity</th>
            <th>price</th>
            <th>category</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @php
        //     $product = $category->products()->with('store')->latest()->paginate(5);
        @endphp
        {{-- @forelse($products as $product) --}}
        <tr>
            <td><img src="{{ asset('storage/' . $product->image) }}" alt="" height="50"></td>
            <td>{{ $product->name }}</td>
            {{-- <td>{{ $product->store->name }}</td> --}}
            <td>{{ $product->quantity }}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->category->name }}</td>
            <td>{{ $product->status }}</td>
            <td>{{ $product->created_at }}</td>
        </tr>
        {{-- @empty --}}
        {{-- <tr>
            <td colspan="5">No products defined.</td>
        </tr> --}}
        {{-- @endforelse --}}
    </tbody>
</table>

{{-- {{ $products->links() }} --}}

@endsection