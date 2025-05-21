@extends('layouts.dashboard')

@section('title', 'Products')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')



<form action="{{ URL::current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">All</option>
        <option value="active" @selected(request('status') == 'active')>Active</option>
        <option value="archived" @selected(request('status') == 'archived')>Archived</option>
    </select>
    <button class="btn btn-dark mx-2">Filter</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th></th>
            <th>ID</th>
            <th>Name</th>
            <th>description </th>
            <th>category</th>
            <th>price</th>
            <th>Status</th>
            <th>####</th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td><img src="{{ asset('storage/' . $product->image) }}" alt="" height="50"></td>
            <td>{{ $product->id }}</td>
            <td><a href="{{ route('front.products.show', $product->id) }}">{{ $product->name }}</a></td>
            <td>{{ $product->description }}</td>

           
                
            {{-- <td>{{ $category->products_number }}</td> --}}
            <td>{{ $product->category->name }}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->status }}</td>
            <td>
                <form method="post" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="col-lg-4 col-md-4 col-12">
                        <div class="form-group quantity">
                            <label for="color">Quantity</label>
                            <select class="form-control" name="quantity">
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-outline-success">add to cart</button>
                </form>
                {{-- @can('categories.update') --}}
                {{-- @endcan --}}
            </td> 
             <td> 
                {{-- @can('categories.delete') --}}
                {{-- <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form> --}}
                {{-- @endcan --}}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No categories defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $products->
// withQueryString()->
// appends(['search' => 1])->
links() }}

{{-- {{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }} --}}
{{-- {{ $categories->links('pagination::bootstrap-5') }} --}}

@endsection