@extends('layouts.dashboard')

@section('title', __('app.products') )

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.products') }}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{ __('app.create') }}</a>
     <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">{{ __('app.trash') }}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- filter --}}
{{-- <form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4"> --}}
<form action="{{ request()->url() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="status" class="form-control mx-2">
        <option value="">{{ __('app.all') }}</option>
            @foreach(\App\Enums\ProductStatus::cases() as $status)
            <option 
                value="{{ $status->value }}"
                @selected(request('status') == $status->value)
            >
                {{ $status->label() }}
            </option>
        @endforeach
    </select>
    <button class="btn btn-dark mx-2">{{ __('app.filter') }}</button>
</form>

<table class="table">
    <thead>
        <tr>
            {{-- <th>#</th> --}}
            <th>ID</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.stock') }}</th>
            <th>{{ __('app.price') }}</th>
            {{-- <th>{{ __('app.compare_price') }}</th> --}}
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.created_at') }}</th>
            <th colspan="2">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            {{-- <td>
                @if ($product->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $product->images->first()->image) }}" 
                        alt="" height="50">
                @else
                    <span>No Image</span>
                @endif
            </td> --}}
            <td>{{ $product->id }}</td>
            <td> <a href="{{ route('dashboard.products.show', $product->id) }}">{{ $product->name }}</td>
            <td>{{ $product->category->name }}</td>
            <td>{{ $product->total_quantity }}</td>

            <td>
                @if($product->primaryVariation && $product->primaryVariation->compare_price > $product->price)
                    <span class="fw-bold">
                        {{ $product->primaryVariation->price }}
                    </span>
                    <span class="text-muted text-decoration-line-through">
                        {{ $product->primaryVariation->compare_price }}
                    </span>
                @endif
                
                
            </td>

            <td>
                <span class="badge bg-{{ $product->status->color() }} badge-{{ $product->id }}">
                    {{ $product->status->label() }}
                </span>
            </td>
            <td>{{ $product->created_at }}</td>
            <td>
                <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-sm btn-outline-success">{{ __('app.show') }}</a>
            </td>
            <td>
                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-outline-success">{{ __('app.edit') }}</a>
            </td>
            <td>
                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
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

{{-- {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }} 
{{ $products->links('pagination::bootstrap-5') }}  --}}


{{ $products->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}


@endsection